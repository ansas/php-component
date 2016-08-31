<?php
namespace App\Provider;

use Ansas\Component\Locale\Localization;
use Ansas\Slim\Provider\AbstractProvider;
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
            'available' => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        $settings = array_merge([], self::getDefaultSettings(), $container['settings']['locale']);

        /**
         * Add dependency (DI).
         *
         * @return Localization
         */
        $container['locale'] = function () use ($settings) {
            $locale = Localization
                ::create()
                ->setPath($settings['path'])
                ->setDomain($settings['domain'])
                ->setDefault($settings['default'])
                ->addLocales($settings['available'])
                ->findAvailableLocales()
            ;

            return $locale;
        };
    }
}
