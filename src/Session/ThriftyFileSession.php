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
 * All you need is to configure the native PHP session settings, see:
 * http://php.net/manual/de/session.configuration.php
 *
 * After that load this class and call the static init() method:
 * Ansas\Component\Session\ThriftyFileSession::init();
 *
 * This will automatically start the session and you can use the native
 * session handling functions and the super global variable $_SESSION
 * as usual.
 *
 * The benefit of this class (compared to pure native PHP sessions):
 * - session is only storaged on disk if $_SESSION has data
 * - session cookie is only set if $_SESSION has data
 * - session cookie is updated after every request
 * - session cookie is deleted automatically when session is destroyed
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
 *  - will be set to false by this class
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
     * @var string $path    Session save path
     * @var string $handler Original session handler
     * @var string $prefix  Session prefix (is native PHP session value)
     */
    protected $path    = null;
    protected $handler = null;
    protected $prefix  = 'sess_';

    /**
     * @var boolean $cookie Flag if cookies are supposed to be set
     * @var boolean $cookie Flag if session exists on disk
     */
    protected $cookie = false;
    protected $exists = false;

    /**
     * @var int $ttl Cookie time-to-live value in seconds
     */
    protected $ttl = 0;

    /**
     * @var callable $clenup Function called before session close
     */
    protected $cleanup = null;

    /**
     * @static object $instance Holds instance of this class (singleton)
     */
    protected static $instance = null;

    /**
     * Returns new or existing singleton instance
     *
     * @static
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
     *
     * @static
     */
    public static function init()
    {
        self::getInstance()->start();
    }

    /**
     * Set time-to-live (ttl) for the session cookie
     *
     * @static
     * @param int $ttl time-to-live for the session in seconds
     */
    public static function ttl($ttl)
    {
        self::getInstance()->setCookieLifetime($ttl);
    }

    /**
     * Set callback function to execute directly before session closed
     *
     * @static
     * @param callable $callback function to call on session termination
     */
    public static function cleanup($callback)
    {
        self::getInstance()->setCleanupCallback($callback);
    }

    /**
     * Close session
     *
     * This method closes the session. This method will be called on
     * script termination automatically if init() was called.
     *
     * @static
     */
    public static function kill()
    {
        if (null !== static::$instance) {
            self::getInstance()->end();
            static::$instance = null;
        }
    }

    /**
     * Constructor
     *
     * Must be called via static method init()
     *
     * Performes session setup:
     * - several checks
     * - register this class as session handler
     * - add kill() as shutdown function
     * - start output buffering
     */
    protected function __construct()
    {
        $this->cookie  = (bool)   ini_get('session.use_cookies');
        $this->handler = (string) ini_get('session.save_handler');
        $this->path    = (string) realpath(ini_get('session.save_path'));

        // Make sure session is not already started as we can only set
        // ini values or override the session handler before session is
        // started
        if ($this->isStarted()) {
            throw new \Exception("Session already started");
        }

        // Make sure nothing is sent to client already as we have to be
        // able to set cookie just before script is terminated
        if (headers_sent($file, $line)) {
            throw new \Exception(sprintf("Headers already sent in file %s on line %d", $file, $line));
        }

        // Make current instance of class responsible for saving session
        if (!session_set_save_handler($this, false)) {
            throw new \Exception(sprintf("Cannot set %s as session handler", __CLASS__));
        }

        // Make sure session is closes (and saved) on script termination
        register_shutdown_function([__CLASS__, 'kill']);

        // Make sure no output is printed until __destruct() of this
        // class is called so that we can set session cookie later
        if (!ob_start()) {
            throw new \Exception("Cannot start output buffer");
        }

        // Initial cookie time-to-live value, can be overwritten via
        // ttl() method as session is not terminated via kill()
        $this->setCookieLifetime(ini_get('session.gc_maxlifetime'));

        // Prevent PHP from setting session cookie internally
        ini_set('session.use_cookies', false);

        // Make sure session save path is writable
        if (!is_writable($this->path)) {
            throw new \Exception(sprintf("Session path %s is not writable", $this->path));
        }
    }

    /**
     * Destructor
     *
     * Called automatically via kill()
     *
     * Performs session cleanup:
     * - set session cookie
     * - restore default values
     * - flush output buffer
     */
    public function __destruct()
    {
        if ($this->cookie
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

    /**
     * Clone
     *
     * Make sure instance cannot be cloned
     */
    private function __clone()
    {
    }

    /**
     * INTERNAL METHOD open
     *
     * {@inheritdoc}
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * INTERNAL METHOD read
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        // Reset data
        $data = '';
        $this->exists = false;

        // Check if session file exists but (unlike native sessions) do
        // not create new session file on disk if session does not exist
        $file = $this->getPathForSessionId($id);
        if (file_exists($file)) {
            $data = (string) file_get_contents($file);
            $this->exists = true;
        }

        return $data;
    }

    /**
     * INTERNAL METHOD write
     *
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        // Only store session to disk if data exists or session already
        // existed before (in last case we always want to touch the file
        // to prevent early garbage collection)
        if ($data
            || $this->exists
        ) {
            $file = $this->getPathForSessionId($id);
            file_put_contents($file, $data);
            chmod($file, 0600);
            $this->exists = true;
        }

        return true;
    }

    /**
     * INTERNAL METHOD close
     *
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * INTERNAL METHOD destroy
     *
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        // Note: Do NOT delete session data in $_SESSION variable or
        // session cookie here as this method is also called via
        // session_regenerate_id(true)

        // Delete session file on disk
        $file = $this->getPathForSessionId($id);
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    /**
     * INTERNAL METHOD gc
     *
     * {@inheritdoc}
     */
    public function gc($ttl)
    {
        // Delete all files not changed or touched since $ttl seconds
        // which is the session.gc_maxlifetime value
        $glob = $this->getPathForSessionId('*');
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

    /**
     * Get full path for session id provided via $id
     *
     * @param string $id Session id
     * @return string
     */
    protected function getPathForSessionId($id)
    {
        if (is_null($this->path)) {
            throw new \Exception("Path not set");
        }

        return sprintf('%s/%s%s', $this->path, $this->prefix, $id);
    }

    /**
     * Start session
     *
     * Get and set session id and start session
     *
     * @return $this
     */
    public function start()
    {
        if (!$this->isStarted()) {
            if ($this->cookie
                && !empty($_COOKIE[session_name()])
            ) {
                session_id($_COOKIE[session_name()]);
            }

            session_start();
        }

        return $this;
    }

    /**
     * End session
     *
     * Call cleanup callback and close session
     *
     * @return $this
     */
    public function end()
    {
        if ($this->isStarted()) {
            if (is_callable($this->getCleanupCallback())) {
                call_user_func($this->getCleanupCallback());
            }
            session_write_close();
        }

        return $this;
    }

    /**
     * Get defined cleanup callback function
     *
     * @return callable|null
     */
    public function getCleanupCallback()
    {
        return $this->cleanupCallback;
    }

    /**
     * Get defined cookie time-to-live
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->cookieLifetime;
    }

    /**
     * Set session cleanup callback function
     *
     * @param callable $callback Cleanup callback function
     * @return $this
     */
    public function setCleanupCallback($callback)
    {
        if (!is_null($callback)
            && !is_callable($callback)
        ) {
            throw new \InvalidArgumentException("No callable function provided");
        }

        $this->cleanupCallback = $callback;

        return $this;
    }

    /**
     * Set cookie time-to-live
     *
     * @param int $ttl Cookie time-to-live
     * @return $this
     */
    public function setCookieLifetime($ttl)
    {
        if (!is_null($ttl)
            && !is_numeric($ttl)
        ) {
            throw new \InvalidArgumentException("No valid ttl provided");
        }

        $this->cookieLifetime = (int) $ttl;

        return $this;
    }
}
