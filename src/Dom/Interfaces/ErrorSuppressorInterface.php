<?php

namespace Nokogiri\Dom\Interfaces;

interface ErrorSuppressorInterface
{
    public function finish();

    public function getErrors();

    public function start();
}
