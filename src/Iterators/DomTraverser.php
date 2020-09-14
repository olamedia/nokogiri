<?php
declare(strict_types=1);

namespace Nokogiri\Iterators;

final class DomTraverser
{
    /**
     * @param $node
     * @param \Nokogiri\Iterators\NodeHandlers $handlers
     * @param bool $flatArray
     *
     * @return array
     */
    public function traverse($node, $handlers, $flatArray)
    {
        if ($node instanceof \DOMNodeList) {
            $result = [];
            foreach ($node as $child) {
                $childResult = $handlers->list->traverse($child, $handlers, $flatArray);
                if ($flatArray) {
                    $result = \array_merge($result, $childResult);
                } else {
                    $result[] = $childResult;
                }
            }

            return $result;
        }
        $nodeHandler = $handlers->node;

        return $nodeHandler($node);
    }
}
