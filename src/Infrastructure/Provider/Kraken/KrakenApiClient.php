<?php

namespace App\Infrastructure\Provider\Kraken;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KrakenApiClient
{
    protected const BASE_URI = "https://api.kraken.com";    // https://docs.kraken.com/rest/
    protected const VERSION = 0;
    protected const COUNTER_LIMIT = 20;
    protected const COUNTER_DECAY_PER_S = 0.5;

    protected string $api_key;
    protected string $secret;
    protected HttpClientInterface $http_client; // https://symfony.com/doc/current/http_client.html
                            // Investigar streams: https://symfony.com/doc/current/http_client.html#http-client-streaming-responses
    protected float $last_call_timestamp;
    protected float $call_counter;


    public function __construct()
    {
        $this->http_client = HttpClient::createForBaseUri(self::BASE_URI);
        $this->last_call_timestamp = microtime(true);
        $this->call_counter = 0;
    }

    public function configureKeys(string $api_key, string $secret): void
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
    }

    //////////////////////
    // Public endpoints //
    //////////////////////

    /// MARKET DATA
    ///////////////
    public function getServerTime(): array
    {
        return $this->getPublicEndpoint("Time", []);
    }

    /**
     * string $data['asset'] - (optional) Comma delimited list of assets to get info on. Ex "asset=XBT,ETH"
     * string $data['aclass'] - (optional) Asset class. Ex "aclass=currency" (default: currency)
     */
    public function getAssetInfo(array $data = []): array
    {
        return $this->getPublicEndpoint("Assets", $data);
    }

    /**
     * string $data['asset'] - (optional) Comma delimited list of assets to get info on. Ex "asset=XBT,ETH"
     * string $data['aclass'] - (optional) Asset class. Ex "aclass=currency" (default: currency)
     */
    public function getTradableAssetPairs(array $data = []): array
    {
        return $this->getPublicEndpoint("AssetPairs", $data);
    }

    /**
     * string $data['pair'] - (required) Asset pair to get data for. Ex "pair=XBTEUR"
     */
    public function getTickerInformation(array $data = []): array
    {
        return $this->getPublicEndpoint("Ticker", $data);
    }

    /**
     * string $data['pair'] - (required) Asset pair to get data for. Ex "XBTUSD"
     * int $data['interval'] - (optional) Time frame interval in minutes (enum: 1, 5, 15, 30, 60, 240, 2550, 10080, 21600). Default 1.
     * int $data['since'] - (optional) Return committed data since given id (unix seconds). Ex 1548111600.
     */
    public function getOHLCData(array $data): array
    {
        $this->checkRequired($data, 'pair');
        return $this->getPublicEndpoint("OHLC", $data);
    }

    /**
     * string $data['pair'] - (required) Asset pair to get data for. Ex "XBTUSD"
     * int $data['count'] - (optional) maximum number of asks/bids (1 ... 500). Default 100
     */
    public function getOrderBook(array $data): array
    {
        return $this->getPublicEndpoint("Depth", $data);
    }

    /**
     * string $data['pair'] - (required) Asset pair to get data for. Ex "XBTUSD"
     * string $data['since'] - (optional) Return trade data since given timestamp
     */
    public function getRecentTrades(array $data): array
    {
        return $this->getPublicEndpoint("Trades", $data);
    }

    /**
     * string $data['pair'] - (required) Asset pair to get data for. Ex "XBTUSD"
     * string $data['since'] - (optional) Return trade data since given timestamp
     */
    public function getRecentSpreads(array $data): array
    {
        return $this->getPublicEndpoint("Spread", $data);
    }

    /** Construction of 'GET' HTTP requests for public endpoints. */
    private function getPublicEndpoint(string $path_end, array $data): array
    {
        $path = "/" . self::VERSION . "/public/" . $path_end;
        $query = $data ? "?" . http_build_query($data) : '';
        $url = self::BASE_URI . $path . $query;

        $response = $this->http_client->request('GET', $url);

        if (($status = $response->getStatusCode()) !== 200) {
            throw new \HttpResponseException(
                "Response to '/public/$path_end' returned status $status. Content: {$response->getContent()}");
        }
        return $response->toArray(true);
    }



    ///////////////////////
    // Private endpoints //
    ///////////////////////

    /// USER DATA
    /////////////
    public function getAccountBalance(): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("Balance");
    }

    /**
     * string $data['asset'] - (optional) Base asset used to determine balance. Default: "ZUSD"
     */
    public function getTradeBalance(array $data = []): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("TradeBalance", $data);
    }

    /**
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     * int $data['userref'] - (optional) Restrict results to given user reference id
     */
    public function getOpenOrders(array $data = []): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("OpenOrders", $data);
    }

    /**
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     * int $data['userref'] - (optional) Restrict results to given user reference id
     * int $data['start'] - (optional) Starting unix timestamp or order tx ID of results (exclusive)
     * int $data['end'] - (optional) Ending unix timestamp or order tx ID of results (inclusive)
     * int $data['ofs'] - (optional) Result offset for pagination
     * string $data['closetime'] - (optional) Which time to use to search (enum: open, close, both). Default: 'both'
     */
    public function getClosedOrders(array $data = []): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("ClosedOrders", $data);
    }

    /**
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     * int $data['userref'] - (optional) Restrict results to given user reference id
     * string $data['txid'] - (required) Comma delimited list of transaction IDs to query info about (20 maximum)
     */
    public function queryOrdersInfo(array $data = []): array
    {
        $this->checkRequired($data, 'txid');
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("QueryOrders", $data);
    }

    /**
     * string $data['type'] - (optional) Type of trade (enum: "all" "any position" "closed position" "closing position" "no position"). Default: 'all'
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     * int $data['start'] - (optional) Starting unix timestamp or order tx ID of results (exclusive)
     * int $data['end'] - (optional) Ending unix timestamp or order tx ID of results (inclusive)
     * int $data['ofs'] - (optional) Result offset for pagination
     */
    public function getTradesHistory(array $data = []): array
    {
        $this->checkRateLimit(2);
        return $this->getPrivateEndpoint("TradesHistory", $data);
    }

    /**
     * string $data['txid'] - (optional) Comma delimited list of transaction IDs to query info about (20 maximum)
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     */
    public function queryTradesInfo(array $data = []): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("QueryTrades", $data);
    }

    /**
     * string $data['txid'] - (optional) Comma delimited list of txids to limit output to
     * bool $data['docalcs'] - (optional) Whether to include P&L calculations. Default: false
     * string $data['consolidation'] - (optional) Consolidate positions by market/pair. Default: 'market'
     */
    public function getOpenPositions(array $data = []): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("OpenPositions", $data);
    }

    /**
     * string $data['asset'] - (optional) Comma delimited list of assets to restrict output to. Default: "all"
     * string $data['aclass'] - (optional) Asset class. Default: "currency"
     * string $data['type'] - (optional) Type of ledger to retrieve (enum: "all" "deposit" "withdrawal" "trade" "margin"). Default: 'all'
     * int $data['start'] - (optional) Starting unix timestamp or ledger ID of results (exclusive)
     * int $data['end'] - (optional) Ending unix timestamp or ledger ID of results (inclusive)
     * int $data['ofs'] - (optional) Result offset for pagination
     */
    public function getLedgersInfo(array $data = []): array
    {
        $this->checkRateLimit(2);
        return $this->getPrivateEndpoint("Ledgers", $data);
    }

    /**
     * string $data['id'] - (optional) Comma delimited list of ledger IDs to query info about (20 maximum)
     * bool $data['trades'] - (optional) Whether or not to include trades related to position in output. Default: false
     */
    public function queryLedgers(array $data = []): array
    {
        $this->checkRateLimit(2);
        return $this->getPrivateEndpoint("QueryLedgers", $data);
    }

    /**
     * Note: If an asset pair is on a maker/taker fee schedule, the taker side is given in fees and maker side in fees_maker.
     * For pairs not on maker/taker, they will only be given in fees.
     *
     * string $data['pair'] - (optional) Comma delimited list of ledger IDs to query info about (20 maximum)
     * bool $data['fee-info'] - (optional) Whether or not to include fee info in results
     */
    public function getTradeVolume(array $data = []): array // TODO probar porque hay un query parameter que no mandamos!! ver docs.
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("TradeVolume", $data);
    }


    /// USER TRADING
    ////////////////
    /**
     * int $data['userref'] - (optional) User reference id
     * string $data['ordertype'] - (required) Order type (enum: "market" "limit" "stop-loss" "take-profit" "stop-loss-limit" "take-profit-limit" "settle-position")
     * string $data['type'] - (required) Order direction (enum: "buy" "sell")
     * string $data['volume'] - (optional) Order quantity in terms of the base asset
     * string $data['pair'] - (required) Asset pair 'id' or 'altname'
     * string $data['price'] - (optional) Limit price for 'limit' orders. Trigger price for 'stop-loss', 'stop-loss-limit', 'take-profit' and 'take-profit-limit' orders
     * string $data['price2'] - (optional) Limit price for 'stop-loss-limit' and 'take-profit-limit' orders
     * string $data['leverage'] - (optional) Amount of leverage desired (default = none)
     * string $data['oflags'] - (optional) Comma delimited list of order flags:
     *                  'post': post-only order (available when ordertype = limit)
     *                  'fcib': prefer fee in base currency (default if selling)
     *                  'fciq': prefer fee in quote currency (default if buying, mutually exclusive with fcib)
     *                  'nompp': disable market price protection for market orders
     * string $data['timeinforce'] - (optional) Time-in-force of the order to specify how long it should remain in the order book before being cancelled. Default: 'GTC'
     *                  'GTC': Good-'til-cancelled
     *                  'IOC': immediate-or-cancel. Will immediately execute the amount possible and cancel any remaining balance rather than resting in the book.
     *                  'GTD': good-'til-date. If specified, must coincide with a desired expiretm.
     * string $data['starttm'] - (optional) Scheduled start time. Can be specified as an absolute timestamp or as a number of seconds in the future.
     *                  0: now (default)
     *                  +<n>: schedule start time seconds from now
     *                  <n>: unix timestamp of start time
     * string $data['expiretm'] - (optional) Expiration time
     *                  0: no expiration (default)
     *                  +<n>: expire seconds from now, minimum 5 seconds
     *                  <n>: unix timestamp of expiration  time
     * string $data['close']['ordertype'] - (optional) Conditional close order type (enum: "limit" "stop-loss" "take-profit" "stop-loss-limit" "take-profit-limit").
     * string $data['close']['price'] - (optional) Conditional close order 'price'
     * string $data['close']['price2'] - (optional) Conditional close order 'price2'
     * string $data['close']['price2'] - (optional) Conditional close order 'price2'
     * string $data['deadline'] - (optional) RFC3339 timestamp (e.g. 2021-04-01T00:18:45Z) after which the matching engine should reject the new order request, in presence of latency or order queueing. min now() + 5 seconds, max now() + 60 seconds.
     * bool $data['validate'] - (optional) Validate inputs only. Do not submit order. Default: false
     */
    public function addOrder(array $data = []): array
    {
        $this->checkRequired($data, 'ordertype', 'type', 'pair');
        return $this->getPrivateEndpoint("AddOrder", $data);
    }

    /**
     * int|string $data['txid'] - (required) Open order transaction ID (txid) or user reference (userref)
     */
    public function cancelOrder(array $data = []): array
    {
        $this->checkRequired($data, 'txid');
        return $this->getPrivateEndpoint("CancelOrder", $data);
    }

    public function cancelAllOrders(): array
    {
        return $this->getPrivateEndpoint("CancelAll", []);
    }


    /// USER STAKING
    ////////////////

    /**
     * string $data['asset'] - (required) Asset to stake (asset ID or altname)
     * string $data['amount'] - (required) Amount of the asset to stake
     * string $data['method'] - (required) Name of the staking option to use (refer to the Staking Assets endpoint)
     */
    public function stakeAsset($data = []): array
    {
        $this->checkRateLimit();
        $this->checkRequired($data, 'asset', 'amount', 'method');
        return $this->getPrivateEndpoint("Stake", $data);
    }

    /**
     * string $data['asset'] - (required) Asset to unstake (asset ID or altname). Must be a valid staking asset. Ex "XBT.M", "ADA.S"
     * string $data['amount'] - (required) Amount of the asset to unstake
     */
    public function unstakeAsset($data = []): array
    {
        $this->checkRateLimit();
        $this->checkRequired($data, 'asset', 'amount');
        return $this->getPrivateEndpoint("Stake", $data);
    }

    public function getStakeableAssets(): array
    {
        $this->checkRateLimit();
        return $this->getPrivateEndpoint("Staking/Assets", []);
    }




    /** Construction of 'POST' HTTP requests for private endpoints. */
    private function getPrivateEndpoint(string $path_end, array $data = []): array
    {
        $path = "/" . self::VERSION . "/private/" . $path_end;
        $url = self::BASE_URI . $path;

        $nonce = $this->generateNonce();

        $response = $this->http_client->request('POST', $url, [
            'headers' => [
                'API-Key' => $this->api_key,
                'API-Sign' => $this->generateApiSign($path, $nonce, $data)
            ],
            'body' => array_merge($data, ['nonce' => $nonce])
        ]);

        if (($status = $response->getStatusCode()) !== 200) {
            throw new \HttpResponseException(
                "Response to '$path' returned status $status. Content: {$response->getContent()}");
        }

        return $response->toArray(true);
    }

    /** Nonce is a mandatory parameter on private endpoints. It must be ever-increasing, so we use Unix timestamps. */
    private function generateNonce(): int
    {
        $ms_s = explode(' ', microtime());
        return (int)($ms_s[1].substr($ms_s[0], 2, 3));
    }

    /** Authentication method for private endpoints. */
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


    private function checkRequired(array $data, string ...$keys): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException("Missing mandatory key '$key'. Can't perform request.");
            }
        }
    }

    private function checkRateLimit(int $add = 1): void
    {
        $s_since_last_call = microtime(true) - $this->last_call_timestamp;
        $counter_decay = $add - $s_since_last_call*self::COUNTER_DECAY_PER_S;
        while ($this->call_counter + $counter_decay > self::COUNTER_LIMIT) {
            sleep(1);
            $s_since_last_call = microtime(true) - $this->last_call_timestamp;
            $counter_decay = $add - $s_since_last_call*self::COUNTER_DECAY_PER_S;
        }

        $this->call_counter = max(0, $this->call_counter + $counter_decay);
    }

}