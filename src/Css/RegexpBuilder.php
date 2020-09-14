<?php
declare(strict_types=1);

namespace Nokogiri\Css;

//        $tag = '(?P<tag>[a-z0-9]+)';
//        $attr = '(?:\\[(?P<attr>[^=\\]]+)(?:=(?P<value>[^\\]]+))?\\])';
//        $id = '(?:#(?P<id>[^\\s:>#\\.]+))';
//        $class = '(?:\\.(?P<class>[^\\s:>#\\.]+))';
//        $child = '(?:(?:first|last|nth|only)-child|(?:first|last|only)-of-type|root|empty)';
//        $expr = '(?:\\((?P<expr>[^\\)]+)\\))';
//        $pseudo = '(?::(?P<pseudo>' . $child . ')' . $expr . '?)';
//        $rel = '\\s*(?P<rel>(?:[>\\+~]|\s+))';
final class RegexpBuilder
{
    const ATTR_NAME = '[^=\\]]+';

    const ATTR_VALUE = '[^\\]]+';

    const CLASS_NAME = '[^\\s:>#\\.]+';

    const ID_NAME = '[^\\s:>#\\.]+';

    const PSEUDO_EXPR = '[^\\)]+';

    const PSEUDO_NAME = '(?:(?:first|last|nth|only)-child|(?:first|last|only)-of-type|root|empty)';

    const REL_SYMBOLS = '(?:[>\\+~]|\s+)';

    const TAG_NAME = '[a-z0-9]+';

    public function getRegexp()
    {
        $tag = '(?P<tag>' . self::TAG_NAME . ')';
        $attr = '(?:\\[(?P<attr>' . self::ATTR_NAME . ')(?:=(?P<value>' . self::ATTR_VALUE . '))?\\])';
        $id = '(?:#(?P<id>' . self::ID_NAME . '))';
        $class = '(?:\\.(?P<class>' . self::CLASS_NAME . '))';
        $pseudo = '(?::(?P<pseudo>' . self::PSEUDO_NAME . ')(?:\\((?P<expr>' . self::PSEUDO_EXPR . ')\\))?)';
        $rel = '\\s*(?P<rel>' . self::REL_SYMBOLS . ')';

        $element = '(?:' . $tag . '?' . $attr . '?' . $id . '?' . $class . '?' . $pseudo . '?' . ')';

        return '/^' . $element . $rel . '?' . '/isS';
    }

    public function getUnlabeledRegexp()
    {
        $tag = '(?:' . self::TAG_NAME . ')';
        $attr = '(?:\\[' . self::ATTR_NAME . '(?:=' . self::ATTR_VALUE . ')?\\])';
        $id = '(?:#' . self::ID_NAME . ')';
        $class = '(?:\\.' . self::CLASS_NAME . ')';
        $pseudo = '(?::' . self::PSEUDO_NAME . '(?:\\(' . self::PSEUDO_EXPR . '\\))?)';
        $rel = '(?:\\s*' . self::REL_SYMBOLS . ')';

        $startingTag = $tag . $attr . '?' . $id . '?' . $class . '?' . $pseudo . '?';
        $startingAttr = $attr . $id . '?' . $class . '?' . $pseudo . '?';
        $startingId = $id . $class . '?' . $pseudo . '?';
        $startingClass = $class . $pseudo . '?';
        $startingPseudo = $pseudo;

        $element = '(?:' .
            $startingTag . '|' .
            $startingAttr . '|' .
            $startingId . '|' .
            $startingClass . '|' .
            $startingPseudo .
            ')';

        return '/^' . $element . $rel . '?' . '/isS';
    }

    public function match($selector)
    {
        if (\preg_match($this->getUnlabeledRegexp(), $selector, $unlabeledMatches)) {
            $elementSelector = $unlabeledMatches[0];
            if (\preg_match($this->getRegexp(), $elementSelector, $matches)) {
                return $matches;
            }
        }
    }
}
