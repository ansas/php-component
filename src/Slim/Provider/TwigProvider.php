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

use Pimple\Container;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

/**
 * Class TwigProvider
 *
 * <code>composer require slim/twig-view</code>
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class TwigProvider extends AbstractProvider
{
    /**
     * Get default settings.
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [
            'engine'  => 'twig',
            'path'    => '.',
            'options' => [
                'autoescape'       => true,
                'auto_reload'      => true,
                'cache'            => false,
                'charset'          => 'utf-8',
                'debug'            => true,
                'strict_variables' => false,
            ],
        ];
    }

    /**
     * Register provider.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $settings = array_merge([], self::getDefaultSettings(), $container['settings']['view']);

        $path    = rtrim($settings['path'], '/') . '/';
        $options = $settings['options'];

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return Twig
         */
        $container['view'] = function (Container $c) use ($path, $options) {
            $view = new Twig($path, $options);
            $view->addExtension(
                new TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                )
            );
            $view->addExtension(new \Twig_Extension_Debug());
            $view->addExtension(new \Twig_Extensions_Extension_Intl());

            // Make container available to template engine (global)
            $view->getEnvironment()->addGlobal("c", $c);

            return $view;
        };
    }
}
