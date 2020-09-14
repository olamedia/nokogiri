<?php
declare(strict_types=1);

namespace Tests\Integration\PHPUnit;

use Nokogiri\Old\nokogiri;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \nokogiri
 */
final class NokogiriTest extends TestCase
{
    /**
     * @see testGet
     *
     * @return array[]
     */
    public function getDataProvider()
    {
        $tag = '<root><div>text</div><p>paragraph</p></root>';
        $class = '<root><div class="class1">text1</div><div class="class2">text2</div><p class="class1">paragraph</p></root>';
        $nth = '<root><div>text1</div><div>text2</div><div>text3</div><p>paragraph</p></root>';
        $nth2 = '<root><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i><i>6</i><i>7</i><i>8</i><i>9</i></root>';

        $node = function ($text) {
            return ['#text' => [$text]];
        };

        return [
            ['test' => $tag, 'css' => 'div',
                'expected' => [['#text' => ['text']]]
            ],
            ['test' => $class, 'css' => '.class1',
                'expected' => [
                    ['class' => 'class1', '#text' => ['text1']],
                    ['class' => 'class1', '#text' => ['paragraph']]
                ]
            ],
            ['test' => $class, 'css' => '.class2',
                'expected' => [
                    ['class' => 'class2', '#text' => ['text2']]
                ]
            ],
            ['test' => $nth, 'css' => 'div:first-child',
                'expected' => [['#text' => ['text1']]]
            ],
            ['test' => $nth, 'css' => 'div:last-child',
                'expected' => []
            ],
            //            ['test' => $nth, 'css' => 'div:last-of-type',
            //                'expected' => [['#text' => ['text3']]]
            //            ],
            ['test' => $nth, 'css' => 'div:nth-child(1)',
                'expected' => [['#text' => ['text1']]]
            ],
            ['test' => $nth, 'css' => 'div:nth-child(2)',
                'expected' => [['#text' => ['text2']]]
            ],
            ['test' => $nth, 'css' => 'div:nth-child(3)',
                'expected' => [['#text' => ['text3']]]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(n+2)',
                'expected' => [['#text' => ['2']], ['#text' => ['3']], ['#text' => ['4']],
                    ['#text' => ['5']], ['#text' => ['6']], ['#text' => ['7']], ['#text' => ['8']], ['#text' => ['9']]]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(-n+2)',
                'expected' => [$node('1'), $node('2')]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(-2n+5)',
                'expected' => [$node('1'), $node('3'), $node('5')]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(3n)',
                'expected' => [$node('3'), $node('6'), $node('9')]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(3n+1)',
                'expected' => [$node('1'), $node('4'), $node('7')]
            ],
            ['test' => $nth2, 'css' => 'i:nth-child(3n-1)',
                'expected' => [$node('2'), $node('5'), $node('8')]
            ]
        ];
    }

    /**
     * @see testGet
     *
     * @return array[]
     */
    public function getNthDataProvider()
    {
        $nth = '<root><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i><i>6</i><i>7</i><i>8</i><i>9</i></root>';

        return [
            ['test' => $nth, 'css' => 'i:first-child',
                'expected' => '1'
            ],
            ['test' => $nth, 'css' => 'i:last-child',
                'expected' => '9'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(1)',
                'expected' => '1'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(2)',
                'expected' => '2'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(-1)',
                'expected' => ''
            ],
            ['test' => $nth, 'css' => 'i:nth-child(n+2)',
                'expected' => '2--3--4--5--6--7--8--9'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(-n+2)',
                'expected' => '1--2'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(-2n+5)',
                'expected' => '1--3--5'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(3n)',
                'expected' => '3--6--9'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(3n+1)',
                'expected' => '1--4--7'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(3n-1)',
                'expected' => '2--5--8'
            ],
            ['test' => $nth, 'css' => 'i:nth-child(6n-8)',
                'expected' => '4'
            ]
        ];
    }

    /**
     * @covers \nokogiri::__construct
     */
    public function testConstruct_WithDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $saw = new \nokogiri($dom);

        $result = $saw->toXml();

        $this->assertSame($result, '<?xml version="1.0" encoding="UTF-8"?>
');
    }

    /**
     * @covers \nokogiri::__construct
     */
    public function testConstruct_WithEmptyHtml()
    {
        $saw = new \nokogiri('');

        $result = $saw->toXml();

        $this->assertSame($result, '<?xml version="1.0" encoding="UTF-8"?>
');
    }

    /**
     * @covers \nokogiri::fromDom()
     */
    public function testFromDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $oldSaw = nokogiri::fromDom($dom);

        $saw = \nokogiri::fromDom($dom);

        $this->assertSame($oldSaw->getDom(true), $saw->getDom(true));
        $this->assertSame($dom, $saw->getDom(true));
    }

    /**
     * @covers \nokogiri::fromHtmlNoCharset()
     */
    public function testFromHtmlNoCharset()
    {
        $saw = \nokogiri::fromHtmlNoCharset('<div>текст</div>');

        $this->assertStringContainsString('<body><div>текст</div></body>', $saw->toXml());
    }

    /**
     * @covers \nokogiri::fromHtml()
     */
    public function testFromHtml_NoMetaUtf8()
    {
        $saw = \nokogiri::fromHtml('<div>текст</div>');

        $result = $saw->toXml();

        $this->assertStringContainsString('<body><div>текст</div></body>', $result);
    }

    /**
     * @covers \nokogiri::fromHtml()
     */
    public function testFromHtml_WithUtf8InXmlTag()
    {
        $saw = \nokogiri::fromHtml('<?xml encoding="UTF-8"?><div>текст</div>');

        $result = $saw->toXml();

        $this->assertStringContainsString('<body><div>текст</div></body>', $result);
    }

    /**
     * @covers \nokogiri::__invoke()
     * @covers \nokogiri::get()
     * @dataProvider getDataProvider
     *
     * @param mixed $test
     * @param mixed $css
     * @param mixed $expected
     */
    public function testGet($test, $css, $expected)
    {
        $htmlString = $test;
        $oldSaw = new nokogiri();
        $oldSaw->loadHtml($htmlString);
        $saw = new \nokogiri();
        $saw->loadHtml($htmlString);
        $oldResult = $oldSaw->get($css);

        $result = $saw->get($css);
        $invokeResult = $saw($css);

        $this->assertInstanceOf(\nokogiri::class, $result);
        $this->assertSame($expected, $result->toArray());
    }

    /**
     * @covers \nokogiri::getDom()
     */
    public function testGetDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $oldSaw = new nokogiri();
        $oldSaw->loadDom($dom);
        $oldResult = $oldSaw->getDom(true);
        $saw = new \nokogiri();
        $saw->loadDom($dom);

        $result = $saw->getDom(true);

        $this->assertSame($oldResult, $result);
        $this->assertSame($dom, $result);
    }

    /**
     * @covers \nokogiri::getDom()
     */
    public function testGetDom_AsDocument()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $oldSaw = new nokogiri();
        $oldSaw->loadDom($dom);
        $oldResult = $oldSaw->getDom(false);
        $saw = new \nokogiri();
        $saw->loadDom($dom);

        $result = $saw->getDom(false);

        $this->assertSame($oldResult, $result);
        $this->assertSame($dom, $result);
    }

    /**
     * @covers \nokogiri::getErrors()
     */
    public function testGetErrors()
    {
        $saw = new \nokogiri('</broken>');

        $result = $saw->getErrors();

        $this->assertIsArray($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertInstanceOf(\LibXMLError::class, $result[0]);
        $this->assertSame('Unexpected end tag : broken', \trim($result[0]->message));
    }

    /**
     * @covers \nokogiri::getIterator()
     *
     * @throws \Exception
     */
    public function testGetIterator()
    {
        $saw = new \nokogiri('<i>text</i>');

        $result = $saw->getIterator();

        $this->assertInstanceOf(\Iterator::class, $result);
    }

    /**
     * @covers \nokogiri::__invoke()
     * @covers \nokogiri::get()
     * @dataProvider getNthDataProvider
     *
     * @param mixed $test
     * @param mixed $css
     * @param mixed $expected
     */
    public function testGet_Nth($test, $css, $expected)
    {
        $htmlString = $test;
        $oldSaw = new nokogiri();
        $oldSaw->loadHtml($htmlString);
        $saw = new \nokogiri();
        $saw->loadHtml($htmlString);
        $oldResult = $oldSaw->get($css);

        $result = $saw->get($css);
        $invokeResult = $saw($css);

        $this->assertInstanceOf(\nokogiri::class, $result);
        $this->assertSame($expected, \implode('--', \array_map(function ($value) {
            return $value['#text'][0];
        }, $result->toArray())));
    }

    /**
     * @covers \nokogiri::getXpathSubquery()
     */
    public function testGetXpathSubquery()
    {
        $oldSaw = new nokogiri();
        $saw = new \nokogiri();
        $oldResult = $oldSaw->getXpathSubquery('div.className');

        $result = $saw->getXpathSubquery('div.className');

        $this->assertSame($oldResult, $result);
        $this->assertSame('//div[contains(concat(" ", normalize-space(@class), " "), " className ")]', $result);
    }

    /**
     * @covers \nokogiri::loadDom()
     */
    public function testLoadDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $oldSaw = new nokogiri();
        $oldSaw->loadDom($dom);
        $saw = new \nokogiri();

        $saw->loadDom($dom);

        $this->assertSame($oldSaw->getDom(true), $saw->getDom(true));
        $this->assertSame($dom, $saw->getDom(true));
    }

    /**
     * @covers \nokogiri::loadHtml()
     */
    public function testLoadHtml()
    {
        $saw = new \nokogiri();

        $saw->loadHtml('<html><head><meta charset="cp1251" /></head><div>текст</div></html>');

        $this->assertSame('<?xml version="1.0" encoding="cp1251" standalone="yes"?>
<html><head><meta charset="cp1251"/></head><body><div>текст</div></body></html>
', $saw->toXml());
    }

    /**
     * @covers \nokogiri::loadHtmlNoCharset
     */
    public function testLoadHtmlNoCharset()
    {
        $saw = new \nokogiri();

        $saw->loadHtmlNoCharset('<div>текст</div>');

        $this->assertStringContainsString('<body><div>текст</div></body>', $saw->toXml());
    }

    /**
     * @covers \nokogiri::toArray()
     */
    public function testToArray()
    {
        $saw = new \nokogiri();
        $saw->loadHtml('<html><div>text</div><p>paragraph</p></html>');

        $result = $saw->toArray();

        $this->assertSame([
            [
                'div' => [['#text' => ['text']]],
                'p' => [['#text' => ['paragraph']]]
            ]
        ], $result['html'][0]['body']);
    }

    /**
     * @covers \nokogiri::toDom()
     */
    public function testToDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $oldSaw = new nokogiri();
        $oldSaw->loadDom($dom);
        $oldResult = $oldSaw->getDom(true);
        $saw = new \nokogiri();
        $saw->loadDom($dom);

        $result = $saw->toDom(true);

        $this->assertSame($oldResult, $result);
        $this->assertSame($dom, $result);
    }

    /**
     * @covers \nokogiri::toText()
     */
    public function testToText()
    {
        $oldSaw = new nokogiri();
        $oldSaw->loadHtml('<div>text</div><p>paragraph</p>');
        $saw = new \nokogiri();
        $saw->loadHtml('<div>text</div><p>paragraph</p>');
        $oldResult = $oldSaw->toText('--');

        $result = $saw->toText('--');

        $this->assertSame($oldResult, $result);
        $this->assertSame('text--paragraph', $result);
    }

    /**
     * @covers \nokogiri::toTextArray()
     */
    public function testToTextArray()
    {
        $oldSaw = new nokogiri();
        $oldSaw->loadHtml('<div>text</div><p>paragraph</p>');
        $saw = new \nokogiri();
        $saw->loadHtml('<div>text</div><p>paragraph</p>');
        $oldResult = $oldSaw->toTextArray();

        $result = $saw->toTextArray();

        $this->assertSame($oldResult, $result);
        $this->assertSame(['text', 'paragraph'], $result);
    }

    /**
     * @covers \nokogiri::toXml()
     */
    public function testToXml()
    {
        $saw = new \nokogiri('<div>text</div>');

        $result = $saw->toXml();

        $this->assertSame('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<html><body><div>text</div></body></html>
', $result);
    }
}
