<?php

namespace Ansas\Component\Exception;

use Exception;
use Throwable;

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ContextException extends Exception
{
    protected array $context;

    public function __construct($message, array $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    public static function createFromException(Throwable $e, array $context = []): self
    {
        if ($e instanceof self) {
            return $e->addContext($context);
        }

        return new self($e->getMessage(), $context, $e->getCode(), $e->getPrevious());
    }

    /**
     * @return static
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function addContext(array $context)
    {
        if ($context) {
            $this->context = array_merge($this->context, $context);
        }

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
