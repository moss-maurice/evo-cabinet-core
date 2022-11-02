<?php

namespace mmaurice\cabinet\core\providers;

use Exception;
use mmaurice\cabinet\core\App;
use mmaurice\qurl\Client;

class MasterTourProvider
{
    protected $baseUrl;
    protected $logFile;
    protected $client;

    public function __construct($baseUrl, $logFile)
    {
        $this->baseUrl = $baseUrl;
        $this->logFile = $logFile;

        $this->client = (new Client)->request();
    }

    public function departureCities()
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'departureCities',
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > departureCities: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function destination(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'Destination',
            array_merge([
                'departureCities' => -1,
            ], $properties),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > Destination: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function getTourTypes(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'searchApi',
            array_merge([
                'resortIds' => -1,
            ], $properties, [
                'action' => 'GetTourTypes',
            ]),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > GetTourTypes: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function actualizeCities(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'ActualizeCities',
            array_merge([
                'cityKeys' => -1,
            ], $properties),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > ActualizeCities: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function actualizeTourType(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'ActualizeTourType',
            array_merge([
                'departureCityKeys' => -1,
                'Type' => 1,
            ], $properties),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > ActualizeTourType: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function hotelInfo($key)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'searchApi',
            array_merge([
                'hotelKey' => intval($key),
                'needImages' => 1,
                'imageSize' => 2,
            ], [
                'action' => 'GetHotelInfo',
            ]),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > GetHotelInfo: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function hotelImages($key)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'HotelImages',
            [
                'hotelKey' => intval($key),
            ],
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > HotelImages: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function hotel($key)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'searchApi',
            array_merge([
                'hotelKey' => intval($key),
                'needImages' => 1,
                'imageSize' => 2,
            ], [
                'action' => 'GetHotelInfo',
            ]),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > GetHotelInfo: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function tourDate(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'TourDate',
            array_merge([
                'departureCity' => -1,
                'destinationCity' => -1,
                'Type' => 1,
                'tourTypes' => -1,
            ], $properties),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > TourDate: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function duration(array $properties = [])
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'Duration',
            array_merge([
                'departureCity' => -1,
                'destinationCity' => -1,
                'tourTypes' => -1,
            ], $properties),
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > Duration: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function tour(array $properties = [])
    {
        $time = microtime(true);

        $defaultProperties = [
            'DepartureCityKeys' => -1,
            'DestinationKey' => -1,
            'PageNumber' => 1,
            'PageSize' => 20,
            'ShowToursWithoutHotels' => -1,
            'isFromBasket' => 'false',
            'isFillSecondaryFilters' => 'false',
            'DestinationType' => 1,
            'AdultCount' => 2,
            'CurrencyName' => 'рб',
            'AviaQuota' => 5,
            'HotelQuota' => 5,
            'BusTransferQuota' => 7,
            'TourType' => -1,
            'TimeDepartureFrom' => '00:00',
            'TimeDepartureTo' => '23:59',
            'TimeArrivalFrom' => '00:00',
            'TimeArrivalTo' => '23:59',
            'SearchId' => 26,
            'RemoteHotelMode' => 0,
            //'MinPrice' => 'NaN',
        ];

        if ((array_key_exists('Durations', $properties) and is_array($properties['Durations'])) or (array_key_exists('Dates', $properties) and is_array($properties['Dates'])) or (array_key_exists('Tour', $properties) and is_array($properties['Tour']))) {
            $get = http_build_query(array_merge($defaultProperties, $properties), '', '&');

            $get = preg_replace('/(Durations|Dates|Tour)(\%5B)([\d]+)(\%5D)(\=)/i', '$1$5', $get);

            //fwrite(STDOUT, $this->baseUrl . 'Tour?' . $get . PHP_EOL);
            //fwrite(STDOUT, 'URL: ' . $this->baseUrl . 'Tour?' . $get . PHP_EOL);

            $response = $this->client->get([
                $this->baseUrl . 'Tour?' . $get,
            ]);
        } else {
            $response = $this->client->get([
                $this->baseUrl . 'Tour',
                array_merge($defaultProperties, $properties),
            ]);
        }

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > Tour: {$json}" . PHP_EOL, FILE_APPEND);
        }

        $body = $response->getRawResponseBody();

        //fwrite(STDOUT, 'URL: ' . $response->getRequestUrl() . PHP_EOL);
        //fwrite(STDOUT, 'CODE: ' . $response->getResponseCode() . PHP_EOL);
        //fwrite(STDOUT, '' . PHP_EOL);
        /*
        var_dump($response->getRequestUrl());
        var_dump($response->getRawRequestHeader());
        var_dump($response->getRawRequestBody());
        var_dump($response->getResponseCode());
        var_dump($response->getRawResponseHeader());
        var_dump($response->getRawResponseBody());
        die();
        */

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function tourImages($tourKeys)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'TourImages',
            [
                'tourKeys' => intval($tourKeys),
            ],
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > TourImages: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function checkTourProgram($tourKey)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'CheckTourProgram',
            [
                'tourKey' => intval($tourKey),
            ],
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > CheckTourProgram: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function tourProgram($fromDateTime)
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'TourProgram',
            [
                'fromDateTime' => $fromDateTime,
            ],
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > TourProgram: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function currency()
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'Currency',
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > Currency: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function currencyRates()
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'CurrencyRates',
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > CurrencyRates: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }

    public function getAllCurrencies()
    {
        $time = microtime(true);

        $response = $this->client->get([
            $this->baseUrl . 'AllCurencies/getAllCurrencies',
        ]);

        if ($this->logFile) {
            $json = json_encode([
                'request' => [
                    'timestamp' => $time,
                    'url' => $response->getRequestUrl(),
                    'header' => $response->getRawRequestHeader(),
                    'body' => $response->getRawRequestBody(),
                ],
                'response' => [
                    'timestamp' => microtime(true),
                    'code' => $response->getResponseCode(),
                    'header' => $response->getRawResponseHeader(),
                    'body' => $response->getRawResponseBody(),
                ],
            ]);

            file_put_contents($this->logFile, date("Y-m-d H:i:s") . " > AllCurencies/getAllCurrencies: {$json}" . PHP_EOL, FILE_APPEND);
        }

        if (in_array($response->getResponseCode(), [200])) {
            return $response->getResponseBody();
        }

        return [];
    }
}
