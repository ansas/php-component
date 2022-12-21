<?php

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Propel\Generator\Platform;

use Propel\Generator\Model\Table;
use Propel\Generator\Platform\MysqlPlatform;

/**
 * Modified MySql PlatformInterface implementation.
 *
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CustomMysqlPlatform extends MysqlPlatform
{
    protected function getTableOptions(Table $table): array
    {
        $tableOptions = parent::getTableOptions($table);

        $dbVI         = $table->getDatabase()->getVendorInfoForType('mysql');
        $tableVI      = $table->getVendorInfoForType('mysql');
        $vi           = $dbVI->getMergedVendorInfo($tableVI);

        // List of supported table options
        // see http://dev.mysql.com/doc/refman/5.7/en/create-table.html
        $supportedOptions = [
            'Encryption'      => 'ENCRYPTION',
        ];

        $noQuotedValue = array_flip([
        ]);

        foreach ($supportedOptions as $name => $sqlName) {
            $parameterValue = null;

            if ($vi->hasParameter($name)) {
                $parameterValue = $vi->getParameter($name);
            } elseif ($vi->hasParameter($sqlName)) {
                $parameterValue = $vi->getParameter($sqlName);
            }

            // if we have a param value, then parse it out
            if (!is_null($parameterValue)) {
                // if the value is numeric or is parameter is in $noQuotedValue, then there is no need for quotes
                if (!is_numeric($parameterValue) && !isset($noQuotedValue[$name])) {
                    $parameterValue = $this->quote($parameterValue);
                }

                $tableOptions [] = sprintf('%s=%s', $sqlName, $parameterValue);
            }
        }

        return $tableOptions;
    }
}
