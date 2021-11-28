<?php

namespace App\Infrastructure\Provider\Kraken;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KrakenApiClient
{
    protected const BASE_URI = "https://api.kraken.com";    // https://docs.kraken.com/rest/
    protected const VERSION = 0;

    protected string $api_key;
    protected string $secret;
    protected HttpClientInterface $http_client; // https://symfony.com/doc/current/http_client.html
                            // Investigar streams: https://symfony.com/doc/current/http_client.html#http-client-streaming-responses

    // TODO SERIALIZER MUST BE IN AN APPLICATION SERVICE!!!
    protected $serializer;  // https://symfony.com/doc/current/components/serializer.html


//    public function __construct(string $api_key, string $secret)
    public function __construct()
    {
//        $this->api_key = $api_key;
//        $this->secret = $secret;

        //$this->http_client = HttpClient::create();
        $this->http_client = HttpClient::createForBaseUri(self::BASE_URI);

        $this->serializer = new Serializer([new ArrayDenormalizer(), new ObjectNormalizer()], [new JsonEncoder()]);

    }

    public function configureKeys(string $api_key, string $secret): void
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
    }

    // Public endpoint example
    public function getAssetInfo(array $data = []): array
    {
        // string asset [optional] Comma delimited list of assets to get info on. Ex "asset=XBT,ETH"
        // string aclass [optional] Asset class. Ex "aclass=currency" (default: currency)

        // TODO consistency checks on $data.
        return $this->getPublicEndpoint("Assets", $data);
    }

    public function getTradableAssetPairs(array $data = []): array
    {
        // string asset [optional] Comma delimited list of assets to get info on. Ex "asset=XBT,ETH"
        // string aclass [optional] Asset class. Ex "aclass=currency" (default: currency)

        // TODO consistency checks on $data.
        return $this->getPublicEndpoint("AssetPairs", $data);
    }

    public function GetOHLCData(array $data): array
    {
        // string $pair [required] Asset pair to get data for. Ex "XBTUSD"
        // int $interval [optional] Time frame interval in minutes (enum: 1, 5, 15, 30, 60, 240, 2550, 10080, 21600). Default 60.
        // int $since [optional] Return committed data since given id (unix seconds). Ex 1548111600.

        // TODO consistency checks on $data.

        return $this->getPublicEndpoint("OHLC". $data);

    }

    // Private endpoint example
    public function getAccountBalance(): array
    {
        $path = "/" . self::VERSION . "/private/Balance";
        $url = self::BASE_URI . $path;

        $nonce = $this->generateNonce();
        $post_data = [];

        $response = $this->http_client->request('POST', $url, [
            'headers' => [
                'API-Key' => $this->api_key,
                'API-Sign' => $this->generateApiSign($path, $nonce, $post_data)
            ],
            'body' => [
                'nonce' => $nonce
            ]
        ]);

        if (($status = $response->getStatusCode()) !== 200) {
            throw new \HttpResponseException(
                "Response to '$path' returned status $status. Content: {$response->getContent()}");
        }

        return $response->toArray(true);
    }






    /**
     * Construction of URLs for public endpoints.
     */
    private function getPublicEndpoint(string $path_end, array $data): array
    {
        $path = "/" . self::VERSION . "/public/" . $path_end;
        $query = $data ? "?" . http_build_query($data, '', '&') : '';
        $url = self::BASE_URI . $path . $query;

        $response = $this->http_client->request('GET', $url);

        if (($status = $response->getStatusCode()) !== 200) {
            throw new \HttpResponseException(
                "Response to '/public/$path_end' returned status $status. Content: {$response->getContent()}");
        }

        return $response->toArray(true);
    }

    /**
     * Nonce is a mandatory parameter on private endpoints. It must be ever-increasing, so we use Unix timestamps.
     */
    private function generateNonce(): int
    {
        $ms_s = explode(' ', microtime());
        return (int)($ms_s[1].substr($ms_s[0], 2, 3));
    }

    /**
     * Authentication method for private endpoints.
     */
    private function generateApiSign(string $path, int $nonce, array $data = []): string
    {
        $data['nonce'] = $nonce;

        return base64_encode(
            hash_hmac(
            'sha512',
            $path . hash('sha256', $nonce . http_build_query($data, '', '&'), true),
            base64_decode($this->secret),
            true
        ));
    }

}