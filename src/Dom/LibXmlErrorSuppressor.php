<?php
declare(strict_types=1);

namespace Nokogiri\Dom;

use Nokogiri\Dom\Interfaces\ErrorSuppressorInterface;

final class LibXmlErrorSuppressor implements ErrorSuppressorInterface
{
    private $errors;

    public function finish()
    {
        $this->errors = \libxml_get_errors();
        \libxml_clear_errors();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function start()
    {
        \libxml_use_internal_errors(true);
        $this->errors = null;
    }
}
