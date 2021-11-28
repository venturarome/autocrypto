<?php

namespace App\Domain\Factory\Account;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Shared\DateTracker\DateTracker;
use App\Domain\Repository\Account\AccountRepository;
use App\Domain\Factory\ReflectionFactory;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class AccountFactory extends ReflectionFactory
{
    private AccountRepository $account_repo;

    public function __construct(AccountRepository $account_repo)
    {
        $this->account_repo = $account_repo;
    }

    public function create(string $api_key, string $secret_key): Account
    {
        $account = $this->instantiateObject(Account::class);


        $parameters = [
            'uuid' => Uuid::uuid6(),
            'reference' => $this->generateUniqueReference(),
            'status' => Account::STATUS_ACTIVE,
            'api_key' => $api_key,
            'secret_key' => $secret_key,
            'date_tracker' => DateTracker::create()
//            'timestamps' => new Timestamps($dt = new DateTimeImmutable(), $dt),
//            'created_at' => $dt = new DateTimeImmutable(),
//            'updated_at' => $dt,
        ];

        $this->fillObject($account, $parameters);

        return $account;
    }

    private function generateUniqueReference(): string
    {
        $length = 8;
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";

        do {
            $code = "";
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars)-1)];
            }
        } while ($this->account_repo->findByReference($code));

        return $code;
    }
}