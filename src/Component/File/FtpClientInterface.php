<?php

namespace Ansas\Component\File;

use Exception;

interface FtpClientInterface
{
    /**
     * @param string $host
     * @param int|null $port    [optional]
     *
     * @throws Exception
     */
    public function __construct(string $host, int $port = 21);

    /**
     * Close connection.
     */
    public function __destruct();

    /**
     * Login to server.
     *
     * @param string $user
     * @param string $password
     *
     * @return $this
     * @throws Exception
     */
    public function login(string $user, string $password);

    /**
     * Change into $dir on ftp-server.
     *
     * @param string $dir Remote dir path.
     *
     * @return $this
     * @throws Exception
     */
    public function chdir(string $dir);

    /**
     * Check if file exists on ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return bool
     */
    public function exists(string $remoteFile);

    /**
     * List (specified) files in directory on ftp-server.
     *
     * @param string $dir         [optional] Directory to search in.
     * @param string $regex       [optional] Match files against regex.
     * @param bool   $returnFirst [optional] Return first result only?
     *
     * @return array|string
     * @throws Exception
     */
    public function listFiles(string $dir = ".", string $regex = "", bool $returnFirst = false);

    /**
     * Get a file from ftp-server.
     *
     * @param string      $remoteFile Remote file path.
     * @param string|null $localFile  [optional] Local file path, default: $remoteFile.
     *
     * @return $this
     * @throws Exception
     */
    public function get(string $remoteFile, string $localFile = null);

    /**
     * Switch to active or passive mode.
     *
     * @param bool $passive
     *
     * @return $this
     * @throws Exception
     */
    public function passive(bool $passive);

    /**
     * Put a file on ftp-server.
     *
     * @param string      $remoteFile Remote file path.
     * @param string|null $localFile  [optional] Local file path, default: $remoteFile.
     *
     * @return $this
     * @throws Exception
     */
    public function put(string $remoteFile, string $localFile = null);

    /**
     * Delete a file from ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return $this
     * @throws Exception
     */
    public function delete(string $remoteFile);

    /**
     * Put a file on ftp-server.
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return $this
     * @throws Exception
     */
    public function rename(string $oldName, string $newName);

    /**
     * Get size of file on ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return int File size in byte.
     * @throws Exception
     */
    public function getSize(string $remoteFile);

    /**
     * Get timestamp of last modification of file on ftp-server.
     *
     * @param string $remoteFile Remote file path.
     *
     * @return int Timestamp.
     * @throws Exception
     */
    public function getModifiedTimestamp(string $remoteFile);
}
