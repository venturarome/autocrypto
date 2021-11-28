<?php

namespace App\Domain\Repository\Event;

interface EventRepository
{
    public function findByName(string $name);
}