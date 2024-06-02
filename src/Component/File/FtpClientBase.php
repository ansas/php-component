<?php

namespace Ansas\Component\File;

use Exception;

abstract class FtpClientBase implements FtpClientInterface
{
    protected ?string $error;
    protected ?string $host;

    protected function execute(string $func, ...$args): mixed
    {
        $this->error = null;

        set_error_handler([$this, 'handleError']);

        $result = $func(... $args);

        restore_error_handler();

        return $result;
    }

    protected function filterFiles(array $files, string $regex, bool $returnFirst): array|string
    {
        // Make sure results are in ascending alphabetical order
        sort($files, SORT_STRING);

        $result = [];

        foreach ($files as $file) {
            // Remove path info from file (some ftp servers send file name incl. path)
            $file = pathinfo($file, PATHINFO_BASENAME);

            // Match files against regex (if set)
            if ($regex and !preg_match($regex, $file)) {
                continue;
            }

            // Return first result if flag is set
            if ($returnFirst) {
                return $file;
            }

            $result[] = $file;
        }

        if ($returnFirst) {
            return "";
        }

        return $result;
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    protected function handleError(int $code, string $message, string $file = '', int $line = 0, array $context = []): void
    {
        $this->error = $message;
    }

    /**
     * @throws Exception
     */
    protected function throwException(string $message, mixed ...$args)
    {
        if ($this->error) {
            $message .= ' [%s]';
            $args[]  = $this->error;
        }

        throw new Exception(vsprintf($message, $args));
    }

}
