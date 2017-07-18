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
     * add
     *
     * @todo everything
     */
    public static function add($fileLocation)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/',
            'max' => 5,
            'strict' => false,
            'referer' => false,
            'protocols' => ['http', 'https'],
            'track_redirects' => false,
            'expect' => true // not sure if this is necessary, check expect docs note about http 1.1
        ]);
        $response = $client->request('POST', 'add', [
            'multipart' => [
                'path' => realpath($fileLocation)
            ],
            'debug' => true
        ]);
        echo $response;
        $output = $response->getBody();
        return $output;
    }
    /*
     * tar add
     *
     * @todo everything
     */
    public static function tarAdd($tarLocation)
    {
        if(file_exists($tarLocation))
        {
            return 'file exists';
        }
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:5001/api/v0/']);
        $response = $client->request('POST', 'tar/add', [
            'file' => $tarLocation
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        $body = $response->getBody()->getContents();
    }

    /*
     * version
     */
    public static function version()
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:5001/api/v0/']);
        $response = $client->request('POST', 'version');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $output['Version'];
    }
}
