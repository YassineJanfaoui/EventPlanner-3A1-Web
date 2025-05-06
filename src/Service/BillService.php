<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\BillRepository;
use Symfony\Contracts\Cache\CacheInterface;

class BillService
{
    private const BASE_CURRENCY = 'USD';
    private const CACHE_KEY = 'currency_rates';
    private const CACHE_TTL = 3600;

    public function __construct(
        private BillRepository $billRepository,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {}

    public function checkUpcomingBills(): array
    {
        return $this->billRepository->findBillsDueWithin(7); // 7 days
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = $this->getExchangeRates();

        // Convert from source currency to USD first if needed
        if ($from !== self::BASE_CURRENCY) {
            if (!isset($rates[$from])) {
                throw new \InvalidArgumentException(sprintf('Unsupported currency: %s', $from));
            }
            $amount = $amount / $rates[$from];
        }

        if ($to !== self::BASE_CURRENCY) {
            if (!isset($rates[$to])) {
                throw new \InvalidArgumentException(sprintf('Unsupported currency: %s', $to));
            }
            $amount = $amount * $rates[$to];
        }

        return $amount;
    }

    public function getAvailableCurrencies(): array
    {
        $rates = $this->getExchangeRates();
        return array_merge([self::BASE_CURRENCY], array_keys($rates));
    }

    private function getExchangeRates(): array
    {
        return $this->cache->get(self::CACHE_KEY, function () {
            $response = $this->httpClient->request(
                'GET',
                'https://api.freecurrencyapi.com/v1/latest?apikey=fca_live_JBUBgIhSd0l3wn8u4HS1DPddYj8r1nuY8pLlkh1R'
            );

            $data = $response->toArray();

            if (!isset($data['data'])) {
                throw new \RuntimeException('Invalid API response format');
            }

            return $data['data'];
        }, self::CACHE_TTL);
    }
}
