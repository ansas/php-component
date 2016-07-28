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
    protected $logger;
    protected $level;
    protected $formatter;

    protected $name     = null; // only children must have a name!
    protected $children = []; // all child profiles go here

    protected $start = null;
    protected $stop  = null;
    protected $laps  = [];

    /*
     ******************************
     * object handling methods
     ******************************
     */

    public function __construct(Logger $logger, $level = Logger::DEBUG, callable $formatter = null)
    {
        $this->setLogger($logger);
        $this->setLevel($level);
        $this->setFormatter($formatter);
    }

    public function __destruct()
    {
        // Skip stop routine if not the parent profile
        if (!$this->name) {
            $this->stopAll();
        }
    }

    /**
     * Get specified profile
     *
     * @param  string $profile
     * @return object The profile
     */
    public function __get($profile)
    {
        return $this->exists($profile) ? $this->get($profile) : $this->add($profile);
    }

    public function __toString()
    {
        // Set report for this profile
        $report = sprintf(
            "started: %s\tstopped: %s\tlaps: %d\truntime: %.03f\tname: %s\n",
            ($this->isStarted() ? 'yes' : 'no'),
            ($this->isStopped() ? 'yes' : 'no'),
            count($this->getLaps()),
            $this->timeTotal(),
            $this->getName()
        );

        // Add report of all child profiles
        foreach ($this->getProfiles() as $child) {
            $report .= (string) $child;
        }

        return $report;
    }

    /*
     ******************************
     * profile methods
     ******************************
     */

    public function add($profile)
    {
        if ($this->exists($profile)) {
            throw new Exception("Profile {$profile} already exist.");
        }

        if (!strlen($profile)) {
            throw new Exception("Profile must be set.");
        }

        $child = clone $this;
        $child->clear();
        $child->name     = ($this->name ? $this->name . ' > ' : '') . $profile;
        $child->children = [];

        $this->children[$profile] = $child;

        return $child;
    }

    public function exists($profile)
    {
        return isset($this->children[$profile]);
    }

    public function get($profile)
    {
        if (!$this->exists($profile)) {
            throw new Exception("Profile {$profile} does not exist.");
        }
        return $this->children[$profile];
    }

    public function remove($profile)
    {
        $child = $this->get($profile);
        $child->clearAll();
        $child->removeAll();

        unset($this->children[$profile]);

        return $this;
    }

    public function removeAll()
    {
        foreach ($this->getProfiles() as $profile => $child) {
            $this->remove($profile);
        }

        return $this;
    }

    /*
     ******************************
     * stopwatch methods
     ******************************
     */

    public function start($message = 'Start', $context = [])
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $context = $message;
            $message = 'Start';
        }

        // A profile can only be started if never started or cleared before
        if ($this->isStarted()) {
            throw new Exception("Profile {$this->getName()} already started.");
        }

        // The main profile
        if (null === $this->name) {
            if ($this->children) {
                throw new Exception("Profile {$this->getName()} must be started before all other profiles.");
            }
            $timeStart = $_SERVER['REQUEST_TIME_FLOAT'] ?? $this->timeCurrent();
        } else {
            $timeStart = $this->timeCurrent();
        }

        // Start counter and first lap
        $this->start  = $timeStart;
        $this->laps[] = $timeStart;

        // Log
        $this->note($message, $context);

        return $this;
    }

    public function startAll($message = 'Start', $context = [])
    {
        // Start this profile if not started
        if (!$this->isStarted()) {
            $this->start($message, $context);
        }

        // Start all child profiles afterwards
        foreach ($this->getProfiles() as $child) {
            $child->startAll($message, $context);
        }

        return $this;
    }

    public function lap($message = 'Lap', $context = [])
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $context = $message;
            $message = 'Lap';
        }

        if (!$this->isRunning()) {
            throw new Exception("Profile {$this->getName()} not running.");
        }

        // Add current laptime to context
        $context['runtime'] = $this->timeLap();

        // Start new lap
        $this->laps[] = $this->timeCurrent();

        // Log
        $this->note($message, $context);

        return $this;
    }

    public function stop($message = 'Stop', $context = [])
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $context = $message;
            $message = 'Stop';
        }

        if (!$this->isRunning()) {
            throw new Exception("Profile {$this->getName()} not running.");
        }

        // Skip saving and logging lap for single lap profiles
        if (count($this->laps) > 1) {
            $this->lap();
        }

        // Stop counter
        $this->stop = $this->timeCurrent();

        // Add total runtime to context
        $context['runtime'] = $this->timeTotal();

        // Log
        $this->note($message, $context);

        return $this;
    }

    public function stopAll($message = 'Stop', $context = [])
    {
        // Stop all running child profiles first
        foreach (array_reverse($this->getProfiles()) as $child) {
            $child->stopAll($message, $context);
        }

        // Stop this profile if still running
        if ($this->isRunning()) {
            $this->stop($message, $context);
        }

        return $this;
    }

    public function note($message = 'Note', $context = [])
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $context = $message;
            $message = 'Note';
        }

        // Skip log entry if message is null
        if (is_null($message)) {
            return $this;
        }

        // Add and format context
        $context['profile'] = $this->name;
        $context['runtime'] = isset($context['runtime']) ? $this->getFormatter()($context['runtime']) : null;

        // Log
        $this->logger->log($this->level, $message, $context);

        return $this;
    }

    public function clear()
    {
        // Clear all stopwatch related values
        $this->start = null;
        $this->stop  = null;
        $this->laps  = [];

        return $this;
    }

    public function clearAll()
    {
        foreach ($this->getProfiles() as $child) {
            $child->clearAll();
        }

        $this->clear();

        return $this;
    }

    public function restart($message = 'Restart', $context = [])
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $context = $message;
            $message = 'Restart';
        }

        // Clear data and start (again)
        $this->clear();
        $this->start($message, $context);
    }

    /*
     ******************************
     * setter methods
     ******************************
     */

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function setLevel($level)
    {
        // Check if specified level is a valid Monolog level
        Logger::getLevelName($level);

        $this->level = $level;
        return $this;
    }

    public function setFormatter(callable $formatter = null)
    {
        $this->formatter = $formatter ?? $this->defaultFormatter();
        return $this;
    }

    /*
     ******************************
     * time methods
     ******************************
     */

    public function timeCurrent()
    {
        return microtime(true);
    }

    public function timeStart()
    {
        return $this->start;
    }

    public function timeStop()
    {
        return $this->stop;
    }

    public function timeTotal()
    {
        if (!$this->isStarted()) {
            return null;
        }
        if (!$this->isStopped()) {
            return $this->timeCurrent() - $this->timeStart();
        }
        return $this->timeStop() - $this->timeStart();
    }

    public function timeLap($lap = -1)
    {
        if (!$this->isStarted()) {
            return null;
        }

        // FIXME: Make this method return value for every lap instead of FIXED last lap
        $lap = -1;

        $timeLapStart = array_slice($this->getLaps(), $lap, 1)[0];
        $timeLapStop  = $this->timeStop() ?? $this->timeCurrent();
        return $timeLapStop - $timeLapStart;
    }

    /*
     ******************************
     * default methods
     ******************************
     */

    public function defaultFormatter()
    {
        return function (float $var) {
            return number_format($var, 3, ',', '.') . " sec.";
        };
    }

    /*
     ******************************
     * helper methods
     ******************************
     */

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getName()
    {
        return $this->name ?? 'MAIN';
    }

    public function getLaps()
    {
        return $this->laps;
    }

    public function getProfiles()
    {
        return $this->children;
    }

    public function isRunning()
    {
        return $this->isStarted() && !$this->isStopped();
    }

    public function isStarted()
    {
        return null !== $this->timeStart();
    }

    public function isStopped()
    {
        return null !== $this->timeStop();
    }
}
