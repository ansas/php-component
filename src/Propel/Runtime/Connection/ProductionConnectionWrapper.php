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
 * Sets debugging to true but suppresses info() logging for profiling.
 */
class ProductionConnectionWrapper extends ConnectionWrapper
{
    public static $useDebugMode = true;

    /**
     * @inheritdoc
     */
    public function log($msg): void
    {
    }
}
