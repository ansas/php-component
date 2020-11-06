<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace Ansas\Component\File;

use Ansas\Util\Path;
use Ansas\Util\Text;
use Exception;

/**
 * Class FtpClientSsh
 *
 * SFTP / SCP support
 *
 * @package Ansas\Component\File
 */
class FtpClientSsh extends FtpClientBase
{
    /**
     * @var resource
     */
    protected $ssh;

    /**
     * @var resource
     */
    protected $sftp;

    /**
     * @var string
     */
    protected $dir;

    /**
     * @param string   $host
     * @param int|null $port    [optional]
     * @param array    $options [optional]
     *
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

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        if ($this->sftp) {
            $this->execute('ssh2_disconnect', $this->sftp);
        }
        if ($this->ssh) {
            $this->execute('ssh2_disconnect', $this->ssh);
        }
    }

    /**
     * @inheritdoc
     */
    public function login(string $user, string $password)
    {
        if (!$this->execute('ssh2_auth_password', $this->ssh, $user, $password)) {
            $this->throwException("Cannot login to host %s", $this->host);
        }

        return $this;
    }

    /**
     * Get server fingerprint
     *
     * @return string
     */
    public function getFingerprint()
    {
        return $this->execute('ssh2_fingerprint', $this->ssh);
    }

    /**
     * @inheritdoc
     */
    public function chdir(string $dir)
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
     * @inheritdoc
     * @throws Exception
     */
    public function exists(string $remoteFile)
    {
        return $this->execute('file_exists', $this->buildUrl($this->buildPath($remoteFile)));
    }

    /**
     * @inheritdoc
     */
    public function listFiles(string $dir = "", string $regex = "", bool $returnFirst = false)
    {
        /** @noinspection SpellCheckingInspection */
        if (!$handle = $this->execute('opendir', $this->buildUrl($this->buildPath($dir)))) {
            $this->throwException("Cannot list files in '%s'", $this->pwd());
        }

        $files = [];
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $files[] = $file;
            }
        }
        closedir($handle);

        return $this->filterFiles($files, $regex, $returnFirst);
    }

    /**
     * @inheritdoc
     */
    public function get(string $remoteFile, string $localFile = null)
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

    /**
     * @inheritdoc
     */
    public function passive(bool $passive)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function put(string $remoteFile, string $localFile = null)
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

    /**
     * @string
     */
    public function pwd()
    {
        return $this->dir ?: '/';
    }

    /**
     * @inheritdoc
     */
    public function delete(string $remoteFile)
    {
        if (!$this->execute('ssh2_sftp_unlink', $this->getSftp(), $this->buildPath($remoteFile))) {
            $this->throwException("Cannot delete remote file '%s'", $remoteFile);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rename(string $oldName, string $newName)
    {
        if (!$this->execute('ssh2_sftp_rename',
            $this->getSftp(),
            $this->buildPath($oldName),
            $this->buildPath($newName))) {
            $this->throwException("Cannot rename file from '%s' to '%s'", $oldName, $newName);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSize(string $remoteFile)
    {
        $size = $this->getFileStat($remoteFile, 'size', -1);

        if ($size == -1) {
            $this->throwException("Cannot get file size");
        }

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function getModifiedTimestamp(string $remoteFile)
    {
        $timestamp = $this->getFileStat($remoteFile, 'mtime', -1);
        if ($timestamp < 0) {
            $this->throwException("Cannot get file modification timestamp");
        }

        return $timestamp;
    }

    /**
     * @param string      $remoteFile
     * @param string|null $key     [optional]
     * @param mixed       $default [optional]
     *
     * @return mixed
     * @throws Exception
     */
    protected function getFileStat(string $remoteFile, string $key = null, $default = null)
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

    /**
     * @param string $path [optional]
     *
     * @return string
     */
    protected function buildPath(string $path = '')
    {
        return Path::combine($this->pwd(), $path, '/');
    }

    /**
     * @param string $path [optional]
     *
     * @return string
     * @throws Exception
     */
    protected function buildUrl(string $path = '')
    {
        return Path::combine(sprintf('ssh2.sftp://%d', (int) $this->getSftp()), $path, '/');
    }
}
