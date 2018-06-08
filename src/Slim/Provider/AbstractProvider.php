<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Provider;

use Exception;
use Pimple\ServiceProviderInterface;

/**
 * Class AbstractProvider
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
abstract class AbstractProvider implements ServiceProviderInterface
{
    /**
     * Get default settings.
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [];
    }

    /**
     * Merge provided $settings with default settings.
     *
     * @param array $settings
     *
     * @return array
     * @throws Exception
     */
    public static function mergeWithDefaultSettings($settings)
    {
        if (empty($settings)) {
            return static::getDefaultSettings();
        }
        if (is_array($settings)) {
            return array_merge(static::getDefaultSettings(), $settings);
        }
        throw new Exception("Argument must be an array.");
    }
}
