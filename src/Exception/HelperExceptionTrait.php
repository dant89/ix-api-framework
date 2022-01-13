<?php

namespace App\Exception;

use App\Exception\Internal\DebugServerErrorException;

trait HelperExceptionTrait
{
    protected string $detail;
    protected string $instance;
    protected array $properties = [];
    protected string $title;
    protected string $type;
    protected array $formattedMessage;

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;
        return $this;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function setInstance(string $instance): self
    {
        $this->instance = $instance;
        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFormattedMessage(): array
    {
        $reportablePropertyFields = ['name', 'reason', 'available_ranges', 'value'];

        $message = [
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'status' => $this->getCode(),
            'detail' => $this->getDetail(),
            'instance' => $this->getInstance()
        ];

        if ($this instanceof DebugServerErrorException) {
            $message['file'] = $this->getFile();
            $message['line'] = $this->getLine();
            $message['trace'] = $this->getTrace();
        }

        $properties = $this->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                $propertyReport = [];
                foreach ($property as $key => $value) {
                    if (in_array($key, $reportablePropertyFields)) {
                        $propertyReport[$key] = $value;
                    }
                }
                $message['properties'][] = $propertyReport;
            }
        }

        return $message;
    }
}
