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
 * @method $this deleteSuccess(string $message, $when = FlashHandler::NEXT) Delete message for $key = 'success'.
 *
 * @method $this setError(string $message, $when = FlashHandler::NEXT) Set message for $key = 'error'.
 * @method $this addError(string $message, $when = FlashHandler::NEXT) Add message for $key = 'error'.
 * @method $this deleteError(string $message, $when = FlashHandler::NEXT) Delete message for $key = 'error'.
 *
 * @method $this setWarning(string $message, $when = FlashHandler::NEXT) Set message for $key = 'warning'.
 * @method $this addWarning(string $message, $when = FlashHandler::NEXT) Add message for $key = 'warning'.
 * @method $this deleteWarning(string $message, $when = FlashHandler::NEXT) Delete message for $key = 'warning'.
 *
 * @method $this setInfo(string $message, $when = FlashHandler::NEXT) Set message for $key = 'info'.
 * @method $this addInfo(string $message, $when = FlashHandler::NEXT) Add message for $key = 'info'.
 * @method $this deleteInfo(string $message, $when = FlashHandler::NEXT) Delete message for $key = 'info'.
 */
class FlashHandler implements IteratorAggregate, Countable
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
        $allowed = ['add', 'delete', 'set'];

        foreach ($allowed as $prefix) {
            if (0 === strpos($name, $prefix)) {
                $key    = Text::toLower(substr($name, strlen($prefix)));
                $method = $prefix . 'Message';
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
    public static function create(CookiesInterface $cookie, string $cookieKey = 'flash')
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
    public function addMessage(string $key, string $message, $when = self::NEXT)
    {
        $merged = $this->messages[$when][$key] ?? [];
        $merged = (array) $merged;

        $merged[] = $message;

        return $this->setMessage($key, $merged, $when);
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
        return count($this->getMessages());
    }

    /**
     * @param string $key
     * @param int    $when [optional]
     *
     * @return $this
     */
    public function deleteMessage(string $key, $when = self::NEXT)
    {
        $deleteWhen = $when == self::ALL ? self::$when : (array) $when;
        foreach ($deleteWhen as $when) {
            $this->setMessage($key, null, $when);
        }

        return $this;
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
        $messages = $this->getMessages();

        return new ArrayIterator($messages);
    }

    /**
     * Get message for specified $key (or $default value as default).
     *
     * @param string $key
     * @param string $default [optional]
     *
     * @return string|null
     */
    public function getMessage(string $key, string $default = null)
    {
        $messages = $this->getMessages();

        return $messages[$key] ?? $default;
    }

    /**
     * Get all messages.
     *
     * @return string[]
     */
    public function getMessages()
    {
        return array_merge($this->messages[self::DURABLE], $this->messages[self::NOW]);
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
    public function setMessage(string $key, $message, $when = self::NEXT)
    {
        $this->validateWhen($when);

        if (null == $message) {
            unset($this->messages[$when][$key]);
        } else {
            $this->messages[$when][$key] = $message;
        }

        $this->save();

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
