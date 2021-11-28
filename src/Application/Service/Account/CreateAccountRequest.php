<?php

namespace App\Application\Service\Account;


class CreateAccountRequest
{
    private string $api_key;
    private string $secret_key;

    public function __construct(string $api_key, string $secret_key)
    {
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function getSecretKey(): string
    {
        return $this->secret_key;
    }
}