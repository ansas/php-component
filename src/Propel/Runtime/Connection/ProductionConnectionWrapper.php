<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Propel\Runtime\Connection;

use Propel\Runtime\Connection\ConnectionWrapper;

/**
 * Class ProductionConnectionWrapper
 *
 * Sets debugging to true but suppresses info() logging for profiling.
 *
 * @package Ansas\Propel\Runtime\Connection
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class ProductionConnectionWrapper extends ConnectionWrapper
{
    /**
     * @var boolean
     */
    public $useDebug = true;

    /**
     * @inheritdoc
     */
    public function log($msg)
    {
    }
}
