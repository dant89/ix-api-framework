<?php

namespace App\Exception\Internal;

use App\Exception\HelperExceptionTrait;
use App\Exception\ReportableExceptionInterface;

class DebugServerErrorException extends \RuntimeException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct(string $message)
    {
        parent::__construct($message);
        $this->code = 500;
        $this->detail = $message;
        $this->title = 'Internal Debug - Server Error';
        $this->type = static::BASE_URL . 'internal-debug-server-error';
    }
}
