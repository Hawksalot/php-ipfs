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
use GuzzleHttp\Client;


class Ipfs
{
    /*
     * adds file to local IPFS node by absolute path
     *
     * @param string $objectPath /path/to/local/file
     * @param string $objectName
     *
     * @todo update to polymorphic function to handle directory uploads
     *
     * @return string Hash of added file
     */
    public static function addLocalFileFromPath($objectPath, $objectName)
    {
        // @todo abstract this client away
	    $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/',
            'max' => 5,
            'strict' => false,
            'referer' => false,
            'protocols' => ['http'],
            'track_redirects' => false,
            'expect' => true // not sure if this is necessary, check expect docs note about http 1.1
        ]);
        // @todo test response time of request with request options enabled
	    $response = $client->request('POST', 'add', [
	        /*'headers' => [
                'boundary' => 'CUSTOM'
            ],*/
	        'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => $objectName,
                    'contents' => fopen(realpath($objectPath), "r"),
                ]
            ],/*
	        'query' => [
                'stream-channels' => true
            ]*/
        ]);
        //var_dump($response);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output['Hash'];
    }

    /*
     * dht/findprovs: finds remote IPFS nodes that are hosting target Merkle-Dag hash
     *
     * @return string
     */
    public static function findRemoteProvidersByObjectHash($hash)
    {
        // @todo abstract this client away
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/',
            'max' => 5,
            'strict' => false,
            'referer' => false,
            'protocols' => ['http'],
            'track_redirects' => false,
            'expect' => true // not sure if this is necessary, check expect docs note about http 1.1
        ]);
        $response = $client->request('POST', 'dht/findprovs', [
           'query' => [
               'arg' => $hash
           ]
        ]);
        // @todo parse response to return meaningful, useful data
        $output = $response->getBody()->getContents();
        return $output;
    }

    /*
     * adds file to local ipfs node by Merkle-Dag hash
     *
     * @param string $hash Merkle-Dag hash of target file
     *
     * @todo everything
     */
    public static function addRemoteObjectFromHash($hash)
    {
       //
    }

    /*
     * adds tar file to local ipfs node from local filepath
     *
     * @param string $tarPath /path/to/tar
     *
     * @todo everything
     */
    public static function tarAdd($tarPath)
    {
	    //
    }

    /*
     * stats/bw: prints ipfs bandwidth information
     *
     * @return array
     */
    public static function getBandwidthStats()
    {
        $client = new Client(['base_uri' => 'http://localhost:5001/api/v0/']);
        $response = $client->request('POST', 'stats/bw');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * queries local ipfs node for ipfs version
     *
     * @return string indicates ipfs version running on queried ipfs node
     */
    public static function version()
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:5001/api/v0/']);
        $response = $client->request('POST', 'version');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $output['Version'];
    }
}
