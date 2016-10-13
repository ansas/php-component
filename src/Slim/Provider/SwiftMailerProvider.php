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
use Pimple\Container;
use Swift_Mailer;
use Swift_MailTransport;
use Swift_Message;
use Swift_NullTransport;
use Swift_SmtpTransport;

/**
 * Class SwiftMailerProvider
 *
 * <code>composer require swiftmailer/swiftmailer</code>
 *
 * @see     http://swiftmailer.org/docs/introduction.html
 *
 * @package Ansas\Slim\Provider
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class SwiftMailerProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings()
    {
        return [
            'from'      => [
                'name'  => 'me',
                'email' => 'me@localhost',
            ],
            'transport' => "mail",  // mail|null|smtp
            'options'   => [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(Container $container)
    {
        // Append custom settings with missing params from default settings
        $container['settings']['mailer'] = self::mergeWithDefaultSettings($container['settings']['mailer']);

        /**
         * Add dependency (DI).
         *
         * @param Container $c
         *
         * @return Swift_Mailer
         * @throws Exception
         */
        $container['mailer'] = function (Container $c) {

            $settings = $c['settings']['mailer'];

            switch ($settings['transport']) {
                case 'mail':
                    $transport = Swift_MailTransport::newInstance();
                    break;
                case 'null':
                    $transport = Swift_NullTransport::newInstance();
                    break;
                case 'smtp':
                    $options   = $settings['options'];
                    $transport = Swift_SmtpTransport
                        ::newInstance()
                        ->setHost($options['host'] ?? 'localhost')
                        ->setPort($options['port'] ?? '25')
                        ->setEncryption($options['encryption'] ?? 'tls')
                        ->setUsername($options['username'] ?? '')
                        ->setPassword($options['password'] ?? '')
                    ;
                    break;
                default:
                    throw new Exception("Transport {$settings['transport']} not supported");
            }

            $mailer = Swift_Mailer::newInstance($transport);

            return $mailer;
        };

        $container['mail'] = $container->factory(function (Container $c) {

            $settings = $c['settings']['mailer'];

            $message = Swift_Message
                ::newInstance()
                ->setFrom($settings['from']['email'], $settings['from']['name'])
            ;

            $bcc = $settings['bcc'] ?? null;
            if ($bcc) {
                $message->setBcc($bcc);
            }

            $returnPath = $settings['returnPath'] ?? null;
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }

            return $message;
        });
    }
}
