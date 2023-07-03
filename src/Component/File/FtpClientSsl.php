<?php

namespace Ansas\Component\File;

class FtpClientSsl extends FtpClient
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $host, int $port = null, array $options = [])
    {
        $this->host = $host;

        if (!$this->ftp = $this->execute('ftp_ssl_connect', $this->host, $port ?? 21, $options['timeout'] ?? 30)) {
            $this->throwException("Cannot connect to host %s", $this->host);
        }
    }
}
