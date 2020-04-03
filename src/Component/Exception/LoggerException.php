<?php

namespace Ansas\Component\Exception;

use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class LoggerException
 *
 * @package Ansas\Component\Exception
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class LoggerException extends Exception
{
    /**
     * @var mixed Log level
     */
    protected $level;

    /**
     * @var array Log context
     */
    protected $context;

    /**
     * Create new exception.
     *
     * @param string    $message  The Exception message to throw
     * @param mixed     $level    Log level
     * @param array     $context  Log context [optional]
     * @param int       $code     The Exception code [optional]
     * @param Throwable $previous The previous exception used for the exception chaining [optional]
     */
    public function __construct($message, $level, array $context = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->level   = $level;
        $this->context = $context;
    }

    /**
     * Create new instance.
     *
     * @param Exception $e
     * @param mixed     $level   Log level
     * @param array     $context Log context [optional]
     *
     * @return LoggerException
     */
    public static function createFromException($e, $level, array $context = [])
    {
        return new static($e->getMessage(), $level, $context, $e->getCode(), $e);
    }

    /**
     * Add log context.
     *
     * @param array $context Log context
     *
     * @return $this
     */
    public function addContext(array $context)
    {
        if ($context) {
            $this->context = array_merge($this->context, $context);
        }

        return $this;
    }

    /**
     * Add exception details to context.
     *
     * @param bool $withTrace [optional]
     *
     * @return $this
     */
    public function addExceptionDetailsToContext($withTrace = false)
    {
        $this->addContext([
            'exceptionCode' => $this->getCode(),
            'exceptionFile' => $this->getFile(),
            'exceptionLine' => $this->getLine(),
        ]);

        if ($withTrace) {
            $this->addContext(['exceptionTrace' => $this->getTraceAsString()]);
        }

        return $this;
    }

    /**
     * Get log context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get log level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param LoggerInterface $logger
     * @param array           $context Log context [optional]
     *
     * @return $this
     */
    public function log(LoggerInterface $logger, array $context = [])
    {
        $this->addContext($context);
        $logger->log($this->getLevel(), $this->getMessage(), $this->getContext());

        return $this;
    }
}
