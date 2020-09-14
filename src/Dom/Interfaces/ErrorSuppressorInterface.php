<?php
declare(strict_types=1);

namespace Nokogiri\Dom\Interfaces;

interface ErrorSuppressorInterface
{
    public function finish();

    public function getErrors();

    public function start();
}
