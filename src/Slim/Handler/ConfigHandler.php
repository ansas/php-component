<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Slim\Handler;

use Ansas\Component\Collection\CollectionOverride;
use Exception;
use Traversable;

/**
 * Class ConfigHandler
 *
 * @package Ansas\Slim\Handler
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ConfigHandler
{
    /**
     * @var CollectionOverride Handler for manipulating config (settings)
     */
    protected $handler;

    /**
     * @var string Path to config override files
     */
    protected $path;

    /**
     * @var string Suffix of config files
     */
    protected $suffix;

    /**
     * @var string Container key to config in override files
     */
    protected $key;

    /**
     * CollectionOverride constructor.
     *
     * @param Traversable $config
     */
    public function __construct(Traversable $config)
    {
        $this->handler = new CollectionOverride($config);
    }

    /**
     * Create new instance.
     *
     * @param Traversable $config
     *
     * @return static
     */
    public static function create(Traversable $config)
    {
        return new static($config);
    }

    /**
     * Returns a list of all available overrides.
     *
     * Format: [$identifier => $file].
     *
     * @return array
     */
    public function getAvailableOverrides()
    {
        $path   = $this->getPath();
        $suffix = $this->getSuffix();

        $glob   = "{$path}/*{$suffix}";
        $result = [];
        foreach (glob($glob) as $file) {
            $identifier          = basename($file, $suffix);
            $result[$identifier] = $file;
        }

        return $result;
    }

    /**
     * Returns the container key to config (settings).
     *
     * @return string
     * @throws Exception
     */
    public function getKey()
    {
        if (null === $this->key) {
            throw new Exception("Value for 'key' must be set");
        }

        return $this->key;
    }

    /**
     * Returns the path to config override files.
     *
     * @return string
     * @throws Exception
     */
    public function getPath()
    {
        if (null === $this->path) {
            throw new Exception("Value for 'path' must be set");
        }

        return $this->path;
    }

    /**
     * Returns the config (settings) collection.
     *
     * @return Traversable
     */
    public function get()
    {
        return $this->handler->get();
    }

    /**
     * Returns the suffix of all config files..
     *
     * @return string
     * @throws Exception
     */
    public function getSuffix()
    {
        if (null === $this->suffix) {
            throw new Exception("Value for 'suffix' must be set");
        }

        return $this->suffix;
    }

    /**
     * Override config (settings) with data stored in $identifier file.
     *
     * @param string $identifier
     * @param bool   $force [optional] Force override and throw Exception on error.
     *
     * @return string
     * @throws Exception
     */
    public function overwrite($identifier, $force = true)
    {
        $path   = $this->getPath();
        $suffix = $this->getSuffix();
        $key    = $this->getKey();
        $file   = "{$path}/{$identifier}{$suffix}";

        if (!file_exists($file)) {
            if ($force) {
                throw new Exception("Could not override config: File {$file} does not exist");
            }

            return $this;
        }

        $data = require $file;

        if (!isset($data[$key])) {
            throw new Exception("Could not override config: Key {$key} does not exist in file {$file}");
        }

        $config = $data[$key];
        $this->handler->override($config);

        return $this;
    }

    /**
     * Make changes / overrides to config (settings) permanent by deleting restore points.
     *
     * @return $this
     */
    public function persist()
    {
        $this->handler->persist();

        return $this;
    }

    /**
     * Restore config (settings) to previous status before last override() call if available.
     *
     * @param bool $force [optional] Force restore and throw Exception on error.
     *
     * @return $this
     * @throws Exception
     */
    public function restore($force = true)
    {
        $ok = $this->handler->restore();

        if (!$ok && $force) {
            throw new Exception("Could not restore settings: No restore point available");
        }

        return $this;
    }

    /**
     * Set container key to config (settings).
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Set path to config override files.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = realpath($path);

        return $this;
    }

    /**
     * Set suffix of all config files.
     *
     * @param string $suffix
     *
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }
}
