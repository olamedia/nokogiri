<?php
declare(strict_types=1);

namespace Tests\Functional\PHPUnit;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class NokogiriTest extends TestCase
{
    /**
     * @coversNothing
     * @dataProvider toArrayDataProvider
     *
     * @param mixed $input
     * @param mixed $get
     * @param mixed $expected
     */
    public function testToArray($input, $get, $expected)
    {
        $saw = new \nokogiri($input);

        $result = $saw->get($get)->toArray();

        $this->assertSame($expected, $result);
    }

    /**
     * @coversNothing
     * @dataProvider toTextDataProvider
     *
     * @param mixed $input
     * @param mixed $get
     * @param mixed $expected
     */
    public function testToText($input, $get, $expected)
    {
        $saw = new \nokogiri($input);

        $result = $saw->get($get)->toText('--');

        $this->assertSame($expected, $result);
    }

    /**
     * @coversNothing
     * @dataProvider toTextArrayDataProvider
     *
     * @param mixed $input
     * @param mixed $get
     * @param mixed $expected
     * @param mixed $expectedFlat
     */
    public function testToTextArray($input, $get, $expected, $expectedFlat)
    {
        $saw = new \nokogiri($input);

        $result = $saw->get($get)->toTextArray(false, false);
        $flatResult = $saw->get($get)->toTextArray(false, true);

        $this->assertSame($expected, $result);
        $this->assertSame($expectedFlat, $flatResult);
    }

    /**
     * @see testToArray
     *
     * @return array[]
     */
    public function toArrayDataProvider()
    {
        return [
            [
                'input' => '<script>script_content</script>',
                'get' => 'script',
                'expected' => [
                    [
                        '#cdata-section' => [
                            'script_content'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @see testToTextArray
     *
     * @return array[]
     */
    public function toTextArrayDataProvider()
    {
        return [
            [
                'input' => '<script>script_content</script>',
                'get' => 'script',
                'expected' => [],
                'expectedFlat' => []
            ],
            [
                'input' => '<script>script_content</script><span>1</span><p>2</p><span>3</span>',
                'get' => 'span',
                'expected' => [
                    'span' => [
                        ['1'], ['3']
                    ]
                ],
                'expectedFlat' => ['1', '3']
            ],
            [
                'input' => '<span>1</span><p>2</p><span>3</span>',
                'get' => 'span',
                'expected' => [
                    'span' => [
                        ['1'], ['3']
                    ]
                ],
                'expectedFlat' => ['1', '3']
            ]
        ];
    }

    /**
     * @see testToText
     *
     * @return array[]
     */
    public function toTextDataProvider()
    {
        return [
            [
                'input' => '<script>script_content</script>',
                'get' => 'script',
                'expected' => ''
            ],
            [
                'input' => '<script>script_content</script><span>1</span><p>2</p><span>3</span>',
                'get' => 'span',
                'expected' => '1--3'
            ]
        ];
    }
}
