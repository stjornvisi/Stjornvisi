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
        $this->client->setUri("http://ja.is/kort/leit/");
        $this->client->setParameterGet(array(
            'q' => $address,
        ));

        $response = $this->client->send();
        if ($response->getStatusCode() == 200) {
            $json = json_decode($response->getBody());
            return (isset($json->map->items[0]->coordinates) )
                ?(object)array(
                    'lat' => (float)$json->map->items[0]->coordinates->lat,
                    'lng' => (float)$json->map->items[0]->coordinates->lon,)
                :(object)array(
                    'lat' => null,
                    'lng' => null,
                );
        } else {
            return (object)array(
                'lat' => null,
                'lng' => null,
            );
        }
    }
}
