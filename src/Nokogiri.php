<?php

namespace Nokogiri;

final class Nokogiri
{
    /**
     * @var \Nokogiri\Css\CssExpressionTransformer
     */
    private $cssExpressionTransformer;

    /**
     * @var \Nokogiri\Dom\Document
     */
    private $document;

    /**
     * @var \Nokogiri\Dom\DocumentFactory
     */
    private $documentFactory;

    /**
     * @var \Nokogiri\Dom\DomTransformer
     */
    private $domTransformer;

    /**
     * @var \DOMDocument | \DOMNodeList | \DOMElement
     */
    private $fragment;

    private $hasWrapper = false;

    /**
     * @var \Nokogiri\Dom\LibXmlErrorSuppressor
     */
    private $suppressor;

    public function __construct($suppressor, $documentFactory, $cssExpressionTransformer, $domTransformer)
    {
        $this->suppressor = $suppressor;
        $this->documentFactory = $documentFactory;
        $this->cssExpressionTransformer = $cssExpressionTransformer;
        $this->domTransformer = $domTransformer;
    }

    public function cssQueryNodes($cssExpression)
    {
        $xpathExpression = $this->cssExpressionTransformer->getXPathSubquery($cssExpression, false);

        return $this->document->xpathQuery($xpathExpression);
    }

    public function get($cssExpression)
    {
        $xpathExpression = $this->cssExpressionTransformer->getXPathSubquery($cssExpression, false);
        $nodeList = $this->document->xpathQuery($xpathExpression);

        return new self($nodeList);
    }

    /**
     * @return \Nokogiri\CssExpressionTransformer
     */
    public function getCssTransformer()
    {
        return $this->cssExpressionTransformer;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getErrors()
    {
        return $this->suppressor->getErrors();
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function hasWrapper()
    {
        return $this->hasWrapper;
    }

    public function loadHtml($htmlString = '', $enforceUtf8 = null, $autoReload = null)
    {
        $this->hasWrapper = false;
        $this->document = $this->documentFactory->createFromHtmlString($htmlString, $enforceUtf8, $autoReload);
        $this->fragment = $this->document->toDOMDocument();
    }

    /**
     * @param \DOMDocument | \DOMNodeList | \DOMElement $fragment
     */
    public function setFragment($fragment)
    {
        $this->fragment = $fragment;
        $this->document = null;
    }

    public function toArray($node = null)
    {
        if ($node === null) {
            $node = $this->fragment;
        }

        return $this->domTransformer->toArray($node);
    }

    public function toDOMDocument()
    {
        if ($this->document === null) {
            $result = $this->domTransformer->toDOMDocument($this->fragment);
            $this->document = $this->documentFactory->createFromDOMDocument($result['document']);
            $this->hasWrapper = true;
        }

        return $this->document->toDOMDocument();
    }

    public function toText($glue = ' ', $skipChildren = false)
    {
        $textArray = $this->domTransformer->toTextArray($this->fragment, $skipChildren, true);

        return \implode($glue, $textArray);
    }

    public function toTextArray($skipChildren = false, $flatArray = true)
    {
        return $this->domTransformer->toTextArray($this->fragment, $skipChildren, $flatArray);
    }
}
