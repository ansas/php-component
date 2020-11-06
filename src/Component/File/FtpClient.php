<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

/** @noinspection SpellCheckingInspection */

namespace Ansas\Component\File;

use Exception;

/**
 * Class FtpClient
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FtpClient extends FtpClientBase
{
    /**
     * @var resource FTP handle
     */
    protected $ftp;

    /**
     * @inheritdoc
     *
     * @param int $timeout [optional]
     */
    public function __construct(string $host, int $port = null, int $timeout = 30)
    {
        $this->host = $host;

        if (!$this->ftp = $this->execute('ftp_connect', $this->host, $port ?? 21, $timeout)) {
            $this->throwException("Cannot connect to host %s", $this->host);
        }
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        $this->execute('ftp_close', $this->ftp);
    }

    /**
     * @inheritdoc
     *
     * @param int $attempts             [optional] Number of retries in case of error.
     * @param int $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     */
    public function login(string $user, string $password, $attempts = 1, $sleepBetweenAttempts = 5)
    {
        if (!$this->execute('ftp_login', $this->ftp, $user, $password)) {
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->login($user, $password, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot login to host %s", $this->host);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function chdir(string $dir)
    {
        if ($dir != '/') {
            $dir = rtrim($dir, '/');
        }
        if (!$dir) {
            $this->throwException("Remote dir must not be empty");
        }

        if (!$this->execute('ftp_chdir', $this->ftp, $dir)) {
            $this->throwException("Cannot chdir to %s", $dir);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function exists(string $remoteFile)
    {
        return $this->execute('ftp_size', $this->ftp, $remoteFile) >= 0;
    }

    /**
     * @inheritdoc
     *
     * @param int $attempts             [optional] Number of retries in case of error.
     * @param int $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     */
    public function listFiles(
        string $dir = ".",
        string $regex = "",
        bool $returnFirst = false,
        int $attempts = 5,
        int $sleepBetweenAttempts = 5
    ) {
        // Get total list of files of given dir
        $files = $this->execute('ftp_nlist', $this->ftp, $dir);

        if ($files === false) {
            // Check if tries left call method again
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->listFiles($dir, $regex, $returnFirst, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot list files in %s with regex %s", $dir, $regex);
        }

        return $this->filterFiles($files, $regex, $returnFirst);

    }

    /**
     * Get raw data of files in directory on ftp-server.
     *
     * @param string $dir                  [optional] Directory to search in.
     * @param int    $attempts             [optional] Number of retries in case of error.
     * @param int    $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     *
     * @return array
     * @throws Exception
     */
    public function listFilesRaw(string $dir = ".", int $attempts = 5, int $sleepBetweenAttempts = 5)
    {
        $total = $this->execute('ftp_rawlist', $this->ftp, $dir);

        if ($total === false) {
            // Check if tries left call method again
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->listFilesRaw($dir, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot list files in %s", $dir);
        }

        $columnMap = [
            "permissions",
            "number",
            "owner",
            "group",
            "size",
            "month",
            "day",
            "year",
            "name",
        ];

        $monthMap = [
            'Jan'  => '01',
            'Feb'  => '02',
            'Mar'  => '03',
            'Apr'  => '04',
            'May'  => '05',
            'Jun'  => '06',
            'Jul'  => '07',
            'Aug'  => '08',
            'Sep'  => '09',
            'Sept' => '09',
            'Oct'  => '10',
            'Nov'  => '11',
            'Dec'  => '12',
        ];

        $files = [];
        foreach ($total as $rawString) {
            $data    = [];
            $rawList = preg_split("/\s*/", $rawString, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($rawList as $col => $value) {
                if ($col > 8) { // Filename with spaces
                    $data[$columnMap[8]] .= " " . $value;
                    continue;
                }
                $data[$columnMap[$col]] = $value;
            }

            $data['month'] = $monthMap[$data['month']];
            $data['time']  = "00:00";

            if (strpos($data['year'], ':') !== false) {
                $data['time'] = $data['year'];
                if ((int) $data['month'] > (int) date('m')) {
                    $data['year'] = date('Y', time() - 60 * 60 * 24 * 365);
                } else {
                    $data['year'] = date('Y');
                }
            }

            $files[] = $data;
        }

        return $files;
    }

    /**
     * @inheritdoc
     *
     * @param int $mode                 [optional] Transfer mode, allowed: FTP_ASCII or FTP_BINARY
     * @param int $attempts             [optional] Number of retries in case of error.
     * @param int $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     */
    public function get(
        string $remoteFile,
        string $localFile = null,
        int $mode = FTP_BINARY,
        int $attempts = 1,
        int $sleepBetweenAttempts = 5
    ) {
        if (!$localFile) {
            $localFile = $remoteFile;
        }

        if (!$this->execute('ftp_get', $this->ftp, $localFile, $remoteFile, $mode)) {
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->get($remoteFile, $localFile, $mode, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot copy file from %s to %s", $remoteFile, $localFile);
        }

        return $this;
    }

    /**
     * Get a file from ftp-server and write it directly into file.
     *
     * @param string $remoteFile Remote file path.
     * @param mixed  $handle     File handle.
     * @param int    $resumePos  [optional] Start or resume position in file.
     *
     * @return $this
     * @throws Exception
     */
    public function fget(string $remoteFile, $handle, int $resumePos = 0)
    {
        if (!$this->execute('ftp_fget', $this->ftp, $handle, $remoteFile, FTP_BINARY, $resumePos)) {
            $this->throwException("Cannot write in file handle");
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function passive(bool $passive)
    {
        if (!$this->execute('ftp_pasv', $this->ftp, $passive)) {
            $this->throwException("Cannot switch to passive = %s", $passive ? "true" : "false");
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param int $mode [optional] Transfer mode, allowed: FTP_ASCII or FTP_BINARY
     */
    public function put(string $remoteFile, string $localFile = null, int $mode = FTP_BINARY)
    {
        if (!$localFile) {
            $localFile = $remoteFile;
        }

        if (!$this->execute('ftp_put', $this->ftp, $remoteFile, $localFile, $mode)) {
            $this->throwException("Cannot copy file from %s to %s", $localFile, $remoteFile);
        }

        return $this;
    }

    /**
     * Read directly from file and put data on ftp-server.
     *
     * @param string $remoteFile Remote file path.
     * @param mixed  $handle     File handle.
     * @param int    $resumePos  [optional] Start or resume position in file.
     *
     * @return $this
     * @throws Exception
     */
    public function fput(string $remoteFile, $handle, int $resumePos = 0)
    {
        if (!$this->execute('ftp_fput', $this->ftp, $remoteFile, $handle, FTP_BINARY, $resumePos)) {
            $this->throwException("Cannot copy data from file handle to %s", $remoteFile);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $remoteFile)
    {
        if (!$this->execute('ftp_delete', $this->ftp, $remoteFile)) {
            $this->throwException("Cannot delete file %s", $remoteFile);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rename(string $oldName, string $newName)
    {
        if (!$this->execute('ftp_rename', $this->ftp, $oldName, $newName)) {
            $this->throwException("Cannot rename file from %s to %s", $oldName, $newName);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSize(string $remoteFile)
    {
        $size = $this->execute('ftp_size', $this->ftp, $remoteFile);

        if ($size == -1) {
            $this->throwException("Cannot get file size");
        }

        return $size;
    }

    /**
     * @inheritdoc
     *
     * @param int $attempts             [optional] Number of retries in case of error.
     * @param int $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     */
    public function getModifiedTimestamp(string $remoteFile, int $attempts = 1, int $sleepBetweenAttempts = 5)
    {
        $timestamp = $this->execute('ftp_mdtm', $this->ftp, $remoteFile);

        if ($timestamp < 0) {
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->getModifiedTimestamp($remoteFile, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot get file modification timestamp");
        }

        return $timestamp;
    }
}
