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
class ThriftyFileSession implements SessionHandlerInterface
{
    /**
     * @var int
     */
    protected static $ttl;

    /**
     * @var string
     */
    protected static $path;
    protected static $handler;
    protected static $prefix = 'sess_';

    /**
     * @var bool
     */
    protected static $cookie;
    protected static $exists;

    /**
     * @var object
     */
    protected static $instance = null;
    protected static $cleanup = null;

    /**
     * Returns new or existing singleton instance
     *
     * @return $this
     */
    public static function getInstance($start = false)
    {
        return self::init($start);
    }

    public static function init($start = true)
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        if (
            $start
            && !self::started()
        ) {
            session_start();
        }

        return static::$instance;
    }

    public static function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public static function ttl($ttl)
    {
        static::$ttl = (int) $ttl;
    }

    public static function cleanup($cleanup)
    {
        static::$cleanup = $cleanup;
    }

    public static function exists()
    {
        return (bool) static::$exists;
    }

    public static function kill()
    {
        if (is_callable(static::$cleanup)) {
            call_user_func(static::$cleanup);
        }
        session_write_close();
    }

    protected function __construct()
    {
        // Note: ttl of cookie can be overwritten later and via ttl()
        // method as long as this object is not destroyed
        static::$ttl = (int) ini_get('session.gc_maxlifetime');

        // Make sure save handler is 'files'
        static::$handler = (string) ini_get('session.save_handler');

        if (self::started()) {
            throw new Exception("Session already started");
        }

        if (headers_sent($file, $line)) {
            throw new Exception(sprintf("Headers already sent in file %s on line %d", $file, $line));
        }

        if (!session_set_save_handler($this, false)) {
            throw new Exception(sprintf("Cannot set %s as session handler", __CLASS__));
        }

        register_shutdown_function([__CLASS__, 'kill']);

        // Make sure no output is printed until __destruct() of this
        // class is called so that we can set session cookie later
        ob_start();
    }

    private function __clone() {}

    public function open($path, $name)
    {
        // Note: create realpath here as method write() will have
        // path '/' because of chdir() internally somewhere later
        // and therefore won't work with relative paths
        static::$path = realpath($path);

        return true;
    }

    public function read($id)
    {
        // Note: Prevent PHP from always setting session cookie but do
        // not disable this feature before this point as the cookie is
        // generated later but used for getting session-id before here
        static::$cookie = (bool) ini_get('session.use_cookies');
        ini_set('session.use_cookies', false);

        static::$exists = false;
        $file = $this->file($id);
        $data = '';

        if (file_exists($file)) {
            static::$exists = true;
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
            || static::$exists
        ) {
            // Save session data in session file on disk
            $file = $this->file($id);
            $ok = @file_put_contents($file, $data);
            if ($ok === false) {
                return false;
            }
            @chmod($file, 0600);

            static::$exists = true;
        }

        return true;
    }

    public function close()
    {
        if (
            static::$cookie
            && static::$exists
            && !headers_sent()
        ) {
            setcookie(
                session_name(),
                session_id() ?: '-',
                session_id() ? time() + static::$ttl : 1,
                ini_get('session.cookie_path'),
                ini_get('session.cookie_domain'),
                ini_get('session.cookie_secure'),
                ini_get('session.cookie_httponly')
            );
        }

        if (ob_get_length()) {
            ob_end_flush();
        }

        // Restore overwritten ini settings
        ini_set('session.save_handler', static::$handler);
        ini_set('session.use_cookies', static::$cookie);

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

    protected function file($id)
    {
        return sprintf('%s/%s%s', static::$path, static::$prefix, $id);
    }
}
