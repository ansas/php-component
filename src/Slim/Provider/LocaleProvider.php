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

use Ansas\Component\Locale\Localization;
use Pimple\Container;

/**
 * Class LocaleProvider
 *
 * - Enables localization: Locale::getDefault() and gettext() support)
 * - Adds the "default" locale your to translate parts are written in and
 * - Finds all supported translated locales dynamically.
 *
 * @package App\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class LocaleProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'path'      => '.',
            'domain'    => 'default',
            'default'   => 'de_DE',
            'available' => ['de_DE'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['locale'] = self::mergeWithDefaultSettings($container['settings']['locale']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return Localization
         */
        $container['locale'] = function (Container $c) {
            $settings = $c['settings']['locale'];

            // Create localization object
            $locale = Localization
                ::create()
                ->setPath($settings['path'])
                ->setDomain($settings['domain'])
                ->addLocales($settings['available'])
                ->findAvailableLocales()
            ;

            // Set default locale (after setting all available locales!)
            // Note: locale will be added if not already set
            $locale->setDefault($settings['default']);

            return $locale;
        };
    }
}
