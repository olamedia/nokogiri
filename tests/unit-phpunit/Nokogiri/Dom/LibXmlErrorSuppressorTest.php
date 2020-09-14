<?php
declare(strict_types=1);

namespace Tests\Unit\PHPUnit\Nokogiri\Dom;

use Nokogiri\Dom\LibXmlErrorSuppressor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class LibXmlErrorSuppressorTest extends TestCase
{
    /**
     * @covers \Nokogiri\Dom\LibXmlErrorSuppressor
     */
    public function testStart(){
        $suppressor = new LibXmlErrorSuppressor();

        $suppressor->start();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('</broken>');
        $errors = \libxml_get_errors();
        \libxml_clear_errors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(0, $errors);
        $this->assertInstanceOf(\LibXMLError::class, $errors[0]);
        $this->assertSame('Unexpected end tag : broken', \trim($errors[0]->message));
    }

    /**
     * @covers \Nokogiri\Dom\LibXmlErrorSuppressor
     */
    public function testFinish(){
        $suppressor = new LibXmlErrorSuppressor();
        $suppressor->start();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('</broken>');

        $suppressor->finish();
        $errors = \libxml_get_errors();
        \libxml_clear_errors();

        $this->assertIsArray($errors);
        $this->assertArrayNotHasKey(0, $errors);
    }

    /**
     * @covers \Nokogiri\Dom\LibXmlErrorSuppressor
     */
    public function testGetErrors(){
        $suppressor = new LibXmlErrorSuppressor();
        $suppressor->start();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('</broken>');
        $suppressor->finish();

        $errors = $suppressor->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey(0, $errors);
        $this->assertInstanceOf(\LibXMLError::class, $errors[0]);
        $this->assertSame('Unexpected end tag : broken', \trim($errors[0]->message));
    }
}
