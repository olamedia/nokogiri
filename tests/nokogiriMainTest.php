<?php
require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'nokogiri.php';
ini_set('memory_limit', '128M');
date_default_timezone_set('Europe/Moscow');
chdir(__DIR__);


class nokogiriMainTest extends PHPUnit_Framework_TestCase{
    /** 
      * Проверка конструктора на правильные значения.
      * ToDo - проверить с DOM элементами
      * @dataProvider htmlSrcsProvider 
      */
	public function testConstruct($src){
        $saw = new nokogiri($src);
        $this->assertTrue($saw instanceof nokogiri);
        $this->assertFalse( is_null($src) xor empty($saw->toNodes()));
        unset ($saw);
    }
    
    /**
     * Проверка метода toArray(). TODO - сделать тщательнее, например, рекурсивно обойти возвращаемый массив.
     * @dataProvider htmlSrcsProvider 
     * @depends testConstruct
     */
	public function testToArray($src){
        $saw = new nokogiri($src);
        $this->assertFalse(empty($saw->toArray()));
        unset ($saw);   
    }
    
    /**
     * Проверка метода toDom()
     * @dataProvider htmlSrcsProvider 
     * @depends testConstruct
     */
	public function testToDom($src){
        $saw = new nokogiri($src);
        $doc = $saw->toDom();
        $this->assertTrue(
            $doc instanceof DOMDocument && 
            strlen($doc->textContent) > 10 && 
            ($doc->nodeType == XML_HTML_DOCUMENT_NODE || $doc->nodeType == XML_DOCUMENT_NODE)
        );
        unset ($saw);        
    }
    
    /**
     * Проверка выборки по селекторам + проверка корректности преобразования toArray().
     * @dataProvider htmlSrcsProvider 
     */
	public function testGet($src){
        $saw = new nokogiri($src);
        
        switch (str_replace('.html', '', basename($src))){
            case 'tiny':
                $arr = $saw->get('span#id1')->toArray();
                $this->assertTrue($arr[0]['id'] == "id1" && $arr[0]['#text'][0] == "sp1 id1 text");
                $arr = $saw->get('div div.inner')->toArray();
                $this->assertTrue(count($arr) == 3);
                break;
            case 'dwiki':
            case 'dwiki-1251':
                // print_r($saw->toArray()); die();
                $nodes = $saw->get('.dokuwiki div .pad li .li a.wikilink1')->toNodes();
                $this->assertTrue($nodes->length == 21);
                break;
            case 'mwiki':
                // print_r($saw->toArray()); die();
                $nodes = $saw->get('.mediawiki .mw-content-ltr li a.external')->toNodes();
                $this->assertTrue($nodes->length == 19);
                break;
            case 'habrs':
                // print_r($saw->toArray()); die();
                $nodes = $saw->get('.content_left .post blockquote code')->toNodes();
                $this->assertTrue($nodes->length == 20);
                break;
            case 'habri':
                // print_r($saw->toArray()); die();
                $nodes = $saw->get('.post')->toNodes();
                $this->assertTrue($nodes->length == 10);
                break;
            default:
                $nodes = $saw->get('div div')->toNodes();
                $this->assertTrue($nodes->length > 1);
                break;
        }
        unset ($saw);
    }
    
    
    /**
     * Проверка toText()
     * @dataProvider htmlSrcsProvider 
     * @depends      testGet
     */
	public function testToText($src){
        $saw = new nokogiri($src);
        $this->assertTrue( strlen($saw->toText()) > 20);
        unset ($saw);
    }
    
    
    
    
    
    
    
    /* Data providers */
    
    public function htmlSrcsProvider(){
        $files    = $this->_getHtmlFiles();
        foreach ($files as $param) 
            $out[] = array(file_get_contents($param));
        return $out;
    }
    
    /* Private methods */
    private function _getHtmlFiles(){
        $data_dir = __DIR__.DIRECTORY_SEPARATOR.'data';
        return glob($data_dir.DIRECTORY_SEPARATOR.'*html');
    }
    
}

