<?php

namespace App\Exception;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationErrorException extends ValidationException implements ReportableExceptionInterface
{
    use HelperExceptionTrait;

    public function __construct(string $title = '', string $detail = '', array $properties = [])
    {
        $code = 400;
        parent::__construct($title, $code);
        $this->code = $code;
        $this->detail = $detail ?: 'Some fields in the request could not be validated correctly.';
        $this->properties = $properties;
        $this->title = $title ?: 'Validation Error';
        $this->type = static::BASE_URL . 'validation-error';
    }

    /**
     * Map from ValidatorInterface::validate format violations to a ValidationErrorException formatted for IX-API
     */
    public static function throwFromViolations(
        ConstraintViolationListInterface $apiPlatformViolations,
        string $title = '',
        string $details = ''
    ) {
        $ixapiViolations = [];
        /** @var ConstraintViolation $violation */
        foreach ($apiPlatformViolations as $violation) {
            $ixapiViolations[] = [
                'name' => $violation->getPropertyPath(),
                'reason' => $violation->getMessage(),
                'value' => $violation->getInvalidValue(),
            ];
        }
        throw new self($title, $details, $ixapiViolations);
    }
}
