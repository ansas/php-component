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
 * Class TextTest
 *
 * @package Ansas\Util
 * @author  Ansas Meyer <mail@ansas-meyer.de>
 */
class TextTest extends TestCase
{
    public function testMaxCharWidth()
    {
        $this->assertEquals(
            0,
            Text::maxCharWidth('')
        );

        $this->assertEquals(
            1,
            Text::maxCharWidth(' ')
        );

        $this->assertEquals(
            1,
            Text::maxCharWidth('abc')
        );

        $this->assertEquals(
            2,
            Text::maxCharWidth('äöüß')
        );

        $this->assertEquals(
            3,
            Text::maxCharWidth('€')
        );

        $this->assertEquals(
            3,
            Text::maxCharWidth('Übrig: 123,56 €')
        );

        $this->assertEquals(
            4,
            Text::maxCharWidth('🌟')
        );
    }

    public function testStripEmails()
    {
        $this->assertEquals(
            '',
            Text::stripEmails('')
        );

        $this->assertEquals(
            '',
            Text::stripEmails('', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripEmails('test@test.de')
        );

        $this->assertEquals(
            '',
            Text::stripEmails('<test@test.de>')
        );

        $this->assertEquals(
            '<b>[...]</b>',
            Text::stripEmails('<b>test@test.de</b>', '[...]')
        );

        $this->assertEquals(
            'Email: [...]',
            Text::stripEmails('Email: test@test.de', '[...]')
        );

        $this->assertEquals(
            'First [...] LAST',
            Text::stripEmails('First <test@test.de> LAST', '[...]')
        );

        $this->assertEquals(
            'First test@test LAST',
            Text::stripEmails('First test@test LAST', '[...]')
        );

        $this->assertEquals(
            'First LAST',
            Text::stripEmails('First LAST', '[...]')
        );
    }

    public function testStripLinks()
    {
        $this->assertEquals(
            '',
            Text::stripLinks('')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('http://test.de')
        );

        $this->assertEquals(
            '[...]',
            Text::stripLinks('//test.de', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripLinks('www.test.de')
        );

        $this->assertEquals(
            ' ',
            Text::stripLinks(' www.test.de/sub/?lang=en.')
        );

        $this->assertEquals(
            'at: ',
            Text::stripLinks('at: test.de/sub')
        );

        $this->assertEquals(
            'Der Link: ',
            Text::stripLinks('Der Link: www.test.de/test/test.htm?test=1&test2=2')
        );

        $this->assertEquals(
            'First [...] LAST',
            Text::stripLinks('First https://test.de?test LAST', '[...]')
        );

        $this->assertEquals(
            '<b>[...]</b>',
            Text::stripLinks('<b>www.test.de</b>', '[...]')
        );

        $this->assertEquals(
            '<b>[...]</b>',
            Text::stripLinks('<b>//www.test.de</b>', '[...]')
        );

        $this->assertEquals(
            '<b>[...]</b>',
            Text::stripLinks('<b>https://www.test.de</b>', '[...]')
        );

        $this->assertEquals(
            'Buy at MY.[...].',
            Text::stripLinks('Buy at MY.Amazon.com.', '[...]', ['com', 'de'])
        );

        $this->assertEquals(
            'Buy at <b>[...]</b>.',
            Text::stripLinks('Buy at <b>Amazon.com</b>.', '[...]', ['com'])
        );
    }

    public function testStripPhones()
    {
        $this->assertEquals(
            '',
            Text::stripPhones('')
        );

        $this->assertEquals(
            '',
            Text::stripPhones('', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripPhones('+49 541 123456')
        );

        $this->assertEquals(
            '',
            Text::stripPhones('0049 (0)541 1234-56')
        );

        $this->assertEquals(
            'Tel.: ',
            Text::stripPhones("Tel.: 0541\t123456")
        );

        $this->assertEquals(
            '123 + 123 = 246',
            Text::stripPhones('123 + 123 = 246')
        );

        $this->assertEquals(
            '(+123)',
            Text::stripPhones('(+123)')
        );

        $this->assertEquals(
            'Phone: <b>[...]</b>',
            Text::stripPhones("Phone: <b>+49 (0) 541 / 123 - 456</b>", '[...]')
        );

        $this->assertEquals(
            '9781231231230',
            Text::stripPhones('9781231231230')
        );

        $this->assertEquals(
            '[...]',
            Text::stripPhones('0123456789012', '[...]')
        );
    }

    public function testStripSocials()
    {
        $this->assertEquals(
            '',
            Text::stripSocials('')
        );

        $this->assertEquals(
            '',
            Text::stripSocials('', '[...]')
        );

        $this->assertEquals(
            'test ',
            Text::stripSocials('test @test')
        );

        $this->assertEquals(
            ' ',
            Text::stripSocials(' @test')
        );

        $this->assertEquals(
            'test@test',
            Text::stripSocials('test@test')
        );

        $this->assertEquals(
            'Twitter: [...] etc.',
            Text::stripSocials('Twitter: @test etc.', '[...]')
        );

        $this->assertEquals(
            '',
            Text::stripSocials('facebook.com/test')
        );

        $this->assertEquals(
            '<u>at: </u>[...]<b>now</b>',
            Text::stripSocials('<u>at: </u>facebook.com/test<b>now</b>', '[...]')
        );
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testToXmlValid()
    {
        $xml = Text::toXml('<root><name>Test</name></root>');
        $this->assertEquals('Test', (string) $xml->name);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testToXmlInvalid()
    {
        $this->expectException(ContextException::class);
        try {
            Text::toXml('invalid xml');
        } catch (ContextException $e) {
            $this->assertContains("start tag expected", ($e->getContext())[0]['message'], '', true);
            throw $e;
        }
    }
}
