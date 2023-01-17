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
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * <code>composer require symfony/mailer</code>
 *
 * @see     https://symfony.com/doc/current/mailer.html
 *
 * @author  Ansas Meyer <ansas@preigu.com>
 * @author  Eike Grundke <eike@preigu.com>
 */
class SymfonyMailerProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    public static function getDefaultSettings(): array
    {
        /**
         * Examples for dsn:
         *
         * smtp://user:pass@smtp.example.com:25
         * sendmail://default
         * native://default
         * null://null
         *
         * @see https://symfony.com/doc/current/mailer.html#using-built-in-transports
         */
        return [
            'from'      => [
                'name'  => 'me',
                'email' => 'me@localhost',
            ],
            'dsn' => "sendmail://default",
            'options'   => [],
        ];
    }

    /**
     * {@inheritDoc}
     * @throws Exception
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
         * @return MailerInterface
         * @throws Exception
         */
        $container['mailer'] = function (Container $c) {

            $settings = $c['settings']['mailer'];

            $dsn = $settings['dsn'] ?? null;

            if (!$dsn) {
                throw new Exception('dsn for mailer missing');
            }

            $transport = Transport::fromDsn($dsn);
            return new Mailer($transport);
        };

        $container['mail'] = $container->factory(function (Container $c) {

            $settings = $c['settings']['mailer'];

            $email = new Email();
            $email->from(new Address($settings['from']['email'], $settings['from']['name']));

            $bcc = $settings['bcc'] ?? null;
            if ($bcc) {
                $email->bcc(new Address($bcc));
            }

            $returnPath = $settings['returnPath'] ?? null;
            if ($returnPath) {
                $email->returnPath(new Address($returnPath));
            }

            return $email;
        });
    }
}
