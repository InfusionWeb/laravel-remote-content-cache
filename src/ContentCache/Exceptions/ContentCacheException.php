<?php

namespace InfusionWeb\Laravel\ContentCache\Exceptions;

class ContentCacheException extends \Exception
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
