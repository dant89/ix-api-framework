<?php

namespace App\Security\Entity;

class Company
{
    protected string $id;
    protected ?string $name;

    public static function create(string $id, ?string $name = null)
    {
        return ((new static())->setId($id)->setName($name));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Company
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Company
    {
        $this->name = $name;
        return $this;
    }
}
