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
     * adds file to local IPFS node by local filepath
     *
     * @param string $filePath /path/to/local/file
     */
    public static function addFromPath($filePath)
    {
        // @todo abstract this client away
	    $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/',
            'max' => 5,
            'strict' => false,
            'referer' => false,
            'protocols' => ['http', 'https'],
            'track_redirects' => false,
            'expect' => true // not sure if this is necessary, check expect docs note about http 1.1
        ]); 
        // @todo structure this request so it returns successfully
	    $response = $client->request('POST', 'add', [
	        'debug' => true,
	        'headers' => [
		        ''
	        ],
            'multipart' => [
                'path' => fopen(realpath($filePath), "r")
            ],
	        'query' => [
            'stream-channels' => true
            ]
        ]);
        echo 'response = ' . $response;
        $output = $response->getBody();
        return $output;
    }

    /*
     * adds file to local ipfs node by Merkle-Dag hash
     *
     * @param string $hash Merkle-Dag hash of target file
     *
     * @todo everything
     */
    public static function addFromHash($hash)
    {
        // @todo abstract this client away
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/'
        ]);
        $response = $client->request('POST', 'add', [
            'arg' => $hash
        ]);
        $output = $response->getBody()->getContents();
	    return $output;
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
