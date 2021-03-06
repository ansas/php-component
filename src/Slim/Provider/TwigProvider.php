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
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'engine'     => 'twig',
            'path'       => '.',
            'extension'  => '.twig',
            'options'    => [
                'autoescape'       => true,
                'auto_reload'      => true,
                'cache'            => false,
                'charset'          => 'utf-8',
                'debug'            => true,
                'strict_variables' => false,
            ],
            'global'     => [
                'router',
                'request',
                'response',
            ],
            'status'     => [
                404 => '_notfound',
                405 => '_notallowed',
                500 => '_error',
            ],
            'extensions' => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['view'] = self::mergeWithDefaultSettings($container['settings']['view']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return Twig
         */
        $container['view'] = function (Container $c) {

            $settings = $c['settings']['view'];
            $path     = rtrim($settings['path'], '/') . '/';

            $view = new Twig($path, $settings['options']);
            $view->addExtension(
                new TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                )
            );
            $view->addExtension(new \Twig_Extension_Debug());

            // Add extensions (must be loaded via e. g. composer)
            foreach ($settings['extensions'] as $extension) {
                $extension = is_callable($extension) ? $extension() : new $extension();
                $view->addExtension($extension);
            }

            return $view;
        };
    }
}
