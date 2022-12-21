<?php

/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Propel\Generator\Reverse;

use Propel\Generator\Model\Table;
use Propel\Generator\Reverse\MysqlSchemaParser;

/**
 * Modified MySql PlatformInterface implementation.
 *
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CustomMysqlSchemaParser extends MysqlSchemaParser
{
    /**
     * Adds Columns to the specified table.
     *
     * @param Table $table The Table model class to add columns to.
     */
    protected function addColumns(Table $table): void
    {
        $platform = $this->getPlatform();

        $stmt = $this->dbh->query(sprintf('SHOW COLUMNS FROM %s', $platform->doQuoting($table->getName())));

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $column = $this->getColumnFromRow($row, $table);

            // Add support for generated columns
            $generated = ' GENERATED';
            if (isset($row['Extra']) && strpos($row['Extra'], $generated)) {
                // Build SQL do get generate "expression"
                $sql = sprintf("
                    SELECT `GENERATION_EXPRESSION`
                    FROM `information_schema`.`COLUMNS`
                    WHERE `TABLE_SCHEMA`= %s AND `TABLE_NAME` = %s AND `COLUMN_NAME` = %s",
                    $platform->quote($table->guessSchemaName()),
                    $platform->quote($table->getCommonName()),
                    $platform->quote($column->getName()),
                );

                // Note: Do not use quoted values here (done later)
                $type = $row['Type'];
                $expr = str_replace("\'", "'", $this->dbh->query($sql)->fetchColumn());
                $mode = str_replace($generated, '', $row['Extra']);

                // Build and replace "sqlType"
                $sqlType = sprintf("%s GENERATED ALWAYS AS (%s) %s", $type, $expr, $mode);
                $column->getDomain()->replaceSqlType($sqlType);
            }

            $table->addColumn($column);
        }
    }
}
