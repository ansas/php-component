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

use Exception;

/**
 * Class FtpClient
 *
 * @package Ansas\Component\File
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FtpClient
{
    /**
     * @var string Host
     */
    private $host;

    /**
     * @var int Port
     */
    private $port;

    /**
     * @var string User
     */
    private $user;

    /**
     * @var string Password
     */
    private $password;

    /**
     * @var resource FTP handle
     */
    private $ftp;

    /**
     * FTP constructor.
     *
     * @param string $host
     * @param int    $port     [optional]
     * @param string $user     [optional]
     * @param string $password [optional]
     *
     * @throws Exception
     */
    public function __construct(string $host, int $port = 21, string $user = null, string $password = null)
    {
        $this->host     = $host;
        $this->user     = $user;
        $this->port     = $port;
        $this->password = $password;

        if (!$this->ftp = @ftp_connect($this->host, $this->port)) {
            throw new Exception(sprintf("Cannot connect to host %s", $this->host));
        }
    }

    /**
     * FTP destructor.
     */
    public function __destruct()
    {
        @ftp_close($this->ftp);
    }

    /**
     * Login to server.
     *
     * @param string $user                 [optional]
     * @param string $password             [optional]
     * @param int    $attempts             [optional] Number of retries in case of error.
     * @param int    $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     *
     * @return $this
     * @throws Exception
     */
    public function login(string $user = null, string $password = null, $attempts = 1, $sleepBetweenAttempts = 5)
    {
        if ($user) {
            $this->user = $user;
        }
        if ($password) {
            $this->password = $password;
        }

        if (!@ftp_login($this->ftp, $this->user, $this->password)) {
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->login($user, $password, $attempts, $sleepBetweenAttempts);
            }

            throw new Exception(sprintf("Cannot login to host %s", $this->host));
        }

        return $this;
    }

    /**
     * Change into $dir on ftp-server.
     *
     * @param string $dir Remote dir path.
     *
     * @return $this
     * @throws Exception
     */
    public function chdir(string $dir)
    {
        if ($dir != '/') {
            $dir = rtrim($dir, '/');
        }
        if (!$dir) {
            throw new Exception("First argument must not be empty");
        }

        if (!@ftp_chdir($this->ftp, $dir)) {
            throw new Exception(sprintf("Cannot chdir to %s", $dir));
        }

        return $this;
    }

    /**
     * List (specified) files in directory on ftp-server.
     *
     * @param string $dir                  [optional] Directory to search in.
     * @param string $regex                [optional] Match files against regex.
     * @param bool   $returnFirst          [optional] Return first result only?
     * @param int    $attempts             [optional] Number of retries in case of error.
     * @param int    $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     *
     * @return array|string
     * @throws Exception
     */
    public function listFiles(
        string $dir = ".", string $regex = "", bool $returnFirst = false, int $attempts = 5,
        int $sleepBetweenAttempts = 5
    ) {
        // Get total list of files of given dir
        $total = @ftp_nlist($this->ftp, $dir);

        if ($total === false) {
            // Check if tries left call method again
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->listFiles($dir, $regex, $returnFirst, $attempts, $sleepBetweenAttempts);
            }

            throw new Exception(sprintf("Cannot list files in %s with regex %s", $dir, $regex));
        }

        $result = [];

        foreach ($total as $file) {
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
        $total = @ftp_rawlist($this->ftp, $dir);

        if ($total === false) {
            // Check if tries left call method again
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->listFilesRaw($dir, $attempts, $sleepBetweenAttempts);
            }

            throw new Exception(sprintf("Cannot list files in %s", $dir));
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
     * Get a file from ftp-server.
     *
     * @param string|null $remoteFile Remote file path.
     * @param string      $localFile  [optional] Local file path, default: $remoteFile.
     * @param int         $mode       [optional] Transfer mode, allowed: FTP_ASCII or FTP_BINARY
     *
     * @return $this
     * @throws Exception
     */
    public function get(string $remoteFile, string $localFile = null, int $mode = FTP_BINARY)
    {
        if (!$localFile) {
            $localFile = $remoteFile;
        }

        if (!@ftp_get($this->ftp, $localFile, $remoteFile, $mode)) {
            throw new Exception(sprintf("Cannot copy file from %s to %s", $remoteFile, $localFile));
        }

        return $this;
    }

    /**
     * Get a file from ftp-server and write it directly into file.
     *
     * @param string   $remoteFile Remote file path.
     * @param resource $handle     File handle.
     * @param int      $resumePos  [optional] Start or resume position in file.
     *
     * @return $this
     * @throws Exception
     */
    public function fget(string $remoteFile, resource $handle, int $resumePos = 0)
    {
        if (!@ftp_fget($this->ftp, $handle, $remoteFile, FTP_BINARY, $resumePos)) {
            throw new Exception("Cannot write in file handle");
        }

        return $this;
    }

    /**
     * Switch to active or passive mode.
     *
     * @param bool $passive
     *
     * @return $this
     * @throws Exception
     */
    public function passive(bool $passive)
    {
        if (!@ftp_pasv($this->ftp, $passive)) {
            throw new Exception(sprintf("Cannot switch to passive = %s", $passive ? "true" : "false"));
        }

        return $this;
    }

    /**
     * Put a file on ftp-server.
     *
     * @param string      $remoteFile Remote file path.
     * @param string|null $localFile  [optional] Local file path, default: $remoteFile.
     * @param int         $mode       [optional] Transfer mode, allowed: FTP_ASCII or FTP_BINARY
     *
     * @return $this
     * @throws Exception
     */
    public function put(string $remoteFile, string $localFile = null, int $mode = FTP_BINARY)
    {
        if (!$localFile) {
            $localFile = $remoteFile;
        }

        if (!@ftp_put($this->ftp, $remoteFile, $localFile, $mode)) {
            throw new Exception(sprintf("Cannot copy file from %s to %s", $localFile, $remoteFile));
        }

        return $this;
    }

    /**
     * Read directly from file and put data on ftp-server.
     *
     * @param string   $remoteFile Remote file path.
     * @param resource $handle     File handle.
     * @param int      $resumePos  [optional] Start or resume position in file.
     *
     * @return $this
     * @throws Exception
     */
    public function fput(string $remoteFile, resource $handle, int $resumePos = 0)
    {
        if (!@ftp_fput($this->ftp, $remoteFile, $handle, FTP_BINARY, $resumePos)) {
            throw new Exception(sprintf("Cannot copy data from file handle to %s", $remoteFile));
        }

        return $this;
    }

    /**
     * Delete a file from ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return $this
     * @throws Exception
     */
    public function delete(string $remoteFile)
    {
        if (!@ftp_delete($this->ftp, $remoteFile)) {
            throw new Exception(sprintf("Cannot delete file %s", $remoteFile));
        }

        return $this;
    }

    /**
     * Put a file on ftp-server.
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return $this
     * @throws Exception
     */
    public function rename(string $oldName, string $newName)
    {
        if (!@ftp_rename($this->ftp, $oldName, $newName)) {
            throw new Exception(sprintf("Cannot rename file from %s to %s", $oldName, $newName));
        }

        return $this;
    }

    /**
     * Get size of file on ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return int File size in byte.
     * @throws Exception
     */
    public function getSize(string $remoteFile)
    {
        $size = @ftp_size($this->ftp, $remoteFile);

        if ($size === false) {
            throw new Exception("Cannot get file size");
        }

        return $size;
    }

    /**
     * Get timestamp of last modification of file on ftp-server.
     *
     * @param string $remoteFile           Remote file path.
     * @param int    $attempts             [optional] Number of retries in case of error.
     * @param int    $sleepBetweenAttempts [optional] Sleep time in seconds between attempts.
     *
     * @return int Timestamp.
     * @throws Exception
     */
    public function getModifiedTimestamp(string $remoteFile, int $attempts = 2, int $sleepBetweenAttempts = 5)
    {
        $timestamp = @ftp_mdtm($this->ftp, $remoteFile);

        if ($timestamp < 0) {
            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->getModifiedTimestamp($remoteFile, $attempts, $sleepBetweenAttempts);
            }

            throw new Exception("Cannot get file modification timestamp");
        }

        return $timestamp;
    }
}
