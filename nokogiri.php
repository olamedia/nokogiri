<?php

/*
 * This file is part of the zero package.
 * Copyright (c) 2012 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Simple HTML parser
 *
 * @author olamedia <olamedia@gmail.com>
 */
class nokogiri implements IteratorAggregate{
	protected $_source = '';
	/**
	 * @var DOMDocument
	 */
	protected $_dom = null;
	/**
	 * @var DOMDocument
	 */
	protected $_tempDom = null;
	/**
	 * @var DOMXpath
	 * */
	protected $_xpath = null;
	/**
 	 * @var libxmlErrors
 	 */
	protected $_libxmlErrors = null;
	public function __construct($htmlString = ''){
		$this->loadHtml($htmlString);
	}
	public function getRegexp(){
		return XpathSubquery::getRegexp();
	}
	public static function fromHtml($htmlString){
		$me = new self();
		$me->loadHtml($htmlString);
		return $me;
	}
	public static function fromHtmlNoCharset($htmlString){
		$me = new self();
		$me->loadHtmlNoCharset($htmlString);
		return $me;
	}
	public static function fromDom($dom){
		$me = new self();
		$me->loadDom($dom);
		return $me;
	}
	public function loadDom($dom){
		$this->_dom = $dom;
	}
	public function loadHtmlNoCharset($htmlString = ''){
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		if (strlen($htmlString)){
			libxml_use_internal_errors(true);
			$this->_libxmlErrors = null;
			$dom->loadHTML('<?xml encoding="UTF-8">'.$htmlString);
			// dirty fix
			foreach ($dom->childNodes as $item){
			    if ($item->nodeType == XML_PI_NODE){
			        $dom->removeChild($item); // remove hack
			        break;
			    }
			}
			$dom->encoding = 'UTF-8'; // insert proper
			$this->_libxmlErrors = libxml_get_errors();
			libxml_clear_errors();
		}
		$this->loadDom($dom);
	}
	public function loadHtml($htmlString = ''){
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		if (strlen($htmlString)){
			libxml_use_internal_errors(true);
			$this->_libxmlErrors = null;
			$dom->loadHTML($htmlString);
			$this->_libxmlErrors = libxml_get_errors();
			libxml_clear_errors();
		}
		$this->loadDom($dom);
	}
	public function getErrors(){
 		return $this->_libxmlErrors;
 	}
	public function __invoke($expression){
		return $this->get($expression);
	}
	public function get($expression, $compile = true){
		return $this->getElements($this->getXpathSubquery($expression, false, $compile));
	}
	protected function getNodes(){

	}
	public function getDom($asIs = false){
		if ($asIs){
			return $this->_dom;
		}
		if ($this->_dom instanceof DOMDocument){
			return $this->_dom;
		}elseif ($this->_dom instanceof DOMNodeList || $this->_dom instanceof DOMElement){
			if ($this->_tempDom === null){
				$this->_tempDom = new DOMDocument('1.0', 'UTF-8');
				$root = $this->_tempDom->createElement('root');
				$this->_tempDom->appendChild($root);
				if($this->_dom instanceof DOMNodeList){
					foreach ($this->_dom as $domElement){
						$domNode = $this->_tempDom->importNode($domElement, true);
						$root->appendChild($domNode);
					}
				}else{
					$domNode = $this->_tempDom->importNode($this->_dom, true);
					$root->appendChild($domNode);
				}
			}
			return $this->_tempDom;
		}
	}
	protected function getXpath(){
		if ($this->_xpath === null){
			$this->_xpath = new DOMXpath($this->getDom());
		}
		return $this->_xpath;
	}
	public function getXpathSubquery($expression, $rel = false, $compile = true){
		return XpathSubquery::get($expression, $rel = false, $compile);
	}
	protected function getElements($xpathQuery){
		if (strlen($xpathQuery)){
			$nodeList = $this->getXpath()->query($xpathQuery);
			if ($nodeList === false){
				throw new Exception('Malformed xpath');
			}
			return self::fromDom($nodeList);
		}
	}
	public function toDom($asIs = false){
		return $this->getDom($asIs);
	}
	public function toXml(){
		return $this->getDom()->saveXML();
	}
	public function toArray($xnode = null){
		$array = array();
		if ($xnode === null){
			if ($this->_dom instanceof DOMNodeList){
				foreach ($this->_dom as $node){
					$array[] = $this->toArray($node);
				}
				return $array;
			}
			$node = $this->getDom();
		}else{
			$node = $xnode;
		}
		if (in_array($node->nodeType, array(XML_TEXT_NODE,XML_COMMENT_NODE))){
			return $node->nodeValue;
		}
		if ($node->hasAttributes()){
			foreach ($node->attributes as $attr){
				$array[$attr->nodeName] = $attr->nodeValue;
			}
		}
		if ($node->hasChildNodes()){
			foreach ($node->childNodes as $childNode){
				$array[$childNode->nodeName][] = $this->toArray($childNode);
			}
		}
		if ($xnode === null){
			$a = reset($array);
			return reset($a); // first child
		}
		return $array;
	}
	public function getIterator(){
		$a = $this->toArray();
		return new ArrayIterator($a);
	}
	protected function _toTextArray($node = null, $skipChildren = false, $singleLevel = true){
		$array = array();
		if ($node === null){
			$node = $this->getDom();
		}
		if ($node instanceof DOMNodeList){
			foreach ($node as $child){
				if ($singleLevel){
					$array = array_merge($array, $this->_toTextArray($child, $skipChildren, $singleLevel));
				}else{
					$array[] = $this->_toTextArray($child, $skipChildren, $singleLevel);
				}
			}
			return $array;
		}
		if (XML_TEXT_NODE === $node->nodeType){
			return array($node->nodeValue);
		}
		if (!$skipChildren){
			if ($node->hasChildNodes()){
				foreach ($node->childNodes as $childNode){
					if ($singleLevel){
						$array = array_merge($array, $this->_toTextArray($childNode, $skipChildren, $singleLevel));
					}else{
						$array[] = $this->_toTextArray($childNode, $skipChildren, $singleLevel);
					}
				}
			}
		}
		return $array;
	}
	public function toTextArray($skipChildren = false, $singleLevel = true){
		return $this->_toTextArray($this->_dom, $skipChildren, $singleLevel);
	}
	public function toText($glue = ' ', $skipChildren = false){
		return implode($glue, $this->toTextArray($skipChildren, true));
	}
}

