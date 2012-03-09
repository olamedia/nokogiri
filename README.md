HTML parser | Парсер HTML
===========
Данная библиотека - это быстрый парсер html кода, который способен работать с невалидным кодом.
На вход необходимо подавать документ в кодировке UTF-8 или DomDocument.
Для поиска элементов используются css-селекторы, которые преобразуются внутри в xpath выражение.
Полученное xpath выражение кешируется, если в методе get не был выставлен в false второй аргумент (стоит отключать кеширование только в случае динамической генерации css выражений).
В возвращаемыхч через ->toArray() массивах находятся аттрибуты, текст под ключом #text и вложенные элементы под числовыми ключами.
Альтернативные методы: ->toXml() возвращает HTML-строку, ->getDom() возвращает DOMDocument


Basic usage | Примеры использования
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


Implemented css selectors | Реализованные селекторы
=========================
* tag
* .class
* \#id
* \[attr=value\]
* :first-child
* :last-child
* :nth-child(a)
* :nth-child(an+b)
* :nth-child(even/odd)


Requirements | Требования
============
DOM
libxml
PHP

Links | Ссылки
============
Статьи на хабре:

* <a href="http://habrahabr.ru/blogs/php/110112/">Нокогири: парсинг HTML в одну строку</a>
* <a href="http://habrahabr.ru/blogs/php/114323/">Сравнение библиотек для парсинга</a>
