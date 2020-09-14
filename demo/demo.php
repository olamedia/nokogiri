<?php
declare(strict_types=1);

require_once '../vendor/autoload.php';

$html = \file_get_contents('test.html');

$saw = new \nokogiri($html);

var_dump($saw->get('div.first-half')->toText('|')); // '1|3'

var_dump($saw->get('body > span.first-half')->toTextArray()); // ['2', '4']

var_dump($saw->get('#element6')->toArray()); // [['id' => 'element6', '#text' => ['6']]]

foreach ($saw->get('div.first-half') as $link){
    var_dump($link['#text']); // ['1'] ['3']
}
