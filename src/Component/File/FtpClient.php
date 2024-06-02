<?php

/** @noinspection SpellCheckingInspection */

namespace Ansas\Component\File;

use Exception;

class FtpClient extends FtpClientBase
{
    /**
     * @var resource|null
     */
    protected $ftp;

    public function __construct(string $host, int $port = null, array $options = [])
    {
        $this->host = $host;

        if (!$this->ftp = $this->execute('ftp_connect', $this->host, $port ?? 21, $options['timeout'] ?? 30)) {
            $this->throwException("Cannot connect to host %s", $this->host);
        }
    }

    public function __destruct()
    {
        $this->execute('ftp_close', $this->ftp);
    }

    public function login(string $user, string $password, int $attempts = 1, int $sleepBetweenAttempts = 5): static
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

    public function chdir(string $dir): static
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

    public function exists(string $remoteFile): bool
    {
        return $this->execute('ftp_size', $this->ftp, $remoteFile) >= 0;
    }

    public function listFiles(
        string $dir = ".",
        string $regex = "",
        bool $returnFirst = false,
        int $attempts = 5,
        int $sleepBetweenAttempts = 5
    ): array|string {
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
     * @throws Exception
     */
    public function listFilesRaw(string $dir = ".", int $attempts = 5, int $sleepBetweenAttempts = 5): array
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
            $data = ['raw' => $rawString];

            $cols = preg_split("/\s+/u", $rawString, -1, PREG_SPLIT_NO_EMPTY);
            if (count($cols) < count($columnMap)) {
                $this->throwException("Cannot parse rawlist row: %s", json_encode($data + ['cols' => $cols]));
            }

            foreach ($cols as $col => $value) {
                if ($col > 8) { // Filename with spaces
                    $data[$columnMap[8]] .= " " . $value;
                } else {
                    $data[$columnMap[$col]] = $value;
                }
            }

            $data['month'] = $monthMap[$data['month']];
            $data['time']  = "00:00";

            if (str_contains($data['year'], ':')) {
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

    public function get(
        string $remoteFile,
        string $localFile = null,
        int $mode = FTP_BINARY,
        int $attempts = 1,
        int $sleepBetweenAttempts = 5
    ): static {
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
     * @throws Exception
     */
    public function fget(string $remoteFile, mixed $handle, int $resumePos = 0): static
    {
        if (!$this->execute('ftp_fget', $this->ftp, $handle, $remoteFile, FTP_BINARY, $resumePos)) {
            $this->throwException("Cannot write in file handle");
        }

        return $this;
    }

    public function passive(bool $passive): static
    {
        if (!$this->execute('ftp_pasv', $this->ftp, $passive)) {
            $this->throwException("Cannot switch to passive = %s", $passive ? "true" : "false");
        }

        return $this;
    }

    public function put(string $remoteFile, string $localFile = null, int $mode = FTP_BINARY): static
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
     * @throws Exception
     */
    public function fput(string $remoteFile, mixed $handle, int $resumePos = 0): static
    {
        if (!$this->execute('ftp_fput', $this->ftp, $remoteFile, $handle, FTP_BINARY, $resumePos)) {
            $this->throwException("Cannot copy data from file handle to %s", $remoteFile);
        }

        return $this;
    }

    public function delete(string $remoteFile): static
    {
        if (!$this->execute('ftp_delete', $this->ftp, $remoteFile)) {
            $this->throwException("Cannot delete file %s", $remoteFile);
        }

        return $this;
    }

    public function rename(string $oldName, string $newName): static
    {
        if (!$this->execute('ftp_rename', $this->ftp, $oldName, $newName)) {
            $this->throwException("Cannot rename file from %s to %s", $oldName, $newName);
        }

        return $this;
    }

    public function getSize(string $remoteFile): int
    {
        $size = $this->execute('ftp_size', $this->ftp, $remoteFile);

        if ($size == -1) {
            $this->throwException("Cannot get file size");
        }

        return $size;
    }

    public function getModifiedTimestamp(string $remoteFile, int $attempts = 1, int $sleepBetweenAttempts = 5): int
    {
        // Try to get timestamp from default FPT function
        $timestamp = $this->execute('ftp_mdtm', $this->ftp, $remoteFile);

        if ($timestamp < 0) {
            // Try to build timestamp from raw list (if ftp_mdtm is not supported)
            $path = pathinfo($remoteFile, PATHINFO_DIRNAME);
            $file = pathinfo($remoteFile, PATHINFO_BASENAME);
            foreach ($this->listFilesRaw($path, 1) as $raw) {
                if ($raw['name'] == $file) {
                    return strtotime(sprintf(
                        '%d-%d-%d %s',
                        $raw['year'],
                        $raw['month'],
                        $raw['day'],
                        $raw['time']
                    ));
                }
            }

            if (--$attempts > 0) {
                sleep($sleepBetweenAttempts);

                return $this->getModifiedTimestamp($remoteFile, $attempts, $sleepBetweenAttempts);
            }

            $this->throwException("Cannot get file modification timestamp");
        }

        return $timestamp;
    }
}
