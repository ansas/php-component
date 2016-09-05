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
     * Refresh cookie (set response cookie only if request cookie exists)
     *
     * @param string     $name    Cookie name
     * @param string|int $expires [optional] Cookie expire value
     *
     * @return $this
     */
    public function refresh($name, $expires = null)
    {
        $name = $this->getFullName($name);

        // Only set response cookie if request cookie was set
        if (null !== $this->get($name)) {
            $this->set($name, $this->get($name), $expires);
        }

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

        // Set response cookie if request cookie was set
        if (null !== $this->get($name)) {
            // Note: timestamp '1' = '1970-01-01 00:00:00 UTC'
            $this->set($name, 'deleted', '1');
        }

        return $this;
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
     * Set one default cookie property
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setDefault(string $key, $value)
    {
        return $this->setDefaults([$key => $value]);
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
}

