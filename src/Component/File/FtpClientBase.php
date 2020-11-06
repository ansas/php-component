<?php

namespace Ansas\Component\File;

use Exception;

abstract class FtpClientBase implements FtpClientInterface
{
    /**
     * @var string Error message
     */
    protected $error;

    /**
     * @var string Host
     */
    protected $host;

    /**
     * @param string $func
     * @param mixed  ...$args [optional]
     *
     * @return mixed
     */
    protected function execute(string $func, ...$args)
    {
        $this->error = null;

        set_error_handler([$this, 'handleError']);

        $result = $func(... $args);

        restore_error_handler();

        return $result;
    }

    /**
     * @param array  $files
     * @param string $regex       [optional]
     * @param bool   $returnFirst [optional]
     *
     * @return string|string[]
     */
    protected function filterFiles(array $files, string $regex, bool $returnFirst)
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
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function handleError(int $code, string $message, string $file = '', int $line = 0, array $context = [])
    {
        $this->error = $message;
    }

    /**
     * @param string $message
     * @param mixed  ...$args [optional]
     *
     * @throws Exception
     */
    protected function throwException(string $message, ...$args)
    {
        if ($this->error) {
            $message .= ' [%s]';
            $args[]  = $this->error;
        }

        throw new Exception(vsprintf($message, $args));
    }

}
