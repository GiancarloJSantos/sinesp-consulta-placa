<?php

namespace Sinesp;

use Sinesp\Exceptions\SinespException;

class Client
{
	const SECRET = '#8.1.0#Mw6HqdLgQsX41xAGZgsF';
    const URL = 'https://cidadao.sinesp.gov.br/sinesp-cidadao/mobile/consultar-placa/v3';
    
    private $proxy = array();
    private $licensePlate;
    private $response;
    private $data;

    public function __construct($proxy = [])
    {
        if ($proxy) {
            $this->proxy($proxy);
        }
    }

    public function __get($param)
    {
        return array_key_exists($param, $this->data) ? $this->data[$param] : '';
    }

    public function proxy($proxy) : Client
    {

        if (isset($proxy['ip'])) {
            $this->proxy = sprintf('%s', $proxy['ip']);
        }

        if (isset($proxy['port'])) {
            $this->proxy = sprintf('%s:%s', $this->proxy, $proxy['port']);
        }

        return $this;

    }

    public function search($licensePlate) : array
    {
        $this->licensePlate = $licensePlate;
        $this->checkRequired();
        $this->doRequest();

        return $this->data;
    }

    private function checkRequired() : void
    {
        if (! function_exists('curl_init')) {
            throw new SinespException('Incapaz de processar. PHP requer biblioteca cURL');
        }

        if (! function_exists('simplexml_load_string')) {
            throw new SinespException('Incapaz de processar. PHP requer biblioteca libxml');
        }
    }

    private function token() : string
    {
        return hash_hmac('sha1', $this->licensePlate, $this->licensePlate . self::SECRET);
    }

    private function latitude() : string
    {

        return '-38.5' . rand(100000, 999999);

    }

    private function longitude() : string
    {

        return '-3.7' . rand(100000, 999999);

    }

    private function xml() : string
    {
        $xml=<<<EOX
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<v:Envelope xmlns:v="http://schemas.xmlsoap.org/soap/envelope/">
<v:Header>
<b>samsung GT-I9192</b>
<c>ANDROID</c>
<d>8.1.0</d>
<i>%s</i>
<e>4.1.5</e>
<f>10.0.0.1</f>
<g>%s</g>
<k></k>
<h>%s</h>
<l>%s</l>
<m>8797e74f0d6eb7b1ff3dc114d4aa12d3</m>
</v:Header>
<v:Body>
<n0:getStatus xmlns:n0="http://soap.ws.placa.service.sinesp.serpro.gov.br/">
<a>%s</a>
</n0:getStatus>
</v:Body>
</v:Envelope>
EOX;

        return sprintf($xml, $this->latitude(), $this->token(), $this->longitude(), strftime('%Y-%m-%d %H:%M:%S'), $this->licensePlate);

    }

    private function doRequest() : void
    {
        $xml = $this->xml();

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($xml),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, self::URL);

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        curl_close($ch);

        if (! $response) {
            throw new SinespException('O servidor não retornou resposta!');
        }

        $response = str_ireplace(['soap:', 'ns2:'], '', $response);

        $this->data = (array) simplexml_load_string($response)->Body->getStatusResponse->return;
    }

}