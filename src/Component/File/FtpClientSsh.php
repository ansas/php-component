<?php

namespace Ansas\Component\File;

use Ansas\Util\Path;
use Ansas\Util\Text;
use Exception;

class FtpClientSsh extends FtpClientBase
{
    /**
     * @var resource|null
     */
    protected $ssh;

    /**
     * @var resource|null
     */
    protected $sftp;

    protected string $dir = '/';

    /**
     * @throws Exception
     */
    public function __construct(string $host, int $port = null, array $options = [])
    {
        $this->host = $host;

        if (!$this->ssh = $this->execute('ssh2_connect', $host, $port ?? 22)) {
            $this->throwException("Cannot connect to host %s", $this->host);
        }

        //Check server fingerprint (if defined)
        if ($options['fingerprint'] ?? null) {
            $fingerprint = $this->getFingerprint();
            if ($options['fingerprint'] != $fingerprint) {
                $this->throwException("Fingerprint '%s' for host '%s' does not match", $fingerprint, $this->host);
            }
        }
    }

    public function __destruct()
    {
        if ($this->sftp) {
            $this->execute('ssh2_disconnect', $this->sftp);
        }
        if ($this->ssh) {
            $this->execute('ssh2_disconnect', $this->ssh);
        }
    }

    public function login(string $user, string $password): static
    {
        if (!$this->execute('ssh2_auth_password', $this->ssh, $user, $password)) {
            $this->throwException("Cannot login to host %s", $this->host);
        }

        return $this;
    }

    /**
     * Get server fingerprint
     */
    public function getFingerprint(): string
    {
        return $this->execute('ssh2_fingerprint', $this->ssh);
    }

    public function chdir(string $dir): static
    {
        if (Text::firstChar($dir) != '/') {
            $dir = Path::combine($this->pwd(), $dir, '/');
        }

        $this->dir = trim($dir, '/');

        if (!$this->execute('is_dir', $this->buildUrl($this->pwd()))) {
            $this->throwException("Cannot chdir to %s", $dir);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function exists(string $remoteFile): bool
    {
        return $this->execute('file_exists', $this->buildUrl($this->buildPath($remoteFile)));
    }

    public function listFiles(string $dir = "", string $regex = "", bool $returnFirst = false): array|string
    {
        /** @noinspection SpellCheckingInspection */
        if (!$handle = $this->execute('opendir', $this->buildUrl($this->buildPath($dir)))) {
            $this->throwException("Cannot list files in '%s'", $this->pwd());
        }

        $files = [];
        while (false !== ($file = readdir($handle))) {
            $files[] = $file;
        }
        closedir($handle);

        $files = array_diff($files, ['.', '..']);

        return $this->filterFiles($files, $regex, $returnFirst);
    }

    public function get(string $remoteFile, string $localFile = null): static
    {
        if (!$localFile) {
            $localFile = pathinfo($remoteFile, PATHINFO_BASENAME);
        }

        $path = $this->buildUrl($this->buildPath($remoteFile));
        $data = $this->execute('file_get_contents', $path);
        if (false === $data || false === $this->execute('file_put_contents', $localFile, $data)) {
            $this->throwException("Cannot get file from %s to %s", $remoteFile, $localFile);
        }

        return $this;
    }

    public function passive(bool $passive): static
    {
        return $this;
    }

    public function put(string $remoteFile, string $localFile = null): static
    {
        if (!$localFile) {
            $localFile = pathinfo($remoteFile, PATHINFO_BASENAME);
        }

        $path = $this->buildUrl($this->buildPath($remoteFile));
        $data = $this->execute('file_get_contents', $localFile);
        if (false === $data || false === $this->execute('file_put_contents', $path, $data)) {
            $this->throwException("Cannot put file from %s to %s", $localFile, $remoteFile);
        }

        return $this;
    }

    public function pwd(): string
    {
        return $this->dir;
    }

    public function delete(string $remoteFile): static
    {
        if (!$this->execute('ssh2_sftp_unlink', $this->getSftp(), $this->buildPath($remoteFile))) {
            $this->throwException("Cannot delete remote file '%s'", $remoteFile);
        }

        return $this;
    }

    public function rename(string $oldName, string $newName): static
    {
        if (!$this->execute('ssh2_sftp_rename',
            $this->getSftp(),
            $this->buildPath($oldName),
            $this->buildPath($newName))) {
            $this->throwException("Cannot rename file from '%s' to '%s'", $oldName, $newName);
        }

        return $this;
    }

    public function getSize(string $remoteFile): int
    {
        $size = $this->getFileStat($remoteFile, 'size', -1);

        if ($size == -1) {
            $this->throwException("Cannot get file size");
        }

        return $size;
    }

    public function getModifiedTimestamp(string $remoteFile): int
    {
        $timestamp = $this->getFileStat($remoteFile, 'mtime', -1);
        if ($timestamp < 0) {
            $this->throwException("Cannot get file modification timestamp");
        }

        return $timestamp;
    }

    /**
     * @throws Exception
     */
    protected function getFileStat(string $remoteFile, string $key = null, mixed $default = null): mixed
    {
        $stat = $this->execute('ssh2_sftp_stat', $this->getSftp(), $this->buildPath($remoteFile));

        if ($key) {
            return $stat[$key] ?? $default;
        }

        return $stat;
    }

    /**
     * @return resource
     * @throws Exception
     */
    protected function getSftp()
    {
        if (!$this->sftp) {
            if (!$this->sftp = $this->execute('ssh2_sftp', $this->ssh)) {
                $this->throwException("Cannot init SFTP for host %s", $this->host);
            }
        }

        return $this->sftp;
    }

    protected function buildPath(string $path = ''): string
    {
        return Path::combine($this->pwd(), $path, '/');
    }

    /**
     * @throws Exception
     */
    protected function buildUrl(string $path = ''): string
    {
        return Path::combine(sprintf('ssh2.sftp://%d', (int) $this->getSftp()), $path, '/');
    }
}
