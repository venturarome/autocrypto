<?php

namespace App\Domain\Model\Shared\DateTracker;

use DateTime;


class DateTracker
{
    protected DateTime $created_at;
    protected DateTime $updated_at;
    protected DateTime $deleted_at;


    public static function create(): self
    {
        return new self();
    }

    private function __construct()
    {
        $now = new DateTime();
        $this->created_at = $now;
        $this->updated_at = $now;
    }

    public function update(): void
    {
        $this->updated_at = new DateTime();
    }

    public function delete(): void
    {
        $now = new DateTime();
        $this->updated_at = $now;
        $this->deleted_at = $now;
    }


    public function getCreatedAt(): DateTime
    {
        return clone $this->created_at;
    }

    public function getUpdatedAt(): DateTime
    {
        return clone $this->updated_at;
    }

    public function getDeletedAt(): DateTime
    {
        return clone $this->deleted_at;
    }


    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}