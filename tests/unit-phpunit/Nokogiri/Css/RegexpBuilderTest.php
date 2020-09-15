<?php

namespace Tests\Unit\PHPUnit\Nokogiri\Css;

use Nokogiri\Css\RegexpBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class RegexpBuilderTest extends TestCase
{
    public function malformedQueryDataProvider()
    {
        return [
            ['input' => '===', 'expected' => null],
        ];
    }

    public function queryDataProvider()
    {
        return [
            ['input' => 'div', 'expected' => ['tag' => 'div']],
            ['input' => '[name]', 'expected' => ['attr' => 'name']],
            ['input' => '[name=value]', 'expected' => ['attr' => 'name', 'value' => 'value']],
            ['input' => '#my-id', 'expected' => ['id' => 'my-id']],
            ['input' => 'div#my-id', 'expected' => ['tag' => 'div', 'id' => 'my-id']],
            ['input' => '.className', 'expected' => ['class' => 'className']],
            ['input' => '.className:first-child', 'expected' => ['class' => 'className', 'pseudo' => 'first-child']],
            ['input' => 'div.className', 'expected' => ['tag' => 'div', 'class' => 'className']],
            ['input' => 'div:first-child', 'expected' => ['tag' => 'div', 'pseudo' => 'first-child']],

            ['input' => 'div:nth-child(5)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => '5']],
            ['input' => ':nth-child(n)', 'expected' => ['pseudo' => 'nth-child', 'expr' => 'n']],
            ['input' => ':nth-child(n+1)', 'expected' => ['pseudo' => 'nth-child', 'expr' => 'n+1']],
            ['input' => 'div:nth-child(4n)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => '4n']],
            ['input' => 'div:nth-child(5n+7)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => '5n+7']],
            ['input' => 'div:nth-child(5n-7)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => '5n-7']],
            ['input' => 'div:nth-child(-5n-7)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => '-5n-7']],
            ['input' => 'div:nth-child(odd)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => 'odd']],
            ['input' => 'div:nth-child(even)', 'expected' => ['tag' => 'div', 'pseudo' => 'nth-child', 'expr' => 'even']],
            ['input' => 'div:last-child', 'expected' => ['tag' => 'div', 'pseudo' => 'last-child']],
        ];
    }

    public function sequenceQueryDataProvider()
    {
        return [
            ['input' => 'div p', 'expected' => [
                'tag' => 'div'
            ]],
            ['input' => 'div>p', 'expected' => [
                'tag' => 'div', 'rel' => '>'
            ]],
            ['input' => 'div+p', 'expected' => [
                'tag' => 'div', 'rel' => '+'
            ]],
            ['input' => 'div~p', 'expected' => [
                'tag' => 'div', 'rel' => '~'
            ]],
        ];
    }

    /**
     * @covers \Nokogiri\Css\RegexpBuilder
     *
     * @dataProvider malformedQueryDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testMalformedCssQuery($input, $expected)
    {
        $builder = new RegexpBuilder();

        $result = $builder->match($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Nokogiri\Css\RegexpBuilder
     *
     * @dataProvider queryDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testMatchSimple($input, $expected)
    {
        $builder = new RegexpBuilder();

        $result = $builder->match($input);

        $this->assertTrue(\is_array($result));
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertSame($value, $result[$key]);
        }
    }

    /**
     * @covers \Nokogiri\Css\RegexpBuilder
     *
     * @dataProvider sequenceQueryDataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testMatchWithSequence($input, $expected)
    {
        $builder = new RegexpBuilder();

        $result = $builder->match($input);

        $this->assertTrue(\is_array($result));
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertSame($value, $result[$key]);
        }
    }
}
