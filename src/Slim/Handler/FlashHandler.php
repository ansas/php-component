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
 * @method $this setSuccess($message, $when = FlashHandler::NEXT) Set message for $key = 'success'.
 * @method $this addSuccess($message, $when = FlashHandler::NEXT) Add message for $key = 'success'.
 * @method $this removeSuccess($when = FlashHandler::NEXT) Delete message for $key = 'success'.
 *
 * @method $this setError($message, $when = FlashHandler::NEXT) Set message for $key = 'error'.
 * @method $this addError($message, $when = FlashHandler::NEXT) Add message for $key = 'error'.
 * @method $this removeError($when = FlashHandler::NEXT) Delete message for $key = 'error'.
 *
 * @method $this setWarning($message, $when = FlashHandler::NEXT) Set message for $key = 'warning'.
 * @method $this addWarning($message, $when = FlashHandler::NEXT) Add message for $key = 'warning'.
 * @method $this removeWarning($when = FlashHandler::NEXT) Delete message for $key = 'warning'.
 *
 * @method $this setInfo($message, $when = FlashHandler::NEXT) Set message for $key = 'info'.
 * @method $this addInfo($message, $when = FlashHandler::NEXT) Add message for $key = 'info'.
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
    public function __construct(CookiesInterface $cookie, $cookieKey = 'flash')
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
     * Create new instance.
     *
     * @param CookiesInterface $cookie
     * @param string           $cookieKey [optional]
     *
     * @return static
     */
    public static function create(CookiesInterface $cookie, $cookieKey = 'flash')
    {
        return new static($cookie, $cookieKey);
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
    public function add(string $key, $message, $when = self::NEXT)
    {
        if (!strlen($message)) {
            return $this;
        }

        $merged = isset($this->messages[$when][$key]) ? $this->messages[$when][$key] : [];
        $merged = (array) $merged;

        $merged[] = $message;

        return $this->set($key, $merged, $when);
    }

    /**
     * Get all messages.
     *
     * @param int $when [optional]
     *
     * @return string[]
     */
    public function all($when = null)
    {
        if ($when) {
            $this->validateWhen($when);

            return $this->messages[$when];
        }

        return array_merge($this->messages[self::DURABLE], $this->messages[self::NOW]);
    }

    /**
     * Returns the number of elements.
     *
     * @param int $when [optional]
     *
     * This method implements the Countable interface.
     *
     * @return int
     */
    public function count($when = null): int
    {
        return count($this->all($when));
    }

    /**
     * Get message for specified $key (or $default value as default).
     *
     * @param string $key
     * @param string $default [optional]
     * @param int    $when    [optional]
     *
     * @return string|null
     */
    public function get(string $key, $default = null, $when = null)
    {
        $messages = $this->all($when);

        return isset($messages[$key]) ? $messages[$key] : $default;
    }

    /**
     * Get the iterator.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        $messages = $this->all();

        return new ArrayIterator($messages);
    }

    /**
     * Check if message for specified $key exists.
     *
     * @param string $key
     * @param int    $when [optional]
     *
     * @return bool
     */
    public function has($key, $when = null)
    {
        return !!$this->get($key, null, $when);
    }

    /**
     * Keep current request messages for next request.
     *
     * @return $this
     */
    public function keep()
    {
        foreach ($this->messages[self::NOW] as $key => $value) {
            if (isset($this->messages[self::NEXT][$key])) {
                $value = array_merge((array) $value, (array) $this->messages[self::NEXT][$key]);
            }
            $this->set($key, $value, self::NEXT);
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
    public function offsetExists($offset): bool
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
    public function offsetGet($offset): mixed
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
    public function offsetSet($offset, $value): void
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
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @param string $key
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function remove($key, $when = self::NEXT)
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
     * Set / unset massage.
     *
     * @param string $key
     * @param mixed  $message
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function set($key, $message, $when = self::NEXT)
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
