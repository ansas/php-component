<?php
/**
 * This file is part of the PHP components.
 *
 * For the full copyright and license information, please view the LICENSE.md file distributed with this source code.
 *
 * @license MIT License
 * @link    https://github.com/ansas/php-component
 */

namespace Ansas\Util;

use Ansas\Component\Exception\ContextException;
use PHPUnit\Framework\TestCase;

/**
 * Class FileTest
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class FileTest extends TestCase
{
    public function testToXmlInvalid()
    {
        $this->expectException(ContextException::class);
        try {
            File::toXml('invalid.file');
        } catch (ContextException $e) {
            $this->assertStringContainsStringIgnoringCase("failed to load", ($e->getContext())[0]['message']);
            throw $e;
        }
    }
}
