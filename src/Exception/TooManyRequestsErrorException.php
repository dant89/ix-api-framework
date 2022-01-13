<?php

namespace App\Exception;

class TooManyRequestsErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 429;
        $this->detail = 'The server has received too many requests from your client in a short space of time. This ' .
            'may indicate an error in your client. If this message occurs in normal usage, please contact support ' .
            'to discuss a more appropriate threshold.';
        $this->title = 'Too Many Requests Error';
        $this->type = static::BASE_URL . 'too-many-requests';
    }
}
