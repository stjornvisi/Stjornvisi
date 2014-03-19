<?php

namespace Stjornvisi\Service;

use Zend\Http\Client;

class GoogleMap implements MapInterface {
    private $client;
    public function __construct(Client $client){
        $this->client = $client;
    }

    /**
     * Convert an address to a LAT / LNG numbers
     * @param string $address
     * @return object
     */
    public function request( $address ){

        $this->client->setUri("http://maps.googleapis.com/maps/api/geocode/json");
        $this->client->setParameterGet(array(
            'address' => $address,
            'sensor' => 'false', // this is not a bool value, it's a string
            'language' => 'is',
        ));

        $response = $this->client->send();
        if( $response->getStatusCode() == 200 ){
            $json = json_decode($response->getBody());
            return ( $json->status === 'OK' )
                ?(object)array(
                    'lat' => (float)$json->results[0]->geometry->viewport->northeast->lat,
                    'lng' => (float)$json->results[0]->geometry->viewport->northeast->lng
                )
                :(object)array(
                    'lat' => null,
                    'lng' => null,
                );

        }else{
            return (object)array(
                'lat' => null,
                'lng' => null,
            );
        }
    }
} 
