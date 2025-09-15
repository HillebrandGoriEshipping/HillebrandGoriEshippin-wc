<?php

namespace HGeS\Dto;

class PackageDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $nb = null,
        public ?float $weight = null,
        public ?float $length = null,
        public ?float $width = null,
        public ?float $height = null,
        public ?string $containerType = null,
        public ?int $itemNumber = null,
        public ?array $weightDefinition = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNb(): int
    {
        return $this->nb;
    }

    public function setNb(int $nb): void
    {
        $this->nb = $nb;
    }
    
    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }
    
    public function getLength(): float
    {
        return $this->length;
    }
    
    public function setLength(float $length): void
    {
        $this->length = $length;
    }

    public function getWidth(): float
    {
        return $this->width;
    }
    
    public function setWidth(float $width): void
    {
        $this->width = $width;
    }
    
    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function getContainerType(): string
    {
        return $this->containerType;
    }

    public function setContainerType(string $containerType): void
    {
        $this->containerType = $containerType;
    }

    public function getItemNumber(): int
    {
        return $this->itemNumber;
    }

    public function setItemNumber(int $itemNumber): void
    {
        $this->itemNumber = $itemNumber;
    }

    public function getWeightDefinition(): ?array
    {
        return $this->weightDefinition;
    }

    public function setWeightDefinition(?array $weightDefinition): void
    {
        $this->weightDefinition = $weightDefinition;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nb' => $this->getNb(),
            'weight' => $this->getWeight(),
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'containerType' => $this->getContainerType(),
            'itemNumber' => $this->getItemNumber(),
            'weightDefinition' => $this->getWeightDefinition(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $package = new self();
        $package->setId($data['id'] ?? 0);
        $package->setNb(1);
        $package->setWeight($data['weight'] ?? 0.0);
        $package->setLength($data['length'] ?? 0.0);
        $package->setWidth($data['width'] ?? 0.0);
        $package->setHeight($data['height'] ?? 0.0);
        $package->setContainerType($data['containerType'] ?? '');
        $package->setItemNumber($data['itemNumber'] ?? 0);
        $package->setWeightDefinition($data['weightDefinition'] ?? []);

        return $package;
    }

    public function sanitize(): self
    {
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT) ?: 0;
        $this->nb = filter_var($this->nb, FILTER_VALIDATE_INT) ?: 0;
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT) ?: 0.0;
        $this->length = filter_var($this->length, FILTER_VALIDATE_FLOAT) ?: 0.0;
        $this->width = filter_var($this->width, FILTER_VALIDATE_FLOAT) ?: 0.0;
        $this->height = filter_var($this->height, FILTER_VALIDATE_FLOAT) ?: 0.0;
        $this->containerType = \sanitize_text_field($this->containerType);
        $this->itemNumber = filter_var($this->itemNumber, FILTER_VALIDATE_INT) ?: 0;
        if (is_array($this->weightDefinition)) {
            $this->weightDefinition = array_map(function ($value) {
                return filter_var($value, FILTER_VALIDATE_FLOAT) ?: 0.0;
            }, $this->weightDefinition);
        } else {
            $this->weightDefinition = [];
        }

        return $this;
    }
}
