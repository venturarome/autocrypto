<?php

namespace App\Domain\Event;

use DateTimeImmutable;

abstract class ThrowableEvent
{
    protected string $entity_uuid;
    protected array $content;
    protected DateTimeImmutable $thrown_at;

    protected function __construct(string $entity_uuid, array $content)
    {
        $this->entity_uuid = $entity_uuid;
        $this->content = $content;
        $this->thrown_at = new DateTimeImmutable();
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getEntityUuid(): string
    {
        return $this->entity_uuid;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getThrownAt(): DateTimeImmutable
    {
        return $this->thrown_at;
    }
}