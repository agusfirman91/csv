<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LeagueTest\Csv;

use DOMDocument;
use DOMElement;
use DOMException;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\XMLConverter;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @group converter
 * @coversDefaultClass \League\Csv\XMLConverter
 */
class XMLConverterTest extends TestCase
{
    /**
     * @covers ::rootElement
     * @covers ::recordElement
     * @covers ::fieldElement
     * @covers ::convert
     * @covers ::recordToElement
     * @covers ::recordToElementWithAttribute
     * @covers ::fieldToElement
     * @covers ::fieldToElementWithAttribute
     * @covers ::filterAttributeName
     * @covers ::filterElementName
     */
    public function testToXML()
    {
        $csv = Reader::createFromPath(__DIR__.'/data/prenoms.csv', 'r')
            ->setDelimiter(';')
            ->setHeaderOffset(0)
        ;

        $stmt = (new Statement())
            ->offset(3)
            ->limit(5)
        ;

        $records = $stmt->process($csv);

        $converter = (new XMLConverter())
            ->rootElement('csv')
            ->recordElement('record', 'offset')
            ->fieldElement('field', 'name')
        ;

        $dom = $converter->convert($records);
        $record_list = $dom->getElementsByTagName('record');
        $field_list = $dom->getElementsByTagName('field');

        self::assertInstanceOf(DOMDocument::class, $dom);
        self::assertSame('csv', $dom->documentElement->tagName);
        self::assertEquals(5, $record_list->length);
        self::assertTrue($record_list->item(0)->hasAttribute('offset'));
        self::assertEquals(20, $field_list->length);
        self::assertTrue($field_list->item(0)->hasAttribute('name'));
    }

    /**
     * @covers ::rootElement
     * @covers ::filterAttributeName
     * @covers ::filterElementName
     */
    public function testXmlElementTriggersException()
    {
        self::expectException(DOMException::class);
        (new XMLConverter())
            ->recordElement('record', '')
            ->rootElement('   ');
    }

    /**
     * @covers ::convert
     */
    public function testConvertRecordsTriggersTypeError()
    {
        self::expectException(TypeError::class);
        (new XMLConverter())->convert('foo');
    }

    /**
     * @covers ::import
     */
    public function testImportRecordsTriggersTypeError()
    {
        $dom = new DOMDocument('1.0');
        self::expectException(TypeError::class);
        (new XMLConverter())->import('foo', $dom);
    }

    /**
     * @covers ::rootElement
     * @covers ::recordElement
     * @covers ::fieldElement
     * @covers ::import
     * @covers ::recordToElement
     * @covers ::recordToElementWithAttribute
     * @covers ::fieldToElement
     * @covers ::fieldToElementWithAttribute
     * @covers ::filterAttributeName
     * @covers ::filterElementName
     */
    public function testImport()
    {
        $csv = Reader::createFromPath(__DIR__.'/data/prenoms.csv', 'r')
            ->setDelimiter(';')
            ->setHeaderOffset(0)
        ;

        $stmt = (new Statement())
            ->offset(3)
            ->limit(5)
        ;

        $records = $stmt->process($csv);

        $converter = (new XMLConverter())
            ->rootElement('csv')
            ->recordElement('record', 'offset')
            ->fieldElement('field', 'name')
        ;

        $doc = new DOMDocument('1.0');
        $element = $converter->import($records, $doc);

        self::assertInstanceOf(DOMDocument::class, $doc);
        self::assertCount(0, $doc->childNodes);
        self::assertInstanceOf(DOMElement::class, $element);
        self::assertCount(5, $element->childNodes);
    }
}
