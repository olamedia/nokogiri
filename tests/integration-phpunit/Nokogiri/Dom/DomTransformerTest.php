<?php

namespace Tests\Integration\PHPUnit\Nokogiri\Dom;

use Nokogiri\Dom\DomTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class DomTransformerTest extends TestCase
{
    /**
     * @covers \Nokogiri\Dom\DomTransformer::toArray
     *
     * @dataProvider toArrayDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testToArray($input, $expected)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);

        $result = $transformer->toArray($document->documentElement, false);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toArray
     *
     * @dataProvider toArrayNoGroupingDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testToArrayNoGrouping($input, $expected)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);

        $result = $transformer->toArray($document->documentElement, false, false);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toDOMDocument
     */
    public function testToDOMDocumentThrowsInvalidArgumentException()
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid fragment given. Should be instance of DOMDocument | DOMNodeList | DOMElement.');

        $result = $transformer->toDOMDocument(42);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toDOMDocument
     */
    public function testToDOMDocumentWithDocument()
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');

        $result = $transformer->toDOMDocument($document);

        $this->assertSame(['document' => $document, 'root' => $document], $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toDOMDocument
     */
    public function testToDOMDocumentWithElement()
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('test');
        $fragment = $document->documentElement->firstChild; // html>body

        $result = $transformer->toDOMDocument($fragment);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('document', $result);
        $this->assertInstanceOf(\DOMDocument::class, $result['document']);
        $this->assertArrayHasKey('root', $result);
        $this->assertSame('<body><p>test</p></body>', $result['document']->saveXML($result['root']));
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toDOMDocument
     */
    public function testToDOMDocumentWithNodeList()
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML('test');
        $fragment = $document->documentElement->childNodes; // html>body

        $result = $transformer->toDOMDocument($fragment);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('document', $result);
        $this->assertInstanceOf(\DOMDocument::class, $result['document']);
        $this->assertArrayHasKey('root', $result);
        $this->assertSame('<root><body><p>test</p></body></root>', $result['document']->saveXML($result['root']));
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toTextArray
     *
     * @dataProvider toTextArrayDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testToTextArray($input, $expected)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);

        $result = $transformer->toTextArray($document->documentElement, false, false);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toTextArray
     *
     * @dataProvider toTextArrayDataProvider
     *
     * @param $input
     * @param mixed $expectedFlat
     * @param mixed $expected
     */
    public function testToTextArrayAsFlat($input, $expected, $expectedFlat)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);

        $result = $transformer->toTextArray($document->documentElement, false, true);

        $this->assertSame($expectedFlat, $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toTextArray
     *
     * @dataProvider toTextArraySkipChildrenDataProvider
     *
     * @param $input
     * @param mixed $expectedFlat
     * @param mixed $expected
     */
    public function testToTextArrayAsFlatSkipChildren($input, $expected, $expectedFlat)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);
        $node = $document->documentElement->firstChild->childNodes;

        $result = $transformer->toTextArray($node, true, true);

        $this->assertSame($expectedFlat, $result);
    }

    /**
     * @covers \Nokogiri\Dom\DomTransformer::toTextArray
     *
     * @dataProvider toTextArraySkipChildrenDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testToTextArraySkipChildren($input, $expected)
    {
        $transformer = new DomTransformer();
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($input);
        $node = $document->documentElement->firstChild->childNodes;

        $result = $transformer->toTextArray($node, true, false);

        $this->assertSame($expected, $result);
    }

    public function toArrayDataProvider()
    {
        return [
            [
                'input' => '<script>script_content</script><i>1</i><span class="c2">2<span class="ce"></span></span><i>3</i><span>4</span><i>5</i>',
                'expected' => [
                    'head' => [
                        [
                            'script' => [['#cdata-section' => ['script_content']]]
                        ]
                    ],
                    'body' => [
                        [
                            'i' => [['#text' => ['1']], ['#text' => ['3']], ['#text' => ['5']]],
                            'span' => [
                                ['class' => 'c2', '#text' => ['2'], 'span' => [['class' => 'ce']]],
                                ['#text' => ['4']]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function toArrayNoGroupingDataProvider()
    {
        return [
            [
                'input' => '<script>script_content</script><i>1</i><span class="c2">2<span class="ce"></span></span><i>3</i><span>4</span><i>5</i>',
                'expected' => [
                    [
                        ['script_content']
                    ],
                    [
                        ['1'],
                        ['class' => 'c2', '2', ['class' => 'ce']],
                        ['3'],
                        ['4'],
                        ['5']
                    ]
                ]
            ]
        ];
    }

    public function toTextArrayDataProvider()
    {
        return [
            [
                'input' => '<i>1</i><span>2<span></span></span><i>3</i><span>4</span><i>5</i>',
                'expected' => [
                    'body' => [
                        [
                            'i' => [['1'], ['3'], ['5']],
                            'span' => [['2'], ['4']]
                        ]
                    ]
                ],
                'expectedFlat' => ['1', '2', '3', '4', '5']
            ]
        ];
    }

    public function toTextArraySkipChildrenDataProvider()
    {
        return [
            [
                'input' => '<i>1</i><span>2<span>child</span></span><i>3</i><span>4</span><i>5</i>',
                'expected' => [
                    'i' => [['1'], ['3'], ['5']],
                    'span' => [['2'], ['4']]
                ],
                'expectedFlat' => ['1', '2', '3', '4', '5']
            ]
        ];
    }
}
