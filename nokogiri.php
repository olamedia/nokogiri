<?php
declare(strict_types=1);

/*
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Facade for main Nokogiri class, follows first version's logic.
 */
final class nokogiri implements IteratorAggregate
{
    /**
     * @var \Nokogiri\Dom\Document
     */
    private $document;

    /**
     * @var \DOMDocument | \DOMNodeList | \DOMElement
     */
    private $dom;

    /**
     * @var \Nokogiri\Nokogiri
     */
    private $nokogiri;

    /**
     * @var \Nokogiri\Dom\LibXmlErrorSuppressor
     */
    private $suppressor;

    public function __construct($value = null, $enforceUtf8 = null, $autoReload = null)
    {
        $suppressor = new \Nokogiri\Dom\LibXmlErrorSuppressor();
        $documentFactory = new \Nokogiri\Dom\DocumentFactory($suppressor);
        $cssRegexpBuilder = new \Nokogiri\Css\RegexpBuilder();
        $cssExpressionTransformer = new \Nokogiri\Css\CssExpressionTransformer($cssRegexpBuilder);
        $domTransformer = new \Nokogiri\Dom\DomTransformer();
        $this->nokogiri = new Nokogiri\Nokogiri($suppressor, $documentFactory, $cssExpressionTransformer, $domTransformer);
        if (\is_string($value)) {
            $this->nokogiri->loadHtml($value, $enforceUtf8, $autoReload);
        }
        if ($value instanceof \DOMDocument || $value instanceof \DOMNodeList || $value instanceof \DOMElement) {
            $this->nokogiri->setFragment($value);
        }
    }

    public function __invoke($cssExpression)
    {
        return $this->get($cssExpression);
    }

    public static function fromDom($dom)
    {
        return new self($dom);
    }

    // ---------------------------------------------------- FACADE -----------------------------------------------------
    public static function fromHtml($htmlString)
    {
        return new self($htmlString, null, true);
    }

    public static function fromHtmlNoCharset($htmlString)
    {
        return new self($htmlString, true, true);
    }

    public function get($cssExpression)
    {
        $nodeList = $this->nokogiri->cssQueryNodes($cssExpression);

        return new self($nodeList, false);
    }

    public function getDom($asIs = false)
    {
        if ($asIs) {
            return $this->nokogiri->getFragment();
        }

        return $this->nokogiri->toDOMDocument();
    }

    public function getErrors()
    {
        return $this->nokogiri->getErrors();
    }

    public function getIterator()
    {
        $result = $this->toArray();

        return new \ArrayIterator($result);
    }

    public function getXpathSubquery($cssExpression)
    {
        return $this->nokogiri->getCssTransformer()->getXPathSubquery($cssExpression);
    }

    public function loadDom($dom)
    {
        $this->nokogiri->setFragment($dom);
    }

    /**
     * Tries to load HTML as is, relies on meta or xml charset.
     *
     * @param string $htmlString
     */
    public function loadHtml($htmlString = '')
    {
        $this->nokogiri->loadHtml($htmlString, null, true);
    }

    /**
     * Tries to load HTML in UTF-8, convert encoding yourself.
     *
     * @param string $htmlString
     */
    public function loadHtmlNoCharset($htmlString = '')
    {
        $this->nokogiri->loadHtml($htmlString, true, true);
    }

    public function toArray($node = null)
    {
        return $this->nokogiri->toArray($node);
    }

    public function toDom($asIs = false)
    {
        return $this->getDom($asIs);
    }

    public function toText($glue = ' ', $skipChildren = false)
    {
        return $this->nokogiri->toText($glue, $skipChildren);
    }

    public function toTextArray($skipChildren = false, $flatArray = true)
    {
        return $this->nokogiri->toTextArray($skipChildren, $flatArray);
    }

    public function toXml()
    {
        $this->nokogiri->toDOMDocument(); // ensure document created from fragment
        return $this->nokogiri->getDocument()->toXml();
    }
}
