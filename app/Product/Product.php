<?php

namespace App\Product;

use Carbon\Carbon;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Product implements jsonSerializable
{
    private int $id;
    private string $name;
    private int $amount;
    private Carbon $createdAt;
    private ?Carbon $lastUpdatedAt;

    public function __construct(
        int     $id,
        string  $name,
        int     $amount,
        string  $createdAt = null,
        ?string $lastUpdatedAt = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->createdAt = $createdAt ? Carbon::parse($createdAt) : Carbon::now('UTC');
        $this->lastUpdatedAt = $lastUpdatedAt ? Carbon::parse($lastUpdatedAt) : null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getLastUpdatedAt(): ?Carbon
    {
        return $this->lastUpdatedAt;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function setLastUpdatedAt(Carbon $lastUpdatedAt): void
    {
        $this->lastUpdatedAt = $lastUpdatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt,
            'lastUpdatedAt' => $this->lastUpdatedAt,
        ];
    }
}