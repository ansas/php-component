<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Exception;

use Exception;

/**
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FoundException extends Exception
{
    /**
     * @var mixed
     */
    protected $found;

    /**
     * @param mixed $found [optional]
     */
    public function __construct($found)
    {
        $message = is_scalar($found) ? $found : serialize($found);
        parent::__construct($message);

        $this->found = $found;
    }

    /**
     * Get found.
     *
     * @return mixed
     */
    public function getFound()
    {
        return $this->found;
    }
}
