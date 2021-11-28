<?php

namespace App\Domain\Model\Shared\DateTracker;

use DateTimeImmutable;
use DateTimeInterface;

class DateTracker
{
    protected DateTimeInterface $created_at;
    protected DateTimeInterface $updated_at;
    protected DateTimeInterface $deleted_at;


    public static function create(): self
    {
        return new self();
    }

    private function __construct()
    {
        $now = new DateTimeImmutable();
        $this->created_at = $now;
        $this->updated_at = $now;
    }

    public function update(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    public function delete(): void
    {
        $now = new DateTimeImmutable();
        $this->updated_at = $now;
        $this->deleted_at = $now;
    }


    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getDeletedAt(): DateTimeImmutable
    {
        return $this->deleted_at;
    }


    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}