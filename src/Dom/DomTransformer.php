<?php
declare(strict_types=1);

namespace Nokogiri\Dom;

final class DomTransformer
{
    public function toArray($node = null, $groupByTags = false, $groupNestedByTags = true)
    {
        if ($node instanceof \DOMNodeList) {
            $result = [];
            foreach ($node as $child) {
                $childResult = $this->toArray($child, $groupNestedByTags, $groupNestedByTags);
                if ($groupByTags && \array_key_exists($child->nodeName, $result) === false) {
                    $result[$child->nodeName] = [];
                }
                if (\is_array($childResult) === false || \count($childResult)) {
                    if ($groupByTags) {
                        $result[$child->nodeName][] = $childResult;
                    } else {
                        $result[] = $childResult;
                    }
                }
            }

            return $result;
        }
        if (
            $node->nodeType === \XML_TEXT_NODE ||
            $node->nodeType === \XML_COMMENT_NODE ||
            $node->nodeType === \XML_CDATA_SECTION_NODE
        ) {
            return $node->nodeValue;
        }
        $nodeResult = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $nodeResult[$attribute->nodeName] = $attribute->nodeValue;
            }
        }

        $childrenResult = $this->toArray($node->childNodes, $groupNestedByTags, $groupNestedByTags);

        $nodeResult = \array_merge($nodeResult, $childrenResult);

        return $nodeResult;
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
            $root = null;
            if ($fragment instanceof \DOMNodeList) {
                $root = $document->createElement('root');
                $document->appendChild($root);
                foreach ($fragment as $domElement) {
                    $domNode = $document->importNode($domElement, true);
                    $root->appendChild($domNode);
                }
            }
            if ($fragment instanceof \DOMElement) {
                $domNode = $document->importNode($fragment, true);
                $root = $domNode;
                $document->appendChild($domNode);
            }

            return [
                'document' => $document,
                'root' => $root
            ];
        }

        throw new \InvalidArgumentException('Invalid fragment given. Should be instance of DOMDocument | DOMNodeList | DOMElement.');
    }

    public function toTextArray($node = null, $skipChildren = false, $flatArray = true, $depth = 0)
    {
        if ($node instanceof \DOMNodeList) {
            $result = [];
            foreach ($node as $child) {
                $childResult = $this->toTextArray($child, $skipChildren, $flatArray, $depth);
                if ($child->nodeType === \XML_TEXT_NODE || $flatArray) {
                    $result = \array_merge($result, $childResult);
                } else {
                    if (\count($childResult)) {
                        $result[$child->nodeName][] = $childResult;
                    }
                }
            }

            return $result;
        }
        if ($node->nodeType === \XML_TEXT_NODE) {
            return [$node->nodeValue];
        }
        // not a text node, can have children
        if ($depth > 0 && ($skipChildren || $node->hasChildNodes() === false)) {
            return [];
        }

        return $this->toTextArray($node->childNodes, $skipChildren, $flatArray, $depth + 1);
    }
}
