<?php
/*
 * IPFS HTTP API commands for use in PHP
 *
 * @package php-ipfs
 */
namespace IpfsApi;

/*
 * Ipfs
 */
class Ipfs
{
    /*
     * version
     */
    public static function version()
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:5001/api/v0/']);
        $response = $client->request('POST', 'version');
        $body = $response->getBody();
        return $body;
    }
}
