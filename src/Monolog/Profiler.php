<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Monolog;

use Ansas\Component\Collection\Collection;
use Closure;
use Exception;
use Monolog\Logger;

/**
 * Class Profiler
 *
 * A slim stop watch for different profiles that are logged to any Monolog logger.
 *
 * @package Ansas\Monolog
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 *
 * @property Collection $context
 * @property Logger     $logger
 */
class Profiler
{
    /**
     * @var Logger Logger.
     */
    protected $logger;

    /**
     * @var int Logger level.
     */
    protected $level;

    /**
     * @var callable Formatter function.
     */
    protected $formatter;

    /**
     * @var string|null Profile name (only children must have a name).
     */
    protected $name = null;

    /**
     * @var Profiler[] Child profiles (all child profiles go here).
     */
    protected $children = [];

    /**
     * @var float|null Start time.
     */
    protected $start = null;

    /**
     * @var float|null Stop time.
     */
    protected $stop = null;

    /**
     * @var array Lap times.
     */
    protected $laps = [];

    /**
     * @var Collection|null Context.
     */
    protected $context = null;

    /**
     * Profiler constructor.
     *
     * @param Logger        $logger
     * @param int           $level     [optional]
     * @param callable|null $formatter [optional]
     */
    public function __construct(Logger $logger, $level = Logger::DEBUG, callable $formatter = null)
    {
        $this->setLogger($logger);
        $this->setLevel($level);
        $this->setFormatter($formatter);
    }

    /**
     * Profiler destructor.
     */
    public function __destruct()
    {
        // Skip stop routine if not the parent profile
        if (!$this->name) {
            $this->stopAll();
        }
    }

    /**
     * Get specified profile.
     *
     * @param  string $profile
     *
     * @return object The profile
     */
    public function __get($profile)
    {
        return $this->set($profile);
    }

    /**
     * Convert object to small summary string of profile incl. child profiles.
     *
     * @return string
     */
    public function __toString()
    {
        // Set report for this profile
        $report = sprintf(
            "started: %s\t" . "stopped: %s\t" . "laps: %d\t" . "runtime: %.03f\t" . "name: %s\n",
            ($this->isStarted() ? 'yes' : 'no'),
            ($this->isStopped() ? 'yes' : 'no'),
            $this->countLaps(),
            $this->timeTotal(),
            $this->getName()
        );

        // Add report of all child profiles
        foreach ($this->getProfiles() as $child) {
            $report .= (string) $child;
        }

        return $report;
    }

    /**
     * Add child profile.
     *
     * @param string $profile
     * @param bool   $inheritContext [optional] Also set context for child profile?
     *
     * @return Profiler
     * @throws Exception
     */
    public function add(string $profile, $inheritContext = false)
    {
        if ($this->has($profile)) {
            throw new Exception("Profile {$profile} already exist.");
        }

        if (!strlen($profile)) {
            throw new Exception("Profile must be set.");
        }

        $child = clone $this;
        $child->clear(!$inheritContext);
        $child->name     = ($this->name ? $this->name . ' > ' : '') . $profile;
        $child->children = [];

        $this->children[$profile] = $child;

        return $child;
    }

    /**
     * Clear profile.
     *
     * Resets profile and removes all collected data so it can:
     * - be started again
     * - will not be logged at profiler destruction
     *
     * @param bool $clearContext [optional] Clear context as well?
     *
     * @return $this
     */
    public function clear($clearContext = false)
    {
        // Clear all stopwatch related values
        $this->start = null;
        $this->stop  = null;
        $this->laps  = [];

        if ($clearContext) {
            $this->context = null;
        }

        return $this;
    }

    /**
     * Clear this and all child profiles.
     *
     * @param bool $clearContext [optional] Clear context as well?
     *
     * @return $this
     */
    public function clearAll($clearContext = false)
    {
        foreach ($this->getProfiles() as $child) {
            $child->clearAll($clearContext);
        }

        $this->clear($clearContext);

        return $this;
    }

    /**
     * Set / remove context for this and all later created child profiles.
     *
     * Usage:
     * - <code>$profiler->context($context)</code><br>
     *   Set context.
     * - <code>$profiler->context()</code> or <code>$profiler->context(null)</code><br>
     *   Remove context.
     *
     * @param Collection|null $context [optional] Set context to null to remove context.
     *
     * @return $this
     */
    public function context(Collection $context = null)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get amount of laps.
     *
     * @return int
     */
    public function countLaps()
    {
        return count($this->getLaps());
    }

    /**
     * Get default formatter.
     *
     * @return Closure
     */
    public function defaultFormatter()
    {
        return function (float $var) {
            return number_format($var, 3, ',', '.') . " sec.";
        };
    }

    /**
     * Get child profile.
     *
     * @param string $profile
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $profile)
    {
        if (!$this->has($profile)) {
            throw new Exception("Profile {$profile} does not exist.");
        }

        return $this->children[$profile];
    }

    /**
     * Get formatter.
     *
     * @return mixed
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get laps.
     *
     * @return array
     */
    public function getLaps()
    {
        return $this->laps;
    }

    /**
     * Get profile name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?? 'MAIN';
    }

    /**
     * Get all child profiles of this profile.
     *
     * @param bool $reverse Reverse results?
     *
     * @return Profiler[]
     */
    public function getProfiles($reverse = false)
    {
        if ($reverse) {
            return array_reverse($this->children);
        }

        return $this->children;
    }

    /**
     * Check if profile exists.
     *
     * @param string $profile
     *
     * @return bool
     */
    public function has(string $profile)
    {
        return isset($this->children[$profile]);
    }

    /**
     * Retrieve information if profile is started and not stopped yet.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isStarted() && !$this->isStopped();
    }

    /**
     * Retrieve information if profile was started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return null !== $this->timeStart();
    }

    /**
     * Retrieve information if profile was stopped.
     *
     * @return bool
     */
    public function isStopped()
    {
        return null !== $this->timeStop();
    }

    /**
     * Add lap to this (running) profile.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     * @param mixed  $level   [optional]
     *
     * @return $this
     * @throws Exception
     */
    public function lap($message = 'Lap', $context = [], $level = null)
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $level   = $context ?: null;
            $context = $message;
            $message = 'Lap';
        }

        if (!$this->isRunning()) {
            throw new Exception("Profile {$this->getName()} not running.");
        }

        // Add current lap time to context
        $context['runtime'] = $this->timeLap();

        // Start new lap
        $this->laps[] = $this->timeCurrent();

        // Log
        $this->note($message, $context, $level);

        return $this;
    }

    /**
     * Log note for this profile.
     *
     * This is the main method for actually logging everything. start()|stop()|lap() use this method for logging. This
     * method can be used to note information without using a time feature.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     * @param mixed  $level   [optional]
     *
     * @return $this
     */
    public function note($message = 'Note', $context = [], $level = null)
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $level   = $context ?: null;
            $context = $message;
            $message = 'Note';
        }

        // Skip log entry if message is null
        if (is_null($message)) {
            return $this;
        }

        // Add and format context
        if ($this->context) {
            $context = array_merge($this->context->all(), $context);
        }
        $context['profile'] = $this->name;
        if (isset($context['runtime'])) {
            $formatter          = $this->getFormatter();
            $context['runtime'] = $formatter($context['runtime']);
        }

        // Log
        $this->logger->log($level ?: $this->level, $message, $context);

        return $this;
    }

    /**
     * Removes specified child profile from this profile.
     *
     * @param string $profile
     *
     * @return $this
     */
    public function remove(string $profile)
    {
        $child = $this->get($profile);
        $child->clearAll();
        $child->removeAll();

        unset($this->children[$profile]);

        return $this;
    }

    /**
     * Removes all child profiles from this profile.
     *
     * @return $this
     */
    public function removeAll()
    {
        foreach ($this->getProfiles() as $profile => $child) {
            $this->remove($profile);
        }

        return $this;
    }

    /**
     * Clear and start profile again.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     */
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

    /**
     * Set and use profile.
     *
     * Quick form for add() and get(): Adds child profile if needed and returns the child profile.
     *
     * @param string $profile
     * @param bool   $inheritContext [optional] Also set context for child profile?
     *
     * @return Profiler
     */
    public function set(string $profile, $inheritContext = false)
    {
        return $this->has($profile) ? $this->get($profile) : $this->add($profile, $inheritContext);
    }

    /**
     * Set formatter.
     *
     * @param callable|null $formatter [optional]
     *
     * @return $this
     */
    public function setFormatter(callable $formatter = null)
    {
        $this->formatter = $formatter ?? $this->defaultFormatter();

        return $this;
    }

    /**
     * Set (new) log level.
     *
     * @param $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        // Check if specified level is a valid Monolog level
        Logger::getLevelName($level);

        $this->level = $level;

        return $this;
    }

    /**
     * Set (new) logger.
     *
     * @param Logger $logger
     *
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Start this profile.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     *
     * @return $this
     * @throws Exception
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

    /**
     * Start this and all child profiles.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     *
     * @return $this
     */
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

    /**
     * Stop this profile.
     *
     * Usage:
     * - <code>$profiler->stop("Msg")</code><br>
     *   Simple message with only default context => if set via <code>context()</code>
     * - <code>$profiler->stop("Msg", ['key' => 'value'])</code><br>
     *   Message and (additional) context
     * - <code>$profiler->stop("Msg", ['key' => 'value'], false)</code><br>
     *   For "no lap" before stop
     * - <code>$profiler->stop(['key' => 'value'])</code><br>
     *   Default message and (additional) context
     * - <code>$profiler->stop(['key' => 'value'], false)</code><br>
     *   Context, but "no lap" before stop
     * - <code>$profiler->stop("Msg", false)</code><br>
     *   No (additional) context and "no lap" before stop
     * - <code>$profiler->stop(false)</code><br>
     *   Default message, no (additional) context and "no lap" before stop
     * - <code>$profiler->stop(null)</code><br>
     *   Stop without logging
     * - <code>$profiler->stop(null, false)</code><br>
     *   Stop without logging and "no lap" before stop
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     * @param bool   $lap     [optional]
     *
     * @return $this
     * @throws Exception
     */
    public function stop($message = 'Stop', $context = [], $lap = true)
    {
        // Check if method was called only with $context
        if (is_array($message)) {
            $lap     = $context;
            $context = $message;
            $message = 'Stop';
        } elseif (is_bool($message)) {
            $lap     = $message;
            $context = [];
            $message = 'Stop';
        } elseif (is_bool($context)) {
            $lap     = $context;
            $context = [];
        }

        if (!$this->isRunning()) {
            throw new Exception("Profile {$this->getName()} not running.");
        }

        // Skip saving and logging lap for single lap profiles
        if ($lap && count($this->laps) > 1) {
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

    /**
     * Stop this profile and all children of this profile.
     *
     * @param string $message [optional]
     * @param array  $context [optional]
     * @param bool   $lap     [optional]
     *
     * @return $this
     */
    public function stopAll($message = 'Stop', $context = [], $lap = true)
    {
        // Stop all running child profiles first
        foreach ($this->getProfiles(true) as $child) {
            $child->stopAll($message, $context, $lap);
        }

        // Stop this profile if still running
        if ($this->isRunning()) {
            $this->stop($message, $context, $lap);
        }

        return $this;
    }

    /**
     * Get current timestamp (incl. microtime).
     *
     * @return float
     */
    public function timeCurrent()
    {
        return microtime(true);
    }

    /**
     * Get lap time (if started).
     *
     * Lap number must be existing lap:
     * - positive number starting with 1 gets lap time from beginning (1 = first lap)
     * - negative number starting with -1 gets lap time from end (-1 = last / current lap)
     *
     * @param int $lap [optional] Lap number from beginning (+) or end (-), default: -1 (last lap)
     *
     * @return float|null
     * @throws Exception
     */
    public function timeLap(int $lap = -1)
    {
        if (!$this->isStarted()) {
            return null;
        }

        if ($lap == 0 || abs($lap) > $this->countLaps()) {
            throw new Exception("Lap must be an existing lap number");
        }

        // Get start time of lap
        $lapStart     = $lap > 0 ? $lap - 1 : $lap;
        $timeLapStart = $this->timeLapStart($lapStart);

        // Get stop time of lap
        $lapStop = $lapStart + 1;
        if ($lapStop == 0 || $lapStop == $this->countLaps()) {
            $timeLapStop = $this->timeStop() ?? $this->timeCurrent();
        } else {
            $timeLapStop = array_slice($this->getLaps(), $lapStop, 1)[0];
        }

        return $timeLapStop - $timeLapStart;
    }

    /**
     * Get lap start time (if started).
     *
     * Lap number must be existing lap:
     * - positive number starting with 1 gets lap time from beginning (1 = first lap)
     * - negative number starting with -1 gets lap time from end (-1 = last / current lap)
     *
     * @param int $lap [optional] Lap number from beginning (+) or end (-), default: -1 (last lap)
     *
     * @return float|null
     * @throws Exception
     */
    public function timeLapStart(int $lap = -1)
    {
        if (!$this->isStarted()) {
            return null;
        }

        if ($lap == 0 || abs($lap) > $this->countLaps()) {
            throw new Exception("Lap must be an existing lap number");
        }

        // Get start time of lap
        $lapStart     = $lap > 0 ? $lap - 1 : $lap;
        $timeLapStart = array_slice($this->getLaps(), $lapStart, 1)[0];

        return $timeLapStart;
    }

    /**
     * Get start time (if started).
     *
     * @return float|null
     */
    public function timeStart()
    {
        return $this->start;
    }

    /**
     * Get stop time (if stopped).
     *
     * @return float|null
     */
    public function timeStop()
    {
        return $this->stop;
    }

    /**
     * Get total runtime of profile (if started).
     *
     * @return float|null
     */
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
}
