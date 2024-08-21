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

use Ansas\Component\Convert\ConvertNumber;
use Ansas\Component\Exception\ContextException;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testFixUtf8()
    {
        $this->assertEquals('', Text::fixUtf8(''));
        $this->assertEquals('AbC', Text::fixUtf8('AbC'));
        $this->assertEquals('öl', Text::fixUtf8('Ã¶l'));
        $this->assertEquals('öl?', Text::fixUtf8('Ã¶l?'));
        $this->assertEquals('Straße?', Text::fixUtf8('Straße?'));
        $this->assertEquals('Nö?', Text::fixUtf8('Nö?'));
    }

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

    public function testRemovePrefix()
    {
        $this->assertEquals(
            'abc',
            Text::removePrefix('', 'abc')
        );

        $this->assertEquals(
            'äbc def',
            Text::removePrefix('ÄBC', 'äbc def')
        );

        $this->assertEquals(
            ' def',
            Text::removePrefix('ÄBC', 'äbc def', true)
        );
    }

    public function testReplace()
    {
        $this->assertEquals('Birnen', Text::replace('Äpfel', 'Birnen', 'äpfel'));
        $this->assertEquals('Apfel', Text::replace('Äpfel', 'Birnen', 'Apfel'));
        $this->assertEquals('Birnen', Text::replace(['gespülte Äpfel'], 'Birnen', 'Gespülte Äpfel'));
        $this->assertEquals('gespülte Birnen', Text::replace(['Äpfel'], 'Birnen', 'gespülte Äpfel'));
        $this->assertEquals(['Birnen', 'birnen'], Text::replace(['Äpfel'], 'Apfel', ['Birnen', 'birnen']));
    }

    public function testReplaceFirst()
    {
        $this->assertEquals(
            '',
            Text::replaceFirst('', 'def', '')
        );

        $this->assertEquals(
            'abc',
            Text::replaceFirst('', 'def', 'abc')
        );

        $this->assertEquals(
            'def',
            Text::replaceFirst('äbc', 'def', 'äbc')
        );

        $this->assertEquals(
            'abc',
            Text::replaceFirst('äbc', 'def', 'abc')
        );

        $this->assertEquals(
            'def abc',
            Text::replaceFirst('abc', 'def', 'abc abc')
        );
    }

    public function testSpace()
    {
        $this->assertEquals(
            '',
            Text::space('', 2)
        );

        $this->assertEquals(
            '12 3',
            Text::space('123', 2)
        );

        $this->assertEquals(
            '1 23',
            Text::space('123', 2, true)
        );

        $this->assertEquals(
            '1-23-45',
            Text::space('12345', 2, true, '-')
        );
    }

    public function testStrip4ByteChars()
    {
        $this->assertEquals(
            '',
            Text::strip4ByteChars('', '[...]')
        );

        $this->assertEquals(
            'abcäöüß€@.,!?',
            Text::strip4ByteChars('abcäöüß€@.,!?')
        );

        $this->assertEquals(
            'The duck #',
            Text::strip4ByteChars('The duck 🦆', '#')
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

        // Check that numbers not starting with leading zero (like EAN, UPC) are not striped
        $this->assertEquals(
            '9780000000001',
            Text::stripPhones('9780000000001')
        );
        $this->assertEquals(
            '(xxx 1010101010101).',
            Text::stripPhones('(xxx 1010101010101).')
        );

        // Check that numbers starting with leading zero are striped
        $this->assertEquals(
            '[...]',
            Text::stripPhones('0123456789012', '[...]')
        );
        $this->assertEquals(
            '(xxx ).',
            Text::stripPhones('(xxx 0101010101010).')
        );
    }

    public function testStripPrices()
    {
        $this->assertEquals(
            '',
            Text::stripPrices('')
        );

        $this->assertEquals(
            'Hier steht [...] als Preis',
            Text::stripPrices('Hier steht 1.234,56 EUR als Preis', '[...]')
        );

        $this->assertEquals(
            'nur ?.',
            Text::stripPrices('nur 1,23 GBP.', '?', ['gbp'])
        );
        $this->assertEquals(
            'nur 1,23 GBP.',
            Text::stripPrices('nur 1,23 GBP.')
        );

        $list = [
            'USD 1.23',
            'USD1,234.56',
            '$ 1.23',
            '$12.3',
            '1.230,00 EUR',
            '12,3EUR',
            '1,23 €',
            '12,3€',
        ];
        foreach ($list as $string) {
            $this->assertEquals(
                '',
                Text::stripPrices($string)
            );
            $this->assertEquals(
                'nur .',
                Text::stripPrices('nur ' . $string . '.')
            );
            $this->assertEquals(
                'nur [...],',
                Text::stripPrices('nur ' . $string . ',', '[...]')
            );
            $this->assertEquals(
                ',.',
                Text::stripPrices(',' . $string . '.')
            );
            $this->assertEquals(
                '.[...],',
                Text::stripPrices('.' . $string . ',', '[...]')
            );
        }
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

    public function testToCase()
    {
        $this->assertEquals('', Text::toCase(null, Text::LOWER_FIRST));
        $this->assertEquals('äÖÜ', Text::toCase('ÄÖÜ', Text::LOWER_FIRST));
        $this->assertEquals('Ää Öö', Text::toCase('ää öö', Text::UPPER_WORDS));
    }

    public function testToFloat()
    {
        $this->assertEquals(0, Text::toFloat(null));
        $this->assertEquals(0, Text::toFloat(''));
        $this->assertEquals(0, Text::toFloat('0.000.000,00'));
        $this->assertEquals(-12345.67, Text::toFloat('12.345,67-'));
        $this->assertEquals(12345.67, Text::toFloat('12,345.67'));
        $this->assertEquals(12345.67, Text::toFloat('the price is 12,345.67 EUR'));
        $this->assertEquals(-12345.67, Text::toFloat('the price is 12,345.67- EUR'));
        $this->assertEquals(1325125.54, Text::toFloat('1.325.125,54'));
        $this->assertEquals(1325125.54, Text::toFloat('1,325,125.54'));
        $this->assertEquals(-1325125.54, Text::toFloat('-1.325.125,54'));
        $this->assertEquals(-1325125.54, Text::toFloat('-1,325,125.54'));
        $this->assertEquals(59.95, Text::toFloat('59,95'));
        $this->assertEquals(1, Text::toFloat(true));
        $this->assertEquals(0, Text::toFloat(false));
        $this->assertEquals(0, Text::toFloat(0));
        $this->assertEquals(1, Text::toFloat(1));
    }

    public function testToNormalized()
    {
        $this->assertEquals('', Text::toNormalized(null));
        $this->assertEquals('abc', Text::toNormalized('ABC'));
        $this->assertEquals('woerter', Text::toNormalized('Wörter'));
        $this->assertEquals('masse', Text::toNormalized(' M a ß e '));
    }

    public function testToNormalizedInvalid()
    {
        $this->expectException(\TypeError::class);
        Text::toNormalized(new \DateTime());
    }

    public function testToXml()
    {
        $xml = Text::toXml('<root><name>Test</name></root>');
        $this->assertEquals('Test', (string) $xml->name);
    }

    public function testToXmlInvalid()
    {
        $this->expectException(ContextException::class);
        try {
            Text::toXml('invalid xml');
        } catch (ContextException $e) {
            $this->assertStringContainsStringIgnoringCase("start tag expected", ($e->getContext())[0]['message']);
            throw $e;
        }
    }

    public function testTruncate()
    {
        $this->assertEquals('MyTooLo...', Text::truncate('MyTooLongTestText', 10));
        $this->assertEquals('MyTooLongT', Text::truncate('MyTooLongTestText', 10, false, ''));
        $this->assertEquals('MyTooLongT', Text::truncate('MyTooLongTestText', 10, true, ''));
        $this->assertEquals('MyTooL ...', Text::truncate('MyTooLongTest Text', 10, true, ' ...'));
        $this->assertEquals('My Too ...', Text::truncate('My Too Long Test Text', 10, true, ' ...'));

        $this->assertEquals('MyNotTooLongTestText', Text::truncate('MyNotTooLongTestText', 20));
        $this->assertEquals('My Too Long Test ...', Text::truncate('My Too Long Test Text', 20, true, ' ...'));
        $this->assertEquals('A Too Long Test ...', Text::truncate('A Too Long Test Textpart', 20, true, ' ...'));
    }
}
