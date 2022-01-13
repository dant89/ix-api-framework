<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PermissionDeniedErrorException extends AuthenticationException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct()
    {
        parent::__construct();
        $this->code = 403;
        $this->detail = 'You do not have permission to access or modify the resource you requested. Please ensure ' .
            'that your request is correctly authenticated as a customer with access rights to this specific entity.';
        $this->title = 'Permission Denied Error';
        $this->type = static::BASE_URL . 'permission-denied';
    }
}
