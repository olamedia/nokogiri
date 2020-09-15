![PHP Composer](https://github.com/olamedia/nokogiri/workflows/PHP%20Composer/badge.svg?branch=master)

> Attention: New version can break compatibility, in that case use previous version under the v1.0 branch or tag which supports even php 5.4+

> \nokogiri class is left for compatibility

[In English](README.md) [На русском](README.RU.md)

HTML parser
===========
This library is a fast HTML parser, which can work with invalid code (errors are ignored).<br />
Under the hood is used LibXML.<br />
As the input you can use HTML string in UTF-8 encoding or DOMDocument.<br />
For the querying elements CSS selectors are used, which are transformed to XPath expressions internally.<br />

Usage
=====
### Loading HTML
> HTML errors are ignored
* From HTML string `$saw = new \nokogiri($html);` `$saw = \nokogiri::fromHtml($html);`
* From DOM elements `$saw = new \nokogiri($dom);` `$saw = \nokogiri::fromDom($dom);`

### get($cssSelector)
$cssSelector elements have the following format:
`tagName[attribute=value]#elementId.className:pseudoSelector(expression)`
```php
$saw->get('div > a[rel=bookmark]')->toArray();
```
### toArray()
Returns underlying DOM structure as an array.<br />
Values are attributes, text content under `#text` key and child elements under numeric keys

### toXml()
Returns HTML string

### getDom() toDom()
Returns DOMDocument.
Given true as the first argument - can also return DOMNodeList or DOMElement

### Iteration over found elements
```php
foreach ($saw->get('#sidebar a.topic') as $link){
    var_dump($link['#text']);
}
```

Implemented selectors
=====================
* tag
* .class
* \#id
* \[attr\]
* \[attr=value\]
* :root
* :empty
* :first-child
* :last-child
* :first-of-type
* :last-of-type
* :only-of-type
* :nth-child(a)
* :nth-child(an+b)
* :nth-child(even/odd)

Requirements
============
* DOM
* libxml >=2.9.0
* PHP >= 7.3

License
=======
MIT

What's new
==========
### 2.0.0
* Minimal PHP version 7.3
* Minimal LibXML version 2.9.0
* Complete refactoring
* Partially changed behaviour, can break compatibility
* HTML loading behaviour changed
* Test coverage
* Fixed work of nth-child and other selectors
* Incorrect selectors now throw exceptions
* New selectors added

### 1.0.0
* First version, 2011
* Minimal PHP version 5.4
