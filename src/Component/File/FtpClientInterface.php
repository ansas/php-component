<?php

namespace Ansas\Component\File;

use Exception;
use SensitiveParameter;

interface FtpClientInterface
{
    /**
     * @throws Exception
     */
    public function __construct(string $host, ?int $port = 21, array $options = []);

    /**
     * Close connection.
     */
    public function __destruct();

    /**
     * Login to server.
     *
     * @throws Exception
     */
    public function login(string $user, #[SensitiveParameter] string $password): static;

    /**
     * Change into $dir on ftp-server.
     *
     * @throws Exception
     */
    public function chdir(string $dir): static;

    /**
     * Check if file exists on ftp-server.
     */
    public function exists(string $remoteFile): bool;

    /**
     * List (specified) files in directory on ftp-server.
     *
     * @throws Exception
     */
    public function listFiles(string $dir = ".", string $regex = "", bool $returnFirst = false): array|string;

    /**
     * Get a file from ftp-server.
     *
     * @throws Exception
     */
    public function get(string $remoteFile, ?string $localFile = null): static;

    /**
     * Switch to active or passive mode.
     *
     * @throws Exception
     */
    public function passive(bool $passive): static;

    /**
     * Put a file on ftp-server.
     *
     * @throws Exception
     */
    public function put(string $remoteFile, ?string $localFile = null): static;

    /**
     * Delete a file from ftp-server.
     *
     * @throws Exception
     */
    public function delete(string $remoteFile): static;

    /**
     * Put a file on ftp-server.
     *
     * @throws Exception
     */
    public function rename(string $oldName, string $newName): static;

    /**
     * Get size of file on ftp-server.
     *
     * @throws Exception
     */
    public function getSize(string $remoteFile): int;

    /**
     * Get timestamp of last modification of file on ftp-server.
     *
     * @throws Exception
     */
    public function getModifiedTimestamp(string $remoteFile): int;
}
