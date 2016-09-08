<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Handler;

use Ansas\Util\Text;
use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Slim\Interfaces\Http\CookiesInterface;

/**
 * Class FlashHandler
 *
 * @package Ansas\Slim\Middleware
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @method $this setSuccess(string $message, $when = FlashHandler::NEXT) Set message for $key = 'success'.
 * @method $this addSuccess(string $message, $when = FlashHandler::NEXT) Add message for $key = 'success'.
 * @method $this removeSuccess($when = FlashHandler::NEXT) Delete message for $key = 'success'.
 *
 * @method $this setError(string $message, $when = FlashHandler::NEXT) Set message for $key = 'error'.
 * @method $this addError(string $message, $when = FlashHandler::NEXT) Add message for $key = 'error'.
 * @method $this removeError($when = FlashHandler::NEXT) Delete message for $key = 'error'.
 *
 * @method $this setWarning(string $message, $when = FlashHandler::NEXT) Set message for $key = 'warning'.
 * @method $this addWarning(string $message, $when = FlashHandler::NEXT) Add message for $key = 'warning'.
 * @method $this removeWarning($when = FlashHandler::NEXT) Delete message for $key = 'warning'.
 *
 * @method $this setInfo(string $message, $when = FlashHandler::NEXT) Set message for $key = 'info'.
 * @method $this addInfo(string $message, $when = FlashHandler::NEXT) Add message for $key = 'info'.
 * @method $this removeInfo($when = FlashHandler::NEXT) Delete message for $key = 'info'.
 */
class FlashHandler implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Message will be available from now until deleted.
     */
    const DURABLE = 4;

    /**
     * Message will be available in next request only.
     */
    const NEXT = 2;

    /**
     * Message will be available in current request only.
     */
    const NOW = 1;

    /**
     * Message will be be deleted for all $when groups
     */
    const ALL = 7;

    /**
     * @var string[] Allowed when values.
     */
    protected static $when = [
        self::DURABLE => self::DURABLE,
        self::NEXT    => self::NEXT,
        self::NOW     => self::NOW,
    ];

    /**
     * @var CookieHandler
     */
    protected $cookie;

    /**
     * @var string Cookie key
     */
    protected $cookieKey;

    /**
     * @var array Messages
     */
    protected $messages = [];

    /**
     * Flash constructor.
     *
     * @param CookiesInterface $cookie
     * @param string           $cookieKey [optional]
     */
    public function __construct(CookiesInterface $cookie, string $cookieKey = 'flash')
    {
        $this->cookie    = $cookie;
        $this->cookieKey = $cookieKey;

        $this->load();
    }

    /**
     * Magic method for shortcut message manipulation.
     *
     * @param string $name
     * @param array  $args
     *
     * @return $this
     * @throws BadMethodCallException
     */
    function __call($name, $args)
    {
        $allowed = ['add', 'has', 'remove', 'set'];

        foreach ($allowed as $method) {
            if (0 === strpos($name, $method)) {
                $key = Text::toLower(substr($name, strlen($method)));
                array_unshift($args, $key);

                return call_user_func_array([$this, $method], $args);
            }
        }

        throw new BadMethodCallException('Method unknown.');
    }

    /**
     * Add a message to $key (result for $key will always be an array).
     *
     * @param string $key
     * @param string $message
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function add(string $key, string $message, $when = self::NEXT)
    {
        if (!strlen($message)) {
            return $this;
        }

        $merged = $this->messages[$when][$key] ?? [];
        $merged = (array) $merged;

        $merged[] = $message;

        return $this->set($key, $merged, $when);
    }

    /**
     * Get all messages.
     *
     * @return string[]
     */
    public function all()
    {
        return array_merge($this->messages[self::DURABLE], $this->messages[self::NOW]);
    }

    /**
     * Returns the number of elements.
     *
     * This method implements the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->all());
    }

    /**
     * Create new instance.
     *
     * @param CookiesInterface $cookie
     * @param string           $cookieKey [optional]
     *
     * @return static
     */
    public static function create(CookiesInterface $cookie, string $cookieKey = 'flash')
    {
        return new static($cookie, $cookieKey);
    }

    /**
     * Get message for specified $key (or $default value as default).
     *
     * @param string $key
     * @param string $default [optional]
     *
     * @return string|null
     */
    public function get(string $key, string $default = null)
    {
        $messages = $this->all();

        return $messages[$key] ?? $default;
    }

    /**
     * Get the iterator.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $messages = $this->all();

        return new ArrayIterator($messages);
    }

    /**
     * Check if message for specified $key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key)
    {
        return !!$this->get($key);
    }

    /**
     * Load messages from cookie.
     *
     * @return $this
     */
    protected function load()
    {
        $this->reset();

        $messages = $this->cookie->get($this->cookieKey);

        if ($messages) {
            $messages = json_decode($messages, true);

            if (!empty($messages[self::DURABLE])) {
                $this->messages[self::DURABLE] = $messages[self::DURABLE];
            }
            if (!empty($messages[self::NEXT])) {
                $this->messages[self::NOW] = $messages[self::NEXT];
            }

            $this->save();
        }

        return $this;
    }

    /**
     * Whether a offset exists.
     *
     * This method implements the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * This method implements the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * This method implements the ArrayAccess interface.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * This method implements the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @param string $key
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function remove(string $key, $when = self::NEXT)
    {
        $deleteWhen = $when == self::ALL ? self::$when : (array) $when;
        foreach ($deleteWhen as $when) {
            $this->set($key, null, $when);
        }

        return $this;
    }

    /**
     * Reset messages and build array structure.
     *
     * @return $this
     */
    public function reset()
    {
        $this->messages = [];

        foreach (self::$when as $when) {
            $this->messages[$when] = [];
        }

        return $this;
    }

    /**
     * Save messages to cookie.
     *
     * @return $this
     */
    protected function save()
    {
        $messages = [];
        foreach ([self::DURABLE, self::NEXT] as $when) {
            if (!empty($this->messages[$when])) {
                $messages[$when] = $this->messages[$when];
            }
        }

        if ($messages) {
            $messages = json_encode($messages);
            $this->cookie->set($this->cookieKey, $messages);
        } else {
            $this->cookie->remove($this->cookieKey);
        }

        return $this;
    }

    /**
     * Set / unset massage.
     *
     * @param string $key
     * @param mixed  $message
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function set(string $key, $message, $when = self::NEXT)
    {
        $this->validateWhen($when);

        if (null == $message) {
            if (isset($this->messages[$when][$key])) {
                unset($this->messages[$when][$key]);
                $this->save();
            }
        } elseif ($message) {
            $this->messages[$when][$key] = $message;
            $this->save();
        }

        return $this;
    }

    /**
     * Check if format is supported.
     *
     * @param int $when
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function validateWhen($when)
    {
        if (!in_array($when, self::$when)) {
            throw new InvalidArgumentException('value of parameter $when not supported');
        }

        return $this;
    }
}
