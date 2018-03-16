<?php

namespace Sinesp;

use Sinesp\Exceptions\SinespException;

class Placa
{
    private $client;
    private $licensePlate;

    public function __construct($licensePlate = null)
    {
        $this->client = new Client();

        if ($licensePlate) {
            $this->licensePlate = $licensePlate;
        }
    }

    public function search($licensePlate=null, array $proxy = []) : array
    {
        if ($licensePlate) {
            $this->licensePlate = $licensePlate;
        }

        $this->setUp();

        $this->client->proxy($proxy);

        $response = $this->client
            ->search($this->licensePlate);

        if ($response['codigoRetorno'] > 0) {
            throw new SinespException($response['mensagemRetorno']);
        }
        
        return $response;

    }

    private function setUp() : void
    {
        $this->format();

        if (! $this->validate()) {
            throw new SinespException('Placa invalida.');
        }
    }

    private function validate() : bool
    {
        $result = false;

        if (preg_match('/^[a-zA-Z]{3}-?\d{4}$/i', $this->licensePlate)) {
            $result = true;
        }

        return $result;
    }

    private function format() : void
    {
        $this->licensePlate = str_replace(['-', ' '], '', $this->licensePlate);
    }
}
