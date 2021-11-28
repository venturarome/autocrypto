<?php


////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
///////////////////////////////  NOT IN USE  ///////////////////////////////
////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////



namespace App\Infrastructure\Persistence\Doctrine\EventSubscriber;


use App\Domain\Event\EventRepository;
use App\Domain\Event\EventBase;
use App\Domain\Shared\EventAware\EventAware;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;  // Comprobar que es esta! hay otra clase con el mismo nombre.
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DomainEventSubscriber implements EventSubscriber
{
    //private EventDispatcherInterface $event_dispatcher;
    // private ArrayCollection $entities;    // EntityCollection??

//    public function __construct(EventDispatcherInterface $event_dispatcher)
//    {
//         $this->event_dispatcher = $event_dispatcher;
//         $this->entities = new ArrayCollection();
//    }


    private EventRepository $event_repo;
    private EntityManagerInterface $entity_manager;
    private ArrayCollection $events;

    public function __construct(EventRepository $event_repo, EntityManagerInterface $entity_manager)
    {
        $this->event_repo = $event_repo;
        $this->entity_manager = $entity_manager;
        $this->events = new ArrayCollection();
    }

    // Events that this subscriber wants to listen to.
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::preFlush,
            Events::postFlush
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
//        $this->addEntity($args);
        $this->addEvents($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
//        $this->addEntity($args);
        $this->addEvents($args);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
//        $this->addEntity($args);
        $this->addEvents($args);
    }

//    private function addEntity(LifecycleEventArgs $args): void
//    {
//        $entity = $args->getEntity();
//        if ($entity instanceof EventAware) {
//            $this->entities->add($entity);
//        }
//    }
    private function addEvents(LifecycleEventArgs $args): void
    {
        return;
        $entity = $args->getEntity();
        if (!$entity instanceof EventAware) {
            return;
        }
        foreach ($entity->getEvents() as $event) {
            $this->events->add($event);
        }
    }

    // TODO yo creo que este evento sobraría....
//    public function preFlush(PreFlushEventArgs $args): void
//    {
//        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
//        foreach ($unitOfWork->getIdentityMap() as $class => $entities) {
//            if (!\in_array(EventAware::class, class_implements($class), true)) {
//                continue;
//            }
//            foreach ($entities as $entity) {
//                $this->entities->add($entity);
//            }
//        }
//    }
    public function preFlush(PreFlushEventArgs $args): void
    {
        return;
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        foreach ($unitOfWork->getIdentityMap() as $class => $entities) {
            if (!in_array(EventAware::class, class_implements($class), true)) {
                continue;
            }
            foreach ($entities as $entity) {
                foreach ($entity->getEvents() as $event) {
                    $this->events->add($event);
                }
            }
        }
    }

//    public function postFlush(PostFlushEventArgs $args): void
//    {
//        $events = new ArrayCollection();
//        foreach ($this->entities as $entity) {
//            foreach ($entity->getRecordedEvents() as $domainEvent) {
//                $events->add($domainEvent);
//            }
//            $entity->clearRecordedEvents();
//        }
//        /** @var Event $event */
//        foreach ($events as $event) {
//            $this->eventDispatcher->dispatch(\get_class($event), $event);
//        }
//    }
    public function postFlush(PostFlushEventArgs $args): void
    {
        return;
        $events = new ArrayCollection();
        foreach ($this->events as $entity) {
            foreach ($entity->getRecordedEvents() as $domainEvent) {
                $events->add($domainEvent);
            }
            $entity->clearRecordedEvents();
        }
        foreach ($this->events as $event) {
        /** @var EventBase $event */
            $this->event_repo->save($event);
        }
        $this->entity_manager->flush();
    }
}