<?php
declare(strict_types=1);

namespace Tests\Unit\PHPUnit\Nokogiri\Dom;

use Nokogiri\Dom\Document;
use Nokogiri\Dom\DocumentFactory;
use Nokogiri\Dom\Interfaces\ErrorSuppressorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @internal
 * @coversNothing
 */
final class DocumentTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @covers \Nokogiri\Dom\Document::__construct
     */
    public function testEmptyDocument()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);

        $document = new Document($suppressorMock);

        $this->assertInstanceOf(\DOMDocument::class, $document->toDOMDocument());
    }

    /**
     * @covers \Nokogiri\Dom\Document::loadHtml
     */
    public function testLoadHtml()
    {
        $suppressorMock = $this->prophesize(ErrorSuppressorInterface::class);
        $domDocumentMock = $this->prophesize(\DOMDocument::class);
        $document = new Document($suppressorMock->reveal(), $domDocumentMock->reveal());
        $domDocumentMock->loadHTML('<p>1</p>', 65540)->shouldBeCalled();

        $document->loadHtml('<p>1</p>');

        $suppressorMock->start()->shouldHaveBeenCalled();
        $suppressorMock->finish()->shouldHaveBeenCalled();
    }

    /**
     * @covers \Nokogiri\Dom\Document::__construct
     * @covers \Nokogiri\Dom\Document::toDOMDocument
     */
    public function testToDOMDocument()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);
        $factory = new DocumentFactory($suppressorMock);
        $domDocument = new \DOMDocument('1.0', 'UTF-8');

        $document = new Document($suppressorMock, $domDocument);

        $this->assertSame($domDocument, $document->toDOMDocument());
    }

    /**
     * @covers \Nokogiri\Dom\Document::toXml
     */
    public function testToXml()
    {
        $suppressorMock = $this->createMock(ErrorSuppressorInterface::class);
        $factory = new DocumentFactory($suppressorMock);
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $domDocument->loadHTML('<p>1</p>');
        $document = new Document($suppressorMock, $domDocument);

        $xmlString = $document->toXml();

        $this->assertStringContainsString('<body><p>1</p></body>', $xmlString);
    }
}
