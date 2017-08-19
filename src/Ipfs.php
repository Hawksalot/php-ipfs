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
     * getReturnContent
     *
     * takes contents of response as string and returns associative array
     *
     * @param string $responseContents
     */
    private static function getReturnContent($responseContents)
    {
        $htmlString = htmlentities($responseContents);
        $formattedHtml = html_entity_decode($htmlString);
        $output = json_decode($formattedHtml, true);
        return $output;
    }

    /*
     * adds file to local IPFS node by absolute path
     *
     * @param string $objectPath /path/to/local/file
     * @param string $objectName
     * @param boolean $H Hidden. Include files that are hidden. Only takes effect on recursive add
     * @param boolean $n Only-hash. Only chunk and hash - do not write to disk. can be tested by running ipfs pin ls (I think)
     * @param boolean $p Progress. Stream progress data. TEMPORARILY SET TO ALWAYS FALSE
     * @param boolean $pin Pin this object when adding. @todo test
     * @param boolean $r Recursive. Add directory paths recursively. @todo get function to stream directory and directory contents
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
                'p' => false,
                'pin' => $pin,
                'r' => $r,
                't' => $t,
                'w' => $w
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
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
     * @param string $peer in format /ip4/$ip/tcp/$port/ipfs/$peerID
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
     *
     * @param string $peer peerID in format /ip4/$ip/tcp/$port/ipfs/$peerID
     *
     * @todo this implementes the all option in a weird way. try to make it work in a better way
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
     * cat
     *
     * @param string $hash the hash of the IPFS content to be pulled
     */
    public static function cat($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'cat', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        // @todo return different ouputs based on response
        return $response;
    }

    /*
     * commands
     *
     * @todo decide if this functionality is even reasonable to leave in
     */
    public static function commands()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'commands');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * config
     *
     * @param string $arg1 config option to either retrieve value of or change value of
     * @param string $arg2 value to change config option to
     *
     * @todo implement use of bool and json variables
     */
    public static function config($arg1, $arg2)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'config', [
            'query' => [
                'arg1' => $arg1,
                'arg2' => $arg2
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * config/replace
     *
     * @param string $configFilePath local location of new config file
     */
    public static function configReplace($configFilePath)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'config/replace', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'new_config_file',
                    'contents' => fopen(realpath($configFilePath), "r")
                ]
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * config/show
     *
     * SECURITY WARNING: THIS WILL OUTPUT YOUR PRIVATE KEY
     */
    public static function configShow()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'config/show');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/findpeer
     *
     * @param string $peerID hash of peer to target
     */
    public static function dhtFindpeer($peerID)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dht/findpeer', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/findprovs: finds remote IPFS nodes that are hosting target Merkle-Dag hash
     *
     * @param string $hash IPFS content hash to find providers for
     *
     * @return string
     */
    public static function dhtFindprovs($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dht/findprovs', [
           'query' => [
               'arg' => $hash
           ]
        ]);
        // @todo parse response to return meaningful, useful data
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/get
     *
     * @param string $hash content hash to find providers for
     */
    public static function dhtGet($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dht/get', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/put
     *
     * @param string $key key part of key-value pair to write to IPFS
     * @param string $value value part of key-value
     */
    public static function dhtPut($key, $value)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dht/put', [
            'query' => [
                'arg1' => $key,
                'arg2' => $value
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dht/query
     *
     * @param string $peerID hash of peer to find similar peers for
     */
    public static function dhtQuery($peerID)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dht/query', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * diag/cmds
     */
    public static function diagCmds()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'diag/cmds');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * diag/cmds/clear
     */
    public static function diagCmdsClear()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'diag/cmds/clear');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * diag/cmds/set-time
     *
     * @param string $time time to keep inactive requests in log
     */
    public static function diagCmdsSetTime($time)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'diag/cmds/set-time', [
            'query' => [
                'arg' => $time
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * diag/net
     *
     * @param string $format what format to use for output. Possible values: text, d3, dot
     */
    public static function diagNet($format)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'diag/net', [
            'query' => [
                'vis' => $format
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * diag/sys
     */
    public static function diagSys()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'diag/sys');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * dns
     *
     * @param string $domain DNS name
     * @param boolean $recursive resolve until the result is not a DNS link
     */
    public static function dns($domain, $recursive = false)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'dns', [
            'query' => [
                'arg' => $domain,
                'recursive' => $recursive
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * file/ls
     *
     * @param string $hash IPFS object to list links for
     */
    public static function fileLs($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'file/ls', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/rm
     *
     * @param string $apiFilePath file location in files API to remove
     * @param boolean $recursive is target a directory?
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesRm($apiFilePath, $recursive = false, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/rm', [
            'query' => [
                'arg' => $apiFilePath,
                'r' => $recursive,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/flush
     *
     * @param string $apiFilePath path to flush
     *
     * @todo check for difference in API action between no argument and empty argument
     */
    public static function filesFlush($apiFilePath)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/flush', [
            'query' => [
                'arg' => $apiFilePath
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/mv
     *
     * @param string $apiFilePath1 files api location source
     * @param string $apiFilePath2 files api location destination
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesMv($apiFilePath1, $apiFilePath2, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/mv', [
            'query' => [
                'arg' => $apiFilePath1,
                'arg2' => $apiFilePath2,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/cp
     *
     * @param string $apiFilePath1 files api location source
     * @param string $apiFilePath2 files api location destination
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesCp($apiFilePath1, $apiFilePath2, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/cp', [
            'query' => [
                'arg' => $apiFilePath1,
                'arg2' => $apiFilePath2,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/ls
     *
     * @param string $apiFilePath files api location to list links for
     * @param boolean $longFormat use long listing format
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesLs($apiFilePath, $longFormat = false, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/ls', [
            'query' => [
                'arg' => $apiFilePath,
                'l' => $longFormat,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/mkdir
     *
     * @param string $apiDirPath path to target directory
     * @param boolean $parents make parent directories if necessary
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesMkdir($apiDirPath, $parents = false, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/mkdir', [
            'query' => [
                'arg' => $apiDirPath,
                'p' => $parents,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/stat
     *
     * @param string $apiFilePath path to file to stat
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesStat($apiFilePath, $flush = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'files/stat', [
            'query' => [
                'arg' => $apiFilePath,
                'f' => $flush
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/read
     *
     * @param string $apiFilePath files api location to read
     * @param number $offset byte offset to begin reading from
     * @param number $count maximum number of bytes to read
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesRead($apiFilePath, $offset = 0, $count, $flush = true)
    {
        $client = self::getClient();
        if($count)
        {
            $response = $client->request('POST', 'files/read', [
                'query' => [
                    'arg' => $apiFilePath,
                    'o' => $offset,
                    'n' => $count,
                    'f' => $flush
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'files/read', [
                'query' => [
                    'arg' => $apiFilePath,
                    'o' => $offset,
                    'f' => $flush
                ]
            ]);
        }
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * files/write
     *
     * @param string $apiFilePath path to write data to
     * @param string $dataPath local data to write
     * @param number $offset byte offset to begin writing at
     * @param number $count maximum number of bytes to read
     * @param boolean $create create the file if it does not exist
     * @param boolean $truncate truncate the file to size zero before writing
     * @param boolean $flush flush target and ancestors after write
     */
    public static function filesWrite($apiFilePath, $dataPath, $offset = 0, $count, $create = true, $truncate = false, $flush = true)
    {
        $client = self::getClient();
        if($count)
        {
            $response = $client->request('POST', 'files/write', [
                'query' => [
                    'arg' => $apiFilePath,
                    'arg2' => $dataPath,
                    'o' => $offset,
                    'n' => $count,
                    'e' => $create,
                    't' => $truncate,
                    'f' => $flush
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'files/write', [
                'query' => [
                    'arg' => $apiFilePath,
                    'arg2' => $dataPath,
                    'o' => $offset,
                    'e' => $create,
                    't' => $truncate,
                    'f' => $flush
                ]
            ]);
        }
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * get
     *
     * @param string $hash IPFS hash to output data from
     * @param boolean $tar output tar
     * @param boolean $gzip output gzip
     * @param number $compression level of compression from 0-9
     */
    public static function get($hash, $tar = false, $gzip = false, $compression = -1)
    {
        $client = self::getClient();
        $response = $client->request('POST','get', [
            'query' => [
                'arg' => $hash,
                'archive' => $tar,
                'compress' => $gzip,
                'compression' => $compression
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * id
     *
     * @param string $peerID hash peerID of node to look up. If blank, outputs local ID
     */
    public static function id($peerID = "default")
    {
        $client = self::getClient();
        if($peerID === "default")
        {
            $selfInfo = shell_exec('ipfs id');
            $peerID = $selfInfo['ID'];
        }
        $response = $client->request('POST', 'id', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * log/level
     *
     * @param string $system which subsystem to output logging identifiers for. bitswap/blockstore/dht/merkledag/all
     * @param string $debugLevel what level of debugging to use. critical/error/warning/notice/info/debug
     */
    public static function logLevel($system, $debugLevel)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'log/level', [
            'query' => [
                'arg1' => $system,
                'arg2' => $debugLevel
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * log/ls
     */
    public static function logLs()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'log/ls');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * log/tail
     */
    public static function logTail()
    {
        $client = self::getClient();
        $response = $client->request('POST', 'log/tail');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * ls
     *
     * @param string $hash IPFS content hash
     * @param boolean $resolve resolve linked objects to find out their types
     */
    public static function ls($hash, $resolve = true)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'ls', [
            'query' => [
                'arg' => $hash,
                'resolve' => $resolve
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * name/publish
     *
     * @param string $hash IPFS path of the object to be published
     * @param boolean $resolve resolve given path before publishing
     * @param time $lifetime time duration that the record will be valid
     * @param time $ttl time duration this record should be cached for. EXPERIMENTAL AND NOT INCLUDED
     */
    public static function namePublish($hash, $resolve = true, $lifetime = '24h')
    {
        $client = self::getClient();
        if(!$hash)
        {
            $hash = self::id()['PublicKey'];
        }
        $response = $client->request('POST', 'name/publish', [
            'query' => [
                'arg' => $hash,
                'resolve' => $resolve,
                'lifetime' => $lifetime
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * name/resolve
     *
     * @param string $hash IPNS hash to resolve
     * @param boolean $recursive resolve until the result is not an IPNS name
     * @param boolean $nocache do not use cached entries
     */
    public static function nameResolve($hash, $recursive = false, $nocache = false)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'name/resolve', [
            'query' => [
                'arg' => $hash,
                'recursive' => $recursive,
                'nocache' => $nocache
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/data
     *
     * @param string $hash key of the object to retrieve
     */
    public static function objectData($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/data', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/diff
     *
     * @param string $hash1 hash of object to diff against
     * @param string $hash2 hash of object to diff
     */
    public static function objectDiff($hash1, $hash2)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/diff', [
            'query' => [
                'arg1' => $hash1,
                'arg2' => $hash2
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/get
     *
     * @param string $hash key of the object to retrieve
     * @param string $encoding serializes the DAG node to the format specified. Possible values: json, protobuf, xml
     */
    public static function objectGet($hash, $encoding = 'json')
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/get', [
            'query' => [
                'arg' => $hash,
                'encoding' => $encoding
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/links
     *
     * @param string $hash key of the object to retrieve
     */
    public static function objectLinks($hash)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/links', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/new
     *
     * @param $template template to use. Only possible value is unixfs (untested)
     */
    public static function objectNew($template = 'unixfs')
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/new', [
            'query' => [
                'template' => $template
            ]
        ]);
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        return $output;
    }

    /*
     * object/patch/append-data
     *
     * @param string $hash hash of the object to modify
     * @param string $dataPath path to data to append
     */
    public static function objectPathAppendData($hash, $dataPath)
    {
        $client = self::getClient();
        $response = $client->request('POST', 'object/patch/append-data', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'data_to_append_to_object',
                    'contents' => fopen(realpath($dataPath), "r")
                ]
            ],
            'query' => [
                'arg1' => $hash
            ]
        ]);
        $output = json_decode($response->getBody()->getContents(), true);
        return $output;
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
    public static function statsBw($peer = null, $proto = null, $poll = false, $interval = '1s')
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
        $client = self::getClient();
        $response = $client->request('POST', 'version');
        $output = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $output;
    }
}
?>