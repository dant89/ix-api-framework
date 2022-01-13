<?php

namespace App\Exception;

class UnsupportedMediaTypeErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 415;
        $this->detail = 'The media format your client is using is not supported by the server. The expected media ' .
            'format for requests is UTF8 JSON.';
        $this->title = 'Unsupported Media Type Error';
        $this->type = static::BASE_URL . 'unsupported-media-type';
    }
}
