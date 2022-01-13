<?php

namespace App\Product\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Security\Role\RoleFactory;

/**
 * @ApiResource(
 *     itemOperations={
 *         "get"={
 *             "access_control_roles"={RoleFactory::GET_PRODUCT}
 *          }
 *     },
 *     collectionOperations={
 *         "get"={
 *             "access_control_roles"={RoleFactory::GET_PRODUCTS}
 *          }
 *     }
 * )
 */
class Product {

    private ?string $id;
    private string $name;
    private string $type;
    private ?string $metroArea;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): Product
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Product
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Product
    {
        $this->type = $type;
        return $this;
    }

    public function getMetroArea(): ?string
    {
        return $this->metroArea;
    }

    public function setMetroArea(?string $metroArea): Product
    {
        $this->metroArea = $metroArea;
        return $this;
    }
}
