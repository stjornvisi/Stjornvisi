<?php

namespace Stjornvisi\Service;

use Zend\Http\Client;

class JaMap implements MapInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request($address)
    {
        $response = $this->client->setUri("http://ja.is/kort/leit")
            ->setMethod('GET')
            ->setParameterGet([
                'q' => $address,
            ])
            ->send();

        if ($response->getStatusCode() == 200) {
            $json = json_decode($response->getBody());
            return (isset($json->items[0]->coordinates) )
                ? (object) [
                        'lat' => (float)$json->items[0]->coordinates->lat,
                        'lng' => (float)$json->items[0]->coordinates->lon,
                    ]
                : (object) [
                    'lat' => null,
                    'lng' => null,
                ];
        } else {
            return (object) [
                'lat' => null,
                'lng' => null,
            ];
        }
    }
}
