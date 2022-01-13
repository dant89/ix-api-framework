<?php

namespace App\Exception;

class ParseErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 400;
        $this->detail = 'The request payload could not be parsed. This may mean that the client is sending invalid ' .
            'JSON or that the request has been corrupted. Please check the syntax of your request, and ensure that ' .
            'your `Content-Type` header is `application/json`.';
        $this->title = 'Parse Error';
        $this->type = static::BASE_URL . 'parse-error';
    }
}
