<?php

namespace App\Exception;

class UnableToFulfillErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct(string $title = '', string $detail = '')
    {
        parent::__construct();
        $this->code = 400;
        $this->detail = $detail ?: 'The server cannot give you what you need.';
        $this->title = $title ?: 'Unable To Fulfill Error';
        $this->type = static::BASE_URL . 'unable-to-fulfill';
    }
}
