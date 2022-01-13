<?php

namespace App\Exception;

class NotAcceptableErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 406;
        $this->detail = 'The server cannot produce a representation of the requested resource that meets your ' .
            'client\'s requirements. This is dependant on the negotiation headers supplied by your client';
        $this->title = 'Not Acceptable Error';
        $this->type = static::BASE_URL . 'not-acceptable';
    }
}
