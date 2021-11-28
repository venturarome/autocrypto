<?php

namespace App\Domain\Model\Event;

use App\Domain\Event\ThrowableEvent;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


class Event
{
    // TODO change to Enum when PHP8.1 is available!
    public const HANDLER_STATUS_OK = 'ok';
    public const HANDLER_STATUS_SKIPPED = 'skipped';
    public const HANDLER_STATUS_FAILED = 'failed';
    public const HANDLER_STATUS_NONE = 'none';

    protected int $id;
    protected string $uuid;
    protected string $name;
    protected string $entity_uuid;
    protected array $content;
    protected DateTimeImmutable $thrown_at;
    protected string $handler_status;   // TODO change to Enum when PHP8.1 is available!
    protected DateTimeImmutable $processed_at;


    public static function createFrom(ThrowableEvent $event): self
    {
        return new self($event->getName(), $event->getEntityUuid(), $event->getContent(), $event->getThrownAt());
    }

    private function __construct(string $name, string $entity_uuid, array $content, DateTimeImmutable $thrown_at)
    {
        $this->validateName($name);

        $this->uuid = Uuid::uuid6();
        $this->name = $name;
        $this->entity_uuid = $entity_uuid;
        $this->content = $content;
        $this->thrown_at = $thrown_at;
    }


    private function validateName(string $name): void
    {
        // Event naming strategy: <context>.<entity>.<action>
        //                        account.account.created
        //                        account.balance.updated
        $parts = explode('.', $name);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException(
                "Event 'name' failed validation. It must have exactly 3 keys ('<context>.<entity>.<action>').");
        }

        // TODO añadir mas validaciones??
    }
}