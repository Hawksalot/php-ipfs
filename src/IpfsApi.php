<?php
/*
 * IPFS HTTP API commands for use in PHP
 *
 * @package php-ipfs
 */
namespace PhpIpfs;

/*
 * Ipfs
 */
use GuzzleHttp\Client;

class Ipfs
{
    /*
     * instantiates Client instance for other functions to use to connect to local IPFS daemon
     */
    private static function getClient()
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:5001/api/v0/',
            'max' => 5,
            'strict' => false,
            'referer' => false,
            'protocols' => ['http'],
            'track_redirects' => false,
            'expect' => true // not sure if this is necessary, check expect docs note about http 1.1
        ]);
        return $client;
    }

    /*
     * @link https://gist.github.com/liunian/9338301
     */
    private static function getHumanReadableBytes($bytes, $precision = 2)
    {
        static $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($bytes / $step) > 0.9) {
            $bytes = $bytes / $step;
            $i++;
        }
        return round($bytes, $precision).$units[$i];
    }

    /*
     * adds file to local IPFS node by absolute path
     *
     * @param string $objectPath /path/to/local/file
     * @param string $objectName
     * @param boolean $H Hidden. Include files that are hidden. Only takes effect on recursive add
     * @param boolean $n Only-hash. Only chunk and hash - do not write to disk. can be tested by running ipfs pin ls (I think)
     * @param boolean $p Progress. Stream progress data. @todo not sure what this does, if anything. find out
     * @param boolean $pin Pin this object when adding. @todo test
     * @param boolean $r Recursive. Add directory paths recursively.
     * @param boolean $t Trickle. Use trickle-dag format for dag generation.
     * @param boolean $w Wrap-with-directory. Wrap files with a directory object.
     *
     * @return string Hash of added file
     */
    public static function add($objectPath, $H = false, $n = false, $p = true, $pin = true, $r = false, $t = false, $w = false)
    {
	    $client = self::getClient();
        $response = $client->request('POST', 'add', [
	        'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'object_to_add',
                    'contents' => fopen(realpath($objectPath), "r")
                ]
            ],
	        'query' => [
	            'H' => $H,
	            'n' => $n,
                'p' => $p,
                'pin' => $pin,
                'r' => $r,
                't' => $t,
                'w' => $w
            ]
        ]);
        $output = $response->getBody();
        return $output;
        //$output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        //return $output['Hash'];
    }

    /*
     * bitswap/stat
     *
     * @return array
     */
    public static function bitswapStat()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'bitswap/stat');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * bitswap/unwant
     */
    public static function bitswapUnwant(...$args)
    {
        $client = self::getClient();
        foreach($args as $arg)
        {
            $response = $client->request('POST', 'bitswap/unwant', [
                'query' => [
                    'arg' => $arg
                ]
            ]);
        }
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * bitswap/wantlist
     */
    public static function bitswapWantlist($peer)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'bitswap/wantlist', [
            'query' => [
                'peer' => $peer
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * block/get
     *
     * @return string
     */
    public static function blockGet($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'block/get', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * block/put
     */
    public static function blockPut($dataPath)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'block/put', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'raw_block_data',
                    'content' => fopen(realpath($dataPath), "r")
                ]
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * block/stat
     */
    public static function blockStat($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'block/stat', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * bootstrap & bootstrap/list
     */
    public static function bootstrap()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'bootstrap');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * bootstrap/add
     *
     * @todo this implements the default option in a weird way. try to implement it a better way
     */
    public static function bootstrapAdd($peer)
    {
        $client = self::getClient();
        if($peer === 'default')
        {
            $response = $client->request('POST', 'bootstrap/add', [
                'query' => [
                    'default'
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'bootstrap/add', [
                'query' => [
                    'arg' => $peer
                ]
            ]);
        }
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * bootstrap/rm
     */
    public static function boostrapRm($peer)
    {
        $client = self::getClient();
        if($peer === 'all')
        {
            $response = $client->request('POST', 'bootstrap/rm', [
                'query' => [
                    'all'
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'bootstrap/rm', [
                'query' => [
                    'arg' => $peer
                ]
            ]);
        }
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/findprovs: finds remote IPFS nodes that are hosting target Merkle-Dag hash
     *
     * @return string
     */
    public static function findRemoteProvidersByObjectHash($hash)
    {
        // @todo abstract this client away
        $client = self::getClient();
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
     * ping
     * @todo everything
     */
    public static function getLatencyToRemoteHost($peerID)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'ping', [
            'debug' => true,
            'query' => [
                'arg' => $peerID
            ]
        ]);
        $outputData = $response->getBody();
        var_dump($response);
        return gettype($outputData);

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
     * Call $peer or $proto but not both!
     *
     * @param string $peer peer id
     * @param string $proto protocol to print bw stats for
     *
     * @todo implement poll and interval
     *
     * @return array
     */
    public static function getBandwidthStats($peer = null, $proto = null, $poll = false, $interval = '1s')
    {
        $client = self::getClient();
        /*
         * this response returns an assoc array with 4 pairs
         * of key/values but they may not need to be accurate
         */
        $response = $client->request('POST', 'stats/bw', [
            //'debug' => true,
            'query' => [
                'peer' => $peer,
                'proto' => $proto,
                //'poll' => true,
                //'interval' => $interval
            ]
        ]);

        // extract data to assoc array
        $outputData = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        // fill array with human-readable stats
        $i = -1;
        foreach($outputData as $key => $value)
        {
            $i++;
            $output[$i] = $key.' = '.self::getHumanReadableBytes($value);
        }

        return $output;
    }

    /*
     * queries local ipfs node for ipfs version
     *
     * @return string indicates ipfs version running on queried ipfs node
     */
    public static function version()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'version');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $output['Version'];
    }
}
