<?php

namespace HGeS\Dto;

use HGeS\Utils\RateHelper;

class RateDto
{
    private $metaData = [];

    public function __construct(
        private ?string $checksum = null,
        private ?string $serviceName = null,
        private ?array $prices = [],
        private ?string $carrier = null,
        private ?string $serviceCode = null,
        private ?string $pickupDate = null,
        private ?string $pickupTime = null,
        private ?string $deliveryDate = null,
        private ?string $deliveryTime = null,
        private ?bool $saturdayDelivery = null,
        private ?bool $guaranteedDelay = null,
        private ?string $deliveryMode = null,
        private ?string $coast = null,
        private ?string $firstPickupDelivery = null,
        private ?array $requiredAttachments = null,
        private ?array $packages = []
    ) {
    }

    // getters and setters
    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(?string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function getPrices(): ?array
    {
        return $this->prices;
    }

    public function setPrices(?array $prices): void
    {
        $this->prices = $prices;
    }

    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    public function setCarrier(?string $carrier): void
    {
        $this->carrier = $carrier;
    }

    public function getServiceCode(): ?string
    {
        return $this->serviceCode;
    }

    public function setServiceCode(?string $serviceCode): void
    {
        $this->serviceCode = $serviceCode;
    }

    public function getPickupDate(): ?string
    {
        return $this->pickupDate;
    }

    public function setPickupDate(?string $pickupDate): void
    {
        $this->pickupDate = $pickupDate;
    }

    public function getPickupTime(): ?string
    {
        return $this->pickupTime;
    }

    public function setPickupTime(?string $pickupTime): void
    {
        $this->pickupTime = $pickupTime;
    }

    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?string $deliveryDate): void
    {
        $this->deliveryDate = $deliveryDate;
    }

    public function getDeliveryTime(): ?string
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(?string $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime;
    }

    public function isSaturdayDelivery(): ?bool
    {
        return $this->saturdayDelivery;
    }

    public function setSaturdayDelivery(?bool $saturdayDelivery): void
    {
        $this->saturdayDelivery = $saturdayDelivery;
    }

    public function isGuaranteedDelay(): ?bool
    {
        return $this->guaranteedDelay;
    }

    public function setGuaranteedDelay(?bool $guaranteedDelay): void
    {
        $this->guaranteedDelay = $guaranteedDelay;
    }

    public function getDeliveryMode(): ?string
    {
        return $this->deliveryMode;
    }

    public function setDeliveryMode(?string $deliveryMode): void
    {
        $this->deliveryMode = $deliveryMode;
    }

    public function getCoast(): ?string
    {
        return $this->coast;
    }

    public function setCoast(?string $coast): void
    {
        $this->coast = $coast;
    }

    public function getFirstPickupDelivery(): ?string
    {
        return $this->firstPickupDelivery;
    }

    public function setFirstPickupDelivery(?string $firstPickupDelivery): void
    {
        $this->firstPickupDelivery = $firstPickupDelivery;
    }

    public function getRequiredAttachments(): ?array
    {
        return $this->requiredAttachments;
    }

    public function setRequiredAttachments(?array $requiredAttachments): void
    {
        $this->requiredAttachments = $requiredAttachments;
    }

    public function getPackages(): ?array
    {
        return $this->packages;
    }

    public function setPackages(?array $packages): void
    {
        $this->packages = $packages;
    }

    public function getMetaData($key): mixed
    {
        return $this->metaData[$key];
    }

    public function addMetaData(string $key, mixed $value): void
    {
        $this->metaData[$key] = $value;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getChecksum(),
            'checksum' => $this->getChecksum(),
            'label' => $this->getServiceName(),
            'serviceName' => $this->getServiceName(),
            'prices' => $this->getPrices(),
            'cost' => RateHelper::calculateTotal($this),
            'carrier' => $this->getCarrier(),
            'serviceCode' => $this->getServiceCode(),
            'pickupDate' => $this->getPickupDate(),
            'pickupTime' => $this->getPickupTime(),
            'deliveryDate' => $this->getDeliveryDate(),
            'deliveryTime' => $this->getDeliveryTime(),
            'saturdayDelivery' => $this->isSaturdayDelivery(),
            'guaranteedDelay' => $this->isGuaranteedDelay(),
            'deliveryMode' => $this->getDeliveryMode(),
            'coast' => $this->getCoast(),
            'firstPickupDelivery' => $this->getFirstPickupDelivery(),
            'requiredAttachments' => $this->getRequiredAttachments(),
            'packages' => $this->getPackages(),
            'meta_data' => $this->metaData,
        ];
    }

   
}