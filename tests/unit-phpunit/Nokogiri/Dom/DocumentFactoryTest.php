<?php
declare(strict_types=1);

namespace Tests\Unit\PHPUnit\Nokogiri\Dom;

use Nokogiri\Dom\DocumentFactory;
use Nokogiri\Dom\Interfaces\ErrorSuppressorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DocumentFactoryTest extends TestCase
{
    /**
     * @covers \Nokogiri\Dom\DocumentFactory::__construct
     * @covers \Nokogiri\Dom\DocumentFactory::createFromDOMDocument
     */
    public function testCreateFromDOMDocument()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);
        $factory = new DocumentFactory($suppressorMock);
        $domDocument = new \DOMDocument('1.0', 'UTF-8');

        $document = $factory->createFromDOMDocument($domDocument);

        $this->assertSame($domDocument, $document->toDOMDocument());
    }

    /**
     * @covers \Nokogiri\Dom\DocumentFactory::createFromHtmlString
     */
    public function testCreateFromHtmlStringCP1251()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);
        $factory = new DocumentFactory($suppressorMock);
        $html = \mb_convert_encoding('<head><meta charset="cp1251" /></head><p>тест</p>', 'CP1251', 'UTF-8');

        $document = $factory->createFromHtmlString($html);

        $this->assertStringContainsString(\mb_convert_encoding('<p>тест</p>', 'CP1251', 'UTF-8'), $document->toXml());
        $this->assertSame('cp1251', $document->toDOMDocument()->encoding);
    }


    /**
     * @covers \Nokogiri\Dom\DocumentFactory::createFromHtmlString
     */
    public function testCreateFromHtmlStringUtf8()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);
        $factory = new DocumentFactory($suppressorMock);

        $document = $factory->createFromHtmlString('<p>тест</p>', true);

        $this->assertStringContainsString('<p>тест</p>', $document->toXml());
    }
}
