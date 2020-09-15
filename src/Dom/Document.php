<?php

namespace Nokogiri\Dom;

use Nokogiri\Dom\Interfaces\ErrorSuppressorInterface;
use Nokogiri\Exceptions\MalformedXPathException;

final class Document
{
    /**
     * @var \DOMDocument
     */
    private $domDocument;

    private $suppressor;

    private $xpath;

    /**
     * Document constructor.
     *
     * @param ErrorSuppressorInterface $suppressor
     * @param \DOMDocument | null $domDocument
     */
    public function __construct($suppressor, $domDocument = null)
    {
        $this->suppressor = $suppressor;
        if ($domDocument === null) {
            $this->domDocument = new \DOMDocument('1.0', 'UTF-8');
            $this->domDocument->preserveWhiteSpace = false;
        }
        if ($domDocument !== null) {
            $this->domDocument = $domDocument;
        }
    }

    public function loadHtml($htmlString, $enforceEncoding = null, $autoReload = null)
    {
        $this->xpath = null;
        if ($autoReload === null) {
            $autoReload = false;
        }
        if ($enforceEncoding !== null) {
            $this->domDocument = new \DOMDocument('1.0', $enforceEncoding);
            $this->domDocument->preserveWhiteSpace = false;
        }
        if ($htmlString === '') {
            return;
        }
        $charsetPrefix = function ($charset) {
            return '<?xml encoding="' . $charset . '"><meta charset="' . $charset . '" />';
        };
        // Loading from here
        $this->suppressor->start();
        if ($enforceEncoding !== null) {
            $this->domDocument->loadHTML($charsetPrefix($enforceEncoding) . $htmlString,
                \LIBXML_COMPACT | \LIBXML_HTML_NODEFDTD);
        } else {
            $this->domDocument->loadHTML($htmlString, \LIBXML_COMPACT | \LIBXML_HTML_NODEFDTD);
            $detectedEncoding = null;
            $invalidState = false;

            try {
                $detectedEncoding = $this->domDocument->encoding;
            } catch (\Exception $exception) {
                // silently ignore
                $this->suppressor->finish();

                return;
            }
            $correctEncoding = $detectedEncoding === null ? 'UTF-8' : $detectedEncoding;
            $this->domDocument->encoding = $correctEncoding;
            // Trying to reload with detected encoding
            if ($autoReload && $detectedEncoding === null) {
                $this->domDocument->loadHTML($charsetPrefix($correctEncoding) . $htmlString, \LIBXML_COMPACT | \LIBXML_HTML_NODEFDTD);
            }
        }
        if ($this->domDocument->childNodes) {
            foreach ($this->domDocument->childNodes as $item) {
                if ($item->nodeType == \XML_PI_NODE) { // remove <?xml header
                    $this->domDocument->removeChild($item);

                    break;
                }
            }
        }
        $this->suppressor->finish();
    }

    public function toDOMDocument()
    {
        return $this->domDocument;
    }

    public function toXml()
    {
        return $this->domDocument->saveXML();
    }

    public function xpathQuery($xpathExpression)
    {
        if ($this->xpath === null) {
            $this->xpath = new \DOMXPath($this->domDocument);
        }
        if (\strlen($xpathExpression)) {
            try {
                $nodeList = $this->xpath->query($xpathExpression);
            } catch (\Exception $exception) {
                throw new MalformedXPathException('Malformed XPath', 1, $exception);
            }
            if ($nodeList === false) {
                throw new MalformedXPathException('Malformed XPath');
            }

            return $nodeList;
        }

        throw new MalformedXPathException('Empty XPath');
    }
}
