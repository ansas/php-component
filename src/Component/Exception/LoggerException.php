<?php

namespace Ansas\Component\Exception;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class LoggerException extends ContextException
{
    /**
     * @var mixed
     */
    protected $level;

    // TODO change signature to match
    public function __construct($message, $level, array $context = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $context, $code, $previous);
        $this->setLevel($level);
    }

    public static function createFromException(Throwable $e, array $context = [], $level = Logger::ERROR): self
    {
        if ($e instanceof ContextException) {
            $context = array_merge($e->getContext(), $context);
        }
        return new static($e->getMessage(), $level, $context, $e->getCode(), $e);
    }

    /**
     * @return static
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function addExceptionDetailsToContext(bool $withTrace = false)
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
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function log(LoggerInterface $logger, array $context = [])
    {
        $this->addContext($context);
        $logger->log($this->getLevel(), $this->getMessage(), $this->getContext());
    }

    /**
     * @return static
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }
}
