<?php declare(strict_types=1);

/**
 * This file is part of the PHP components package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Ansas\Monolog;

use Exception;
use Monolog\Logger;

/**
 * Profiler
 *
 * A slim stop watch for different profiles that are logged to any Monolog
 * logger.
 *
 * @author Ansas Meyer <webmaster@ansas-meyer.de>
 */
class Profiler
{
    protected $start;
    protected $stash;
    protected $logger;
    protected $level;
    protected $formatter;

    public function __construct(Logger $logger, $level = Logger::DEBUG, callable $formatter = null)
    {
        $this->start = $_SERVER['REQUEST_TIME_FLOAT'] ?? $this->snapshot();

        $this->clear();

        $this->setLogger($logger);
        $this->setLevel($level);
        $this->setFormatter($formatter);

        $this->save(null, 'START', null);
    }

    public function __destruct()
    {
        $this->quit();
    }

    public function start(string $profile)
    {
        if (!strlen($profile)) {
            throw new Exception("Profile must be set.");
        }

        if ($this->isRunning($profile)) {
            throw new Exception("Profile {$profile} already stated.");
        }

        $starttime = $this->snapshot();

        $this->stash[$profile] = [
            'start' => $starttime,
            'lap'   => null,
        ];

        return $starttime;
    }

    public function lap($profile = null, $message = 'Laptime')
    {
        $profile = $profile ?? $this->lastStartedProfile();

        if (!$this->isRunning($profile)) {
            throw new Exception("Profile {$profile} does not exist.");
        }

        $laptime = $this->snapshot();
        $runtime = $laptime - ($this->stash[$profile]['lap'] ?? $this->stash[$profile]['start']);
        $this->stash[$profile]['lap'] = $laptime;

        $this->save($profile, $message, $runtime);
        return $runtime;
    }

    public function stop($profile = null, $message = 'Runtime')
    {
        $profile = $profile ?? $this->lastStartedProfile();

        if (!$this->isRunning($profile)) {
            throw new Exception("Profile {$profile} does not exist.");
        }

        if (isset($this->stash[$profile]['lap'])) {
            $this->lap($profile);
        }
        $stoptime = $this->snapshot();
        $runtime = $stoptime - $this->stash[$profile]['start'];

        unset($this->stash[$profile]);

        $this->save($profile, $message, $runtime);
        return $runtime;
    }

    public function clear()
    {
        $this->stash = [];
        return $this;
    }

    public function lastStartedProfile()
    {
        end($this->stash);
        return key($this->stash);
    }

    public function countProfiles()
    {
        return count($this->stash);
    }

    public function defaultFormatter()
    {
        return function (float $var) {
            return number_format($var, 3, ',', '.') . " sec.";
        };
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getProfiles()
    {
        return array_keys($this->stash);
    }

    public function isRunning($profile)
    {
        return isset($this->stash[$profile]);
    }

    public function setLevel($level)
    {
        Logger::getLevelName($level);
        $this->level = $level;
        return $this;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function setFormatter(callable $formatter = null)
    {
        $this->formatter = $formatter ?? $this->defaultFormatter();
        return $this;
    }

    public function quit()
    {
        while ($this->countProfiles()) {
            $this->stop($this->lastStartedProfile());
        }

        $runtime = $this->snapshot() - $this->start;
        $this->save(null, 'QUIT', $runtime);
    }

    protected function save($profile, $message, $runtime)
    {
        $context = [];
        $profile && $context['profile'] = $profile;
        $runtime && $context['runtime'] = $this->getFormatter()($runtime);
        
        $this->logger->log($this->level, $message, $context);
    }

    protected function snapshot()
    {
        return microtime(true);
    }
}
