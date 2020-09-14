<?php
declare(strict_types=1);

namespace Nokogiri\Dom;

use Nokogiri\Iterators\DomTraverser;
use Nokogiri\Iterators\NodeHandlers;

final class DomTransformer
{
    private $traverser;

    public function __construct()
    {
        $this->traverser = new DomTraverser();
    }

    public static function loadHtmlWithCharset($domDocument, $htmlString, $charset)
    {
        $domDocument->loadHTML('<meta charset="' . $charset . '" />' . $htmlString, self::LOAD_HTML_OPTIONS);
    }

    public static function removePiNode($domDocument)
    {
        if ($domDocument->childNodes) {
            foreach ($domDocument->childNodes as $item) {
                if ($item->nodeType == \XML_PI_NODE) { // remove <?xml > header
                    $domDocument->removeChild($item);

                    break;
                }
            }
        }
    }

    public function toArray($node = null, $removeWrapper = false)
    {
        $handlers = new NodeHandlers();
        $handlers->list = $this->traverser;
        $handlers->node = function ($node) use ($handlers) {
            $result = [];
            if ($node->nodeType === \XML_PI_NODE) {
                return '';
            }
            if (
                $node->nodeType === \XML_TEXT_NODE ||
                $node->nodeType === \XML_COMMENT_NODE ||
                $node->nodeType === \XML_CDATA_SECTION_NODE
            ) {
                return $node->nodeValue;
            }
            if ($node->hasAttributes()) {
                foreach ($node->attributes as $attribute) {
                    $result[$attribute->nodeName] = $attribute->nodeValue;
                }
            }
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $childNode) {
                    $result[$childNode->nodeName][] = $handlers->list->traverse($childNode, $handlers, false);
                }
            }
//            if ($xnode === null) {
//                $a = reset($result);
//                return reset($a); // first child
//            }
            return $result;
        };
        $result = $handlers->list->traverse($node, $handlers, false);
        if ($removeWrapper) {
            $a = reset($result);

            return reset($a);
        }

        return $result;
    }

    public function toDOMDocument($fragment)
    {
        if ($fragment instanceof \DOMDocument) {
            return [
                'document' => $fragment,
                'root' => $fragment
            ];
        }
        if ($fragment instanceof \DOMNodeList || $fragment instanceof \DOMElement) {
            $document = new \DOMDocument('1.0', 'UTF-8');
            $root = $document->createElement('root');
            $document->appendChild($root);
            if ($fragment instanceof \DOMNodeList) {
                foreach ($fragment as $domElement) {
                    $domNode = $document->importNode($domElement, true);
                    $root->appendChild($domNode);
                }
            }
            if ($fragment instanceof \DOMElement) {
                $domNode = $document->importNode($fragment, true);
                $root->appendChild($domNode);
            }

            return [
                'document' => $document,
                'root' => $root
            ];
        }
    }

    public function toTextArray($node = null, $skipChildren = false, $flatArray = true)
    {
        $handlers = new NodeHandlers();
        $handlers->list = $this->traverser;
        $handlers->node = function ($node) use ($skipChildren, $flatArray, $handlers) {
            if ($node->nodeType === \XML_TEXT_NODE) {
                return [$node->nodeValue];
            }
            $array = [];
            if (!$skipChildren) {
                if ($node->hasChildNodes()) {
                    $children = $handlers->list->traverse($node->childNodes, $handlers, $flatArray);
                    if ($flatArray) {
                        $array = \array_merge($array, $children);
                    } else {
                        $array[] = $children;
                    }
                }
            }

            return $array;
        };

        return $handlers->list->traverse($node, $handlers, $flatArray);
    }
}
