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

use Slim\Http\Cookies;

/**
 * Class CookieHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CookieHandler extends Cookies
{
    /**
     * @var string Cookie name prefix.
     */
    protected $prefix;

    /**
     * Create new instance.
     *
     * @param array $cookies [optional] Request cookies.
     *
     * @return static
     */
    public static function create(array $cookies = [])
    {
        return new static($cookies);
    }

    /**
     * Set cookie name prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function setDefaults(array $settings)
    {
        parent::setDefaults($settings);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        $name = $this->getFullName($name);

        return parent::get($name, $default);
    }

    /**
     * Get full (real) cookie name.
     *
     * @param $name
     *
     * @return string
     */
    public function getFullName($name)
    {
        if (false === strpos($name, $this->prefix)) {
            return $this->prefix . $name;
        }

        return $name;
    }

    /**
     * Set response cookie
     *
     * @param string       $name    Cookie name
     * @param string|array $value   Cookie value, or cookie properties
     * @param string|int   $expires [optional] Cookie expire value
     *
     * @return $this
     */
    public function set($name, $value, $expires = null)
    {
        $name = $this->getFullName($name);

        if (!is_array($value)) {
            $value = ['value' => (string) $value];
        }

        if (isset($expires)) {
            $value['expires'] = $expires;
        }

        parent::set($name, $value);

        return $this;
    }

    /**
     * Remove response cookie.
     *
     * @param string $name Cookie name
     *
     * @return $this
     */
    public function remove($name)
    {
        $name = $this->getFullName($name);

        // Delete existing response cookie
        if (isset($this->responseCookies[$name])) {
            unset($this->responseCookies[$name]);
        }

        // Only set response cookie if request cookie was set
        if ($this->get($name)) {
            // Set new response cookie with value to 'deleted' and timestamp '1' (1970-01-01 00:00:00 UTC)
            $this->set($name, 'deleted', '1');
        }

        return $this;
    }
}

