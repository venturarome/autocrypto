<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class RepositoryBase extends ServiceEntityRepository
{
    // TODO funcionará? En ningún momento le estamos pasando ManagerRegistry!!! Si no funciona, llvar este parametro al constructor de los hijos.
    public function __construct(ManagerRegistry $registry, string $class)
    {
        parent::__construct($registry, $class);
    }

    public function save($entity): void
    {
        $this->_em->persist($entity);
    }
}