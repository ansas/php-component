<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Component\Csv;

use Ansas\Component\File\CsvReader;
use PHPUnit\Framework\TestCase;

/**
 * Class CsvReaderTest
 *
 * @package Ansas\Component\Csv
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class CsvReaderTest extends TestCase
{
    public function testCreateFromString()
    {
        $csv = CsvReader::createFromString("");
        $this->assertInstanceOf(CsvReader::class, $csv);
    }
}
