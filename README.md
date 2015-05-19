HTML parser<br />Парсер HTML
===========
Данная библиотека - это быстрый парсер html кода, который способен работать с невалидным кодом.<br />
На вход необходимо подавать документ в кодировке UTF-8 или DomDocument.<br />
Для поиска элементов используются css-селекторы, которые преобразуются внутри в xpath выражение.<br />
Полученное xpath выражение кешируется, если в методе get не был выставлен в false второй аргумент (стоит отключать кеширование только в случае динамической генерации css выражений).<br />
В возвращаемых через ->toArray() массивах находятся аттрибуты, текст под ключом #text и вложенные элементы под числовыми ключами.<br />
Альтернативные методы: ->toXml() возвращает HTML-строку, ->getDom() возвращает DOMDocument<br />


Basic usage<br />Примеры использования
===================================
```php
<?php
$html = gzdecode(file_get_contents('http://habrahabr.ru/'));

$saw = new nokogiri($html);
var_dump($saw->get('a.habracut')->toArray());
var_dump($saw->get('ul.panel-nav-top li.current')->toArray());
var_dump($saw->get('#sidebar dl.air-comment a.topic')->toArray());
var_dump($saw->get('a[rel=bookmark]')->toArray());

foreach ($saw->get('#sidebar a.topic') as $link){
    var_dump($link['#text']);
}
```

HTML errors will be ignored.
Creating from HTML string: `nokogiri::fromHtml($htmlString)` or `new nokogiri($htmlString)`
Creating from DomDocument: `nokogiri::fromDom($dom)`

Ошибки html игнорируются.
Создание из строки HTML: nokogiri::fromHtml($htmlString); или new nokogiri($htmlString);
Создание из DomDocument: nokogiri::fromDom($dom);


Implemented css selectors<br />Реализованные селекторы
=========================
* tag
* .class
* \#id
* \[attr\]
* \[attr=value\]
* :first-child
* :last-child
* :nth-child(a)
* :nth-child(an+b)
* :nth-child(even/odd)


Requirements<br />Требования
============
DOM
libxml
PHP

Links<br />Ссылки
============
Статьи на хабре:

* <a href="http://habrahabr.ru/blogs/php/110112/">Нокогири: парсинг HTML в одну строку</a>
* <a href="http://habrahabr.ru/blogs/php/114323/">Сравнение библиотек для парсинга</a>
