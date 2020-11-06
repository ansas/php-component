<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\File;

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FtpClientSsl extends FtpClient
{
    /**
     * @inheritdoc
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $host, int $port = null, int $timeout = 30)
    {
        $this->host = $host;

        if (!$this->ftp = $this->execute('ftp_ssl_connect', $this->host, $port ?? 21, $timeout)) {
            $this->throwException("Cannot connect to host %s", $this->host);
        }
    }
}
