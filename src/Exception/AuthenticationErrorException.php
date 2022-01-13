<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationErrorException extends AuthenticationException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->detail = 'The authentication failed because incorrect authentication credentials were provided. ' .
            'Please check your credentials and try again.If the problem persists, please contact support.';
        $this->code = 401;
        $this->title = 'Authentication Credentials Invalid';
        $this->type = static::BASE_URL . 'authentication-error';
    }
}
