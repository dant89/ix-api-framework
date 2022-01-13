<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SignatureExpiredErrorException extends AuthenticationException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 401;
        $this->detail = 'The authentication has failed because the signature in your token has expired. Please ' .
            'refresh your token and try again. If your refresh token has expired, you will need to reauthenticate.';
        $this->title = 'Signature Expired Error';
        $this->type = static::BASE_URL . 'signature-expired';
    }
}
