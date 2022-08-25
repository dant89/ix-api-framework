<?php

namespace App\ProductOffering\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Security\Role\RoleFactory;

/**
 * @ApiResource(
 *     itemOperations={
 *         "get"={
 *             "access_control_roles"={RoleFactory::GET_PRODUCT_OFFERING}
 *          }
 *     },
 *     collectionOperations={
 *         "get"={
 *             "access_control_roles"={RoleFactory::GET_PRODUCT_OFFERINGS}
 *          }
 *     },
 * )
 */
class ProductOffering
{
    /**
     * @ApiProperty(
     *     description="ID",
     *     identifier=true,
     * )
     * @Assert\Type(type="string")
     * @Assert\Length(min="36", max="36")
     */
    protected string $id;

    /**
     * @ApiProperty(
     *     description="Name of the product"
     * )
     * @Assert\NotBlank()
     */
    protected string $name;

     /**
     * @ApiProperty(
     *     description="Display name"
     * )
     * @Assert\NotBlank()
     */
    protected string $displayName;

    /**
     * @ApiProperty(
     *     description="Handover metro area"
     * )
     */
    protected ?string $handoverMetroArea = null;

    /**
     * @ApiProperty(
     *     description="Handover metro area network"
     * )
     */
    protected ?string $handoverMetroAreaNetwork = null;

    /**
     * @ApiProperty(
     *     description="Service metro area"
     * )
     */
    protected ?string $serviceMetroArea = null;

    /**
     * @ApiProperty(
     *     description="Service metro area network"
     * )
     */
    protected ?string $serviceMetroAreaNetwork = null;

    /**
     * @ApiProperty(
     *     description="Product offering type"
     * )
     * @Assert\NotBlank()
     */
    protected string $type;

    /**
     * @ApiProperty(
     *     description="IX-API resource type"
     * )
     * @Assert\NotBlank()
     */
    protected string $resourceType;

    /**
     * @ApiProperty(
     *     description="Minimum bandwidth"
     * )
     * @Assert\NotBlank()
     */
    protected int $bandwidthMin = 50;

    /**
     * @ApiProperty(
     *     description="Maximum bandwidth"
     * )
     */
    protected ?int $bandwidthMax = null;

    /**
     * @ApiProperty(
     *     description="Provider VLANs"
     * )
     */
    protected string $providerVlans = 'single';

    /**
     * @ApiProperty(
     *     description="Downgrade allowed"
     * )
     */
    protected bool $downgradeAllowed = true;

    /**
     * @ApiProperty(
     *     description="Upgrade allowed"
     * )
     */
    protected bool $upgradeAllowed = true;

    /**
     * @ApiProperty(
     *     description="Physical port speed"
     * )
     */
    protected ?int $physicalPortSpeed = null;

    /**
     * @ApiProperty(
     *     description="Service provider"
     * )
     */
    protected string $serviceProvider = "IX";

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getHandoverMetroArea(): ?string
    {
        return $this->handoverMetroArea;
    }

    public function setHandoverMetroArea(string $metroArea): self
    {
        $this->handoverMetroArea = $metroArea;
        return $this;
    }

    public function getHandoverMetroAreaNetwork(): ?string
    {
        return $this->handoverMetroAreaNetwork;
    }

    public function setHandoverMetroAreaNetwork(string $metroAreaNetwork): self
    {
        $this->handoverMetroAreaNetwork = $metroAreaNetwork;
        return $this;
    }

    public function getServiceMetroArea(): ?string
    {
        return $this->serviceMetroArea;
    }

    public function setServiceMetroArea(string $metroArea): self
    {
        $this->serviceMetroArea = $metroArea;
        return $this;
    }

    public function getServiceMetroAreaNetwork(): ?string
    {
        return $this->serviceMetroAreaNetwork;
    }

    public function setServiceMetroAreaNetwork(string $metroAreaNetwork): self
    {
        $this->serviceMetroAreaNetwork = $metroAreaNetwork;
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

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType): self
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    public function getBandwidthMin(): int
    {
        return $this->bandwidthMin;
    }

    public function setBandwidthMin(int $minBandwidth): self
    {
        $this->bandwidthMin = $minBandwidth;
        return $this;
    }

    public function getBandwidthMax(): ?int
    {
        return $this->bandwidthMax;
    }

    public function setBandwidthMax(?int $maxBandwidth): self
    {
        $this->bandwidthMax = $maxBandwidth;
        return $this;
    }

    public function setProviderVlans(string $providerVlans): self
    {
        $this->providerVlans = $providerVlans;
        return $this;
    }

    public function getProviderVlans(): string
    {
        return $this->providerVlans;
    }

    public function setUpgradeAllowed(bool $upgradeAllowed): self
    {
        $this->upgradeAllowed = $upgradeAllowed;
        return $this;
    }

    public function isUpgradeAllowed(): bool
    {
        return $this->upgradeAllowed;
    }

    public function setDowngradeAllowed(bool $downgradeAllowed): self
    {
        $this->downgradeAllowed = $downgradeAllowed;
        return $this;
    }

    public function isDowngradeAllowed(): bool
    {
        return $this->downgradeAllowed;
    }

    public function getPhysicalPortSpeed(): ?int
    {
        return $this->physicalPortSpeed;
    }

    public function setPhysicalPortSpeed(?int $physicalPortSpeed): self
    {
        $this->physicalPortSpeed = $physicalPortSpeed;
        return $this;
    }

    public function getServiceProvider(): string
    {
        return $this->serviceProvider;
    }

    public function setServiceProvider(string $serviceProvider): self
    {
        $this->serviceProvider = $serviceProvider;
        return $this;
    }
}
