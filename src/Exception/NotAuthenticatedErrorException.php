<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NotAuthenticatedErrorException extends AuthenticationException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 401;
        $this->detail = 'The endpoint you called requires the client to have authenticated against the server, but ' .
            'your request did not include the resulting authorization. Please ensure that your request\'s ' .
            '`Authorization` header is present and includes the access_token returned by the `/auth/token` or ' .
            '`/auth/refresh` endpoint.';
        $this->title = 'Not Authenticated Error';
        $this->type = static::BASE_URL . 'not-authenticated';
    }
}
