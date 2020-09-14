<?php
declare(strict_types=1);

namespace Nokogiri\Dom;

final class DocumentFactory
{
    /**
     * @var \Nokogiri\Dom\Interfaces\ErrorSuppressorInterface
     */
    private $suppressor;

    public function __construct($suppressor)
    {
        $this->suppressor = $suppressor;
    }

    public function createFromDOMDocument($domDocument)
    {
        return new Document($this->suppressor, $domDocument);
    }

    public function createFromHtmlString($htmlString = '', $enforceUtf8 = null, $autoReload = null)
    {
        if ($enforceUtf8 === null) {
            $enforceUtf8 = false;
        }
        $document = new Document($this->suppressor);
        $document->loadHtml($htmlString, $enforceUtf8 ? 'UTF-8' : null, $autoReload);

        return $document;
    }
}
