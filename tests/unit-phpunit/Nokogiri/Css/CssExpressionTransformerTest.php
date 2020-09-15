<?php

namespace Tests\Unit\PHPUnit\Nokogiri\Css;

use Nokogiri\Css\CssExpressionTransformer;
use Nokogiri\Css\RegexpBuilder;
use Nokogiri\Exceptions\MalformedCssException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Nokogiri\Css\CssExpressionTransformer
 */
final class CssExpressionTransformerTest extends TestCase
{
    public function malformedQueryDataProvider()
    {
        return [
            ['input' => 'div>'],
            ['input' => 'div>==='],
        ];
    }

    public function queryDataProvider()
    {
        return [
            ['input' => '*', 'expected' => '//*'],
            ['input' => 'div', 'expected' => '//div'],
            ['input' => '[name]', 'expected' => '//*[@name]'],
            ['input' => '[name=value]', 'expected' => '//*[@name=\'value\']'],
            ['input' => '#my-id', 'expected' => '//*[@id=\'my-id\']'],
            ['input' => 'div#my-id', 'expected' => '//div[@id=\'my-id\']'],
            ['input' => '.className', 'expected' => '//*[contains(concat(" ", normalize-space(@class), " "), " className ")]'],
            ['input' => 'div.className', 'expected' => '//div[contains(concat(" ", normalize-space(@class), " "), " className ")]'],
            [
                'input' => 'div.className:first-child',
                'expected' => '//div[(contains(concat(" ", normalize-space(@class), " "), " className ")) and (count(preceding-sibling::*) = 0)]'
            ],
            ['input' => 'div p', 'expected' => '//div/descendant::p'],
            ['input' => 'div>p', 'expected' => '//div/p'],
            ['input' => 'div~p', 'expected' => '//div/following-sibling::p'],
            ['input' => 'div+p', 'expected' => '//div/following-sibling::p[count(preceding-sibling::*) = 0]'],
            ['input' => 'div:root', 'expected' => '//div[not(parent::*)]'],
            ['input' => 'div:empty', 'expected' => '//div[not(*) and not(string-length())]'],
            ['input' => 'div:only-child', 'expected' => '//div[count(parent::*/child::*) = 1]'],
            ['input' => 'div:only-of-type', 'expected' => '//div[count(parent::*/child::div) = 1]'],
            ['input' => 'div:first-child', 'expected' => '//div[count(preceding-sibling::*) = 0]'],
            ['input' => 'div:last-child', 'expected' => '//div[count(following-sibling::*) = 0]'],
            ['input' => 'div:first-of-type', 'expected' => '//div[count(preceding-sibling::div) = 0]'],
            ['input' => 'div:last-of-type', 'expected' => '//div[count(following-sibling::div) = 0]'],

            ['input' => 'div:nth-child(5)', 'expected' => '//div[count(preceding-sibling::*) = 4]'],
            ['input' => ':nth-child(n)', 'expected' => '//*'],
            ['input' => ':nth-child(n+5)', 'expected' => '//*[count(preceding-sibling::*)>=4]'],
            ['input' => ':nth-child(-n+2)', 'expected' => '//*[count(preceding-sibling::*)<=1]'],
            ['input' => 'div:nth-child(4n)', 'expected' => '//div[(count(preceding-sibling::*)+1) mod 4=0]'],
            ['input' => 'div:nth-child(5n+7)', 'expected' => '//div[count(preceding-sibling::*)>=6 and (count(preceding-sibling::*)-6) mod 5=0]'],
            ['input' => 'div:nth-child(6n-8)', 'expected' => '//div[(count(preceding-sibling::*)+9) mod 6=0]'],
            ['input' => 'div:nth-child(odd)', 'expected' => '//div[count(preceding-sibling::*) mod 2=0]'],
            ['input' => 'div:nth-child(even)', 'expected' => '//div[(count(preceding-sibling::*)+1) mod 2=0]'],
        ];
    }

    /**
     * @covers \Nokogiri\Css\CssExpressionTransformer
     * @covers \Nokogiri\Css\CssExpressionTransformer::__construct
     * @covers \Nokogiri\Css\CssExpressionTransformer::getXPathSubquery
     *
     * @dataProvider queryDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testGetXPathSubquery($input, $expected)
    {
        $builder = new RegexpBuilder();
        $transformer = new CssExpressionTransformer($builder);

        $result = $transformer->getXPathSubquery($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Nokogiri\Css\CssExpressionTransformer::__construct
     * @covers \Nokogiri\Css\CssExpressionTransformer::getXPathSubquery
     *
     * @dataProvider malformedQueryDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testGetXPathSubqueryThrowsMalformedCssException($input)
    {
        $builder = new RegexpBuilder();
        $transformer = new CssExpressionTransformer($builder);

        $this->expectException(MalformedCssException::class);
        $this->expectExceptionMessage('Malformed CSS query: div>');

        $result = $transformer->getXPathSubquery($input);
    }
}
