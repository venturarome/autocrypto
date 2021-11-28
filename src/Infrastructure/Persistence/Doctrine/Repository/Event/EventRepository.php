<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Event;

use App\Domain\Model\Event\Event;
use App\Domain\Repository\Event\EventRepository as EventRepositoryI;
use App\Infrastructure\Persistence\Doctrine\Repository\RepositoryBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

//use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
//use Doctrine\Persistence\ManagerRegistry;


class EventRepository extends ServiceEntityRepository /*RepositoryBase*/ implements EventRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    public function findByName(string $name)
    {
        // TODO: Implement findByName() method.
    }
}