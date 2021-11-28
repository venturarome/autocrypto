<?php

namespace App\Infrastructure\Provider\Kaiko;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KaikoApiClient
{
    protected const BASE_URI = "https://reference-data-api.kaiko.io";    // https://docs.kaiko.com/
    protected const VERSION = "v1";

    protected HttpClientInterface $http_client; // https://symfony.com/doc/current/http_client.html
                            // Investigar streams: https://symfony.com/doc/current/http_client.html#http-client-streaming-responses

    // TODO SERIALIZER MUST BE IN AN APPLICATION SERVICE!!!
//    protected $serializer;  // https://symfony.com/doc/current/components/serializer.html


//    public function __construct(string $api_key, string $secret)
    public function __construct()
    {
//        $this->api_key = $api_key;
//        $this->secret = $secret;

        //$this->http_client = HttpClient::create();
        $this->http_client = HttpClient::createForBaseUri(self::BASE_URI);

//        $this->serializer = new Serializer([new ArrayDenormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);

    }

    // Public endpoint example
    public function getAssets(): array
    {
        return $this->getPublicEndpoint("/assets");
    }


    /**
     * Construction of URLs for public endpoints.
     */
    private function getPublicEndpoint(string $path_end): array
    {
        $path = "/" . self::VERSION . $path_end;
        $url = self::BASE_URI . $path;

        $response = $this->http_client->request('GET', $url);

        if (($status = $response->getStatusCode()) !== 200) {
            throw new \HttpResponseException(
                "Response to '$path_end' returned status $status. Content: {$response->getContent()}");
        }

        return $response->toArray(true);
    }

}