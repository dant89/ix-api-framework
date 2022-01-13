<?php

namespace App\Exception;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class MethodNotAllowedErrorException extends MethodNotAllowedException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct(array $allowedMethods = [])
    {
        parent::__construct($allowedMethods);
        $this->code = 405;
        $this->detail = 'The HTTP verb you have used is not supported for this endpoint. Currently supported verbs ' .
            'are `GET`, `POST`, `PUT`, `PATCH` and `DELETE`. Some verbs may not be supported for all endpoints.';
        $this->title = 'Method Not Allowed Error';
        $this->type = static::BASE_URL . 'method-not-allowed';
    }
}
