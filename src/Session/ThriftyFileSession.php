<?php

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Ansas\Component\Session;

/**
 * ThriftyFileSession
 *
 * All you need is to configure the native PHP session settings as usual
 * (see http://php.net/manual/de/session.configuration.php).
 *
 * After that load this class and call the static init() method:
 * Ansas\Component\Session\ThriftyFileSession::init();
 *
 * This will automatically start the session and you can use the native
 * session handling functions and the super global variable $_SESSION
 * as usual.
 *
 * The benefit of this class is that session storage on disk and session
 * cookies are set only if $_SESSION has data. Also the session cookie
 * will also be updated with every request, so it will not expire before
 * session.gc_maxlifetime and the cookie will be deleted automatically
 * if you destroy the session (both not the case with pure native PHP
 * sessions).
 *
 * All output from init until session close (or script termination) will
 * be buffered in order to still be able to set the session cookie on
 * session close.
 *
 * This class ignores / changes these ini settings:
 * - session.use_strict_mode
 *  - will not work with ANY custom save_handler
 *  - see: https://bugs.php.net/bug.php?id=66947
 *  - workaround: always run session_regenerate_id(true) when needed
 * - session.save_handler
 *  - will be set to 'user' as this class registers itself as handler
 *  - original value is restored on session close
 * - session.use_cookies
 *  - original value will be used to determine if cookies are to be set
 *  - will be set to false by this class after session is started
 *  - original value is restored on session close
 * - session.cookie_lifetime
 *  - value is not changed but ignored completely
 *  - session.gc_maxlifetime will be used initially
 *  - value set via ThriftyFileSession::ttl() will be used
 *
 * Known limitations:
 *  - session.use_strict_mode does not work
 *  - session ids collision is possible
 *
 * @author Ansas Meyer <webmaster@ansas-meyer.de>
 */
class ThriftyFileSession implements \SessionHandlerInterface
{
    /**
     * @var string
     */
    protected $path    = null;
    protected $handler = null;
    protected $prefix  = 'sess_';

    /**
     * @var boolean
     */
    protected $cookie;
    protected $exists;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var object
     */
    protected $cleanup = null;
    protected static $instance = null;

    /**
     * Returns new or existing singleton instance
     *
     * @return ThriftyFileSession
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * Start session (init)
     *
     * Usually the only method you have to call. A new instance will be
     * created (if needed) and the session is started.
     */
    public static function init()
    {
        self::getInstance()->sessionStart();
    }

    /**
     * Set time-to-live (ttl) for the session cookie
     *
     * @param int $ttl time-to-live for the session in seconds
     */
    public static function ttl($ttl)
    {
        self::getInstance()->setCookieLifetime($ttl);
    }

    /**
     * Set callback function to execute directly before session is
     * closed (either when ThriftyFileSession::kill() is called or
     * when script is terminated)
     *
     * @param callable $callback function to call on session termination
     */
    public static function cleanup($callback)
    {
        self::getInstance()->setCleanupCallback($callback);
    }

    /**
     * Close session manually
     *
     * If set via ThriftyFileSession::cleanup() the cleanup callback
     * will be executed and the session will be closed
     */
    public static function kill()
    {
        if (null !== static::$instance) {
            self::getInstance()->sessionEnd();
            static::$instance = null;
        }
    }


    public function __construct()
    {
        // Note: ttl of cookie can be overwritten later and via ttl()
        // method as long as this object is not destroyed
        $this->setCookieLifetime(ini_get('session.gc_maxlifetime'));

        $this->cookie  = (bool)   ini_get('session.use_cookies');
        $this->handler = (string) ini_get('session.save_handler');

        if ($this->isStarted()) {
            throw new \Exception("Session already started");
        }

        if (headers_sent($file, $line)) {
            throw new \Exception(sprintf("Headers already sent in file %s on line %d", $file, $line));
        }

        if (!session_set_save_handler($this, false)) {
            throw new \Exception(sprintf("Cannot set %s as session handler", __CLASS__));
        }

        register_shutdown_function([__CLASS__, 'kill']);

        // Make sure no output is printed until __destruct() of this
        // class is called so that we can set session cookie later
        if (!ob_start()) {
            throw new \Exception("Cannot start output buffer");
        }
    }

    public function __destruct()
    {
        if (
            $this->cookie
            && $this->exists
            && !headers_sent()
        ) {
            setcookie(
                session_name(),
                session_id() ?: '-',
                session_id() ? time() + $this->cookieLifetime : 1,
                ini_get('session.cookie_path'),
                ini_get('session.cookie_domain'),
                ini_get('session.cookie_secure'),
                ini_get('session.cookie_httponly')
            );
        }

        // Restore overwritten ini settings
        ini_set('session.save_handler', $this->handler);
        ini_set('session.use_cookies', $this->cookie);

        if (ob_get_length()) {
            ob_end_flush();
        }
    }

    private function __clone() {}

    public function open($path, $name)
    {
        // Note: create realpath here as method write() will have
        // path '/' because of chdir() internally somewhere later
        // and therefore won't work with relative paths
        $this->path = realpath($path);

        if (!is_writable($this->path)) {
            throw new \Exception(sprintf("Session path %s is not writable", $this->path));
        }

        return true;
    }

    public function read($id)
    {
        // Note: Prevent PHP from always setting session cookie but do
        // not disable this feature before this point as the cookie is
        // generated later but used for getting session-id before here
        ini_set('session.use_cookies', false);

        $this->exists = false;
        $file = $this->file($id);
        $data = '';

        if (file_exists($file)) {
            $this->exists = true;
            $data = (string) @file_get_contents($file);
        }

        return $data;
    }

    public function write($id, $data)
    {
        // Note: Only store session to disk if data exists or session
        // already existed before (in last case we always want to touch
        // the file to prevent early garbage collection)
        if (
            $data
            || $this->exists
        ) {
            // Save session data in session file on disk
            $file = $this->file($id);
            $ok = @file_put_contents($file, $data);
            if ($ok === false) {
                return false;
            }
            @chmod($file, 0600);

            $this->exists = true;
        }

        return true;
    }

    public function close()
    {
        return true;
    }

    public function destroy($id)
    {
        // Delete session file on disk
        $file = $this->file($id);
        if (file_exists($file)) {
            unlink($file);
        }

        // Note: Do NOT delete session data in $_SESSION variable or
        // session cookie here as this method is also called via
        // session_regenerate_id(true)

        return true;
    }

    public function gc($ttl)
    {
        $glob = $this->file('*');
        foreach (glob($glob) as $file) {
            if (filemtime($file) + $ttl < time()) {
                @unlink($file);
            }
        }

        return true;
    }

    /**
     * Checks if the session is already started (active)
     *
     * @return boolean
     */
    public function isStarted()
    {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    protected function file($id)
    {
        if (is_null($this->path)) {
            throw new \Exception("Path not set");
        }

        return sprintf('%s/%s%s', $this->path, $this->prefix, $id);
    }

    public function getCleanupCallback()
    {
        return $this->cleanupCallback;
    }

    public function sessionStart()
    {
        if (!$this->isStarted()) {
            session_start();
        }

        return $this;
    }

    public function sessionEnd()
    {
        if ($this->isStarted()) {
            if (is_callable($this->getCleanupCallback())) {
                call_user_func($this->getCleanupCallback());
            }
            session_write_close();
        }

        return $this;
    }

    public function getCookieLifetime()
    {
        return $this->cookieLifetime;
    }

    public function setCleanupCallback($callback)
    {
        if (
            !is_null($callback)
            && !is_callable($callback)
        ) {
            throw new \InvalidArgumentException("No callable function provided");
        }

        $this->cleanupCallback = $callback;

        return $this;
    }

    public function setCookieLifetime($ttl)
    {
        if (
            !is_null($ttl)
            && !is_numeric($ttl)
        ) {
            throw new \InvalidArgumentException("No valid ttl provided");
        }

        $this->cookieLifetime = (int) $ttl;

        return $this;
    }
}
