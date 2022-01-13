<?php

namespace App\Exception;

class ServerErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 500;
        $this->detail = 'An internal problem occurred on the server. This does not imply that the request was ' .
            'invalid. Please retry your request; if the problem persists please contact support.';
        $this->title = 'Server Error';
        $this->type = static::BASE_URL . 'server-error';
    }
}
