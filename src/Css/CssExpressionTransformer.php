<?php
declare(strict_types=1);

namespace Nokogiri\Css;

use Nokogiri\Exceptions\MalformedCssException;

/**
 * :root /
 * :root > body /body
 * #id //*[@id="id"]
 * div p //div//p
 * ul > li //ul/li
 * a:first-child //a[1]
 * a:last-child //a[last()]
 * ul > li:first-child //ul/li[1]
 * li#id:first-child //li[@id="id"][1].
 */
final class CssExpressionTransformer
{
    private static $regexp;

    private static $unlabeledRegexp;

    public function __construct($regexpBuilder)
    {
        self::$unlabeledRegexp = $regexpBuilder->getUnlabeledRegexp();
        self::$regexp = $regexpBuilder->getRegexp();
    }

    /**
     * Builds XPath from css-selector.
     *
     * @param $expression
     * @param false $rel
     */
    public function getXPathSubquery($expression, $rel = false): string
    {
        $queryStart = '//';
        $nextElementRel = false;
        $expectNextElement = false;

        if ($rel !== false) {
            if ($rel === '') {
                $queryStart = '/descendant::';
            }
            if ($rel === '>') {
                $queryStart = '/';
            }
            if ($rel === '~') {
                $queryStart = '/following-sibling::';
            }
            if ($rel === '+') {
                $queryStart = '/following-sibling::';
            }
        }

        $query = '';

        $matched = false;
        $matches = null;
        if (\preg_match(self::$unlabeledRegexp, $expression, $unlabeledMatches)) {
            $elementSelector = $unlabeledMatches[0];
            if (\preg_match(self::$regexp, $elementSelector, $matches)) {
                $matched = true;
                $nextElementRel = isset($matches['rel']) ? \trim($matches['rel']) : '';
                $expectNextElement = $nextElementRel !== ''; // separator can't be final
            }
        }

        if ($matched) {
            $tagQuery = ((isset($matches['tag']) && $matches['tag'] !== '') ? $matches['tag'] : '*');
            $left = \trim(\substr($expression, \strlen($matches[0])));

            $brackets = [];
            if (isset($matches['id']) && $matches['id'] !== '') {
                $brackets[] = "@id='" . $matches['id'] . "'";
            }
            if (isset($matches['attr']) && $matches['attr'] !== '') {
                if (!(isset($matches['value']))) {
                    $brackets[] = '@' . $matches['attr'];
                } else {
                    $attrValue = !empty($matches['value']) ? $matches['value'] : '';
                    $brackets[] = '@' . $matches['attr'] . "='" . $attrValue . "'";
                }
            }
            if (isset($matches['class']) && $matches['class'] !== '') {
                $brackets[] = 'contains(concat(" ", normalize-space(@class), " "), " ' . $matches['class'] . ' ")';
            }
            if (isset($matches['pseudo']) && $matches['pseudo'] !== '') {
                if ($matches['pseudo'] === 'root') {
                    $brackets[] = 'not(parent::*)';
                } elseif ($matches['pseudo'] === 'first-child') {
                    $brackets[] = 'count(preceding-sibling::*) = 0'; // 1
                } elseif ($matches['pseudo'] === 'first-of-type') {
                    $brackets[] = 'count(preceding-sibling::' . $tagQuery . ') = 0';
                } elseif ($matches['pseudo'] === 'last-child') {
                    $brackets[] = 'count(following-sibling::*) = 0'; // last()
                } elseif ($matches['pseudo'] === 'last-of-type') {
                    $brackets[] = 'count(following-sibling::' . $tagQuery . ') = 0';
                } elseif ($matches['pseudo'] === 'only-child') {
                    $brackets[] = 'count(parent::*/child::*) = 1';
                } elseif ($matches['pseudo'] === 'only-of-type') {
                    $brackets[] = 'count(parent::*/child::' . $tagQuery . ') = 1';
                } elseif ($matches['pseudo'] === 'empty') {
                    // The :empty CSS pseudo-class represents any element that has no children.
                    // Children can be either element nodes or text (including whitespace).
                    $brackets[] = 'not(*) and not(string-length())';
                } elseif ($matches['pseudo'] === 'nth-child') {
                    if (isset($matches['expr']) && $matches['expr'] !== '') {
                        $e = $matches['expr'];
                        // Convert aliases
                        if ($e === 'odd') {
                            $e = '2n+1';
                        } elseif ($e === 'even') {
                            $e = '2n';
                        }

                        if (\preg_match('/^-?[0-9]+$/', $e)) {
                            $brackets[] = 'count(preceding-sibling::*) = ' . (intval($e) - 1);
                        } elseif (\preg_match('/^n$/', $e)) {
//                            $brackets[] = 'position() = ' . $e;
                        } elseif (\preg_match('/^(?P<mul>[\\-]?[0-9]*)?n(?P<pos>[\\+\\-][0-9]+)?$/is', $e, $esubs)) {
                            $M = isset($esubs['mul']) ? $esubs['mul'] : 1;
                            if ($M === '-') {
                                $M = -1;
                            }
                            if ($M === '') {
                                $M = 1;
                            }
                            $M = intval($M);
                            $N = isset($esubs['pos']) ? intval($esubs['pos']) : 0;
                            $sign = function ($x) {
                                return $x < 0 ? $x : '+' . $x;
                            };
                            $pos = 'count(preceding-sibling::*)';
                            $position = '(' . $pos . '+1)';

                            $subquery = [];
                            if ($N - 1 > 0) {
                                $subquery[] = $pos . ($M > 0 ? '>=' : '<=') . ($N - 1);
                            }
                            if ($M !== 1 && $M !== -1) {
                                $position2 = 1 - $N === 0 ? $pos : '(' . $pos . $sign(1 - $N) . ')';
                                $subquery[] = $position2 . ' mod ' . $M . '=0';
                            }

                            $brackets[] = \implode(' and ', $subquery);
                        }
                    }
                }
            }
            if ($rel === '+') {
                $brackets[] = 'count(preceding-sibling::*) = 0'; // 1
            }
            $bracketQuery = '';
            $countInBrackets = \count($brackets);
            if ($countInBrackets === 1) {
                $bracketQuery = '[' . \implode(' and ', $brackets) . ']';
            }
            if ($countInBrackets > 1) {
                $bracketQuery = '[(' . \implode(') and (', $brackets) . ')]';
            }

            $left = \trim(\substr($expression, \strlen($matches[0])));

            $query = $queryStart . $tagQuery . $bracketQuery;
            if ($expectNextElement && $left === '') {
                throw new MalformedCssException('Malformed CSS query: ' . $expression);
            }
            if ($left !== '') {
                $nextElementQuery = $this->getXPathSubquery($left, $nextElementRel);
                if ($expectNextElement && $nextElementQuery === '') {
                    throw new MalformedCssException('Malformed CSS query: ' . $expression);
                }
                $query .= $nextElementQuery;
            }
        }

        return $query;
    }
}
