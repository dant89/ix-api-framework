<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundErrorException extends NotFoundHttpException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct(string $message = "")
    {
        parent::__construct($message);
        $this->code = 404;
        $this->detail = 'You are trying to access a resource which does not exist. Either this resource has never ' .
            'existed, or it has been deleted, or we cannot reveal its existence to the authenticated user.';
        $this->title = 'Not Found Error';
        $this->type = static::BASE_URL . 'not-found';
    }
}
