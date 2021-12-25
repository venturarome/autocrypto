<?php

namespace App\Application\Service\Account;

use App\Domain\Event\Account\AccountCreated;
use App\Domain\Factory\Account\AccountFactory;
use App\Domain\Model\Event\Event;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LengthException;

class CreateAccountService
{
    private AccountFactory $account_factory;
    private EntityManagerInterface $entity_manager;


    public function __construct(
        AccountFactory $account_factory,
        EntityManagerInterface $entity_manager
    ) {
        $this->account_factory = $account_factory;
        $this->entity_manager = $entity_manager;
    }

    public function execute(CreateAccountRequest $request)
    {
        $api_key = $request->getApiKey();
        $secret_key = $request->getSecretKey();
        $this->validateKeys($api_key, $secret_key);

        $account = $this->account_factory->create($api_key, $secret_key);
        $event = Event::createFrom(AccountCreated::raise($account));

        try {
            $this->entity_manager->beginTransaction();  // suspend auto-commit
            $this->entity_manager->persist($account);
            $this->entity_manager->persist($event);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        } catch (Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }

    private function validateKeys(string $api_key, string $secret_key): void
    {
        if (strlen($api_key) !== 56) {          // TODO check that it is always this size
            throw new LengthException("api_key for Account is expected to have 56 characters.");
        }

        if (strlen($secret_key) !== 88) {      // TODO check that it is always this size
            throw new LengthException("secret_key for Account is expected to have 88 characters.");
        }
    }
}