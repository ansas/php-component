<?php

namespace Ansas\Component\Exception;

use Exception;
use Throwable;

/**
 * Class ContextException
 *
 * @package Ansas\Component\Exception
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ContextException extends Exception
{
    /**
     * @var array Log context
     */
    protected $context;

    /**
     * Create new exception.
     *
     * @param string    $message  The Exception message to throw
     * @param array     $context  Log context [optional]
     * @param int       $code     The Exception code [optional]
     * @param Throwable $previous The previous exception used for the exception chaining [optional]
     */
    public function __construct($message, array $context = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    /**
     * Create new instance.
     *
     * @param Exception $e
     * @param array     $context Log context [optional]
     *
     * @return ContextException
     */
    public static function createFromException($e, array $context = [])
    {
        if ($e instanceof self) {
            return $e->addContext($context);
        }

        return new static($e->getMessage(), $context, $e->getCode(), $e->getPrevious());
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
     * Get log context.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
