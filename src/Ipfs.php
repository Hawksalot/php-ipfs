<?php
/*
 * PHP implementation of IPFS HTTP API functions
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
    private static function setClient()
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
     *
     */
    /*private static function getRequestArgs($uri, $file = false, $query = false, $options = false)
    {
        if($file !== false)
        {
            $multipart = [
                'Content-Type' => 'multipart/formdata',
                'name' => $uri,
                'contents' => $file
            ];
        }
        else
        {
            $multipart = null;
        }
        if($query !== false)
        {
            $qry
        }
        $miscArgs = [
            $multipart,

        ];
        $args = [
            'POST',
            $uri,
            $miscArgs
        ];
        return $args;
    }*/

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
     * add
     *
     * adds file or directory to local IPFS node
     *
     * @param string $objectPatch /path/to/local/file
     * @param boolean $hidden Hidden. Include files that are hidden. Only takes effect on recursive add
     * @param boolean $onlyHash Only-hash. Only chunk and hash - do not write to disk. can be tested by running ipfs pin ls (I think)
     * @param boolean $progress Progress. Stream progress data. TEMPORARILY SET TO ALWAYS FALSE
     * @param boolean $pin Pin this object when adding. @todo test
     * @param boolean $recursive Recursive. Add directory paths recursively. @todo get function to stream directory and directory contents
     * @param boolean $trickle Trickle. Use trickle-dag format for dag generation.
     * @param boolean $wrap Wrap-with-directory. Wrap files with a directory object.
     *
     * @return string Hash of added file
     */
    public static function add($objectPath, $hidden = false, $onlyHash = false, $progress = true, $pin = true, $recursive = false, $trickle = false, $wrap = false)
    {
	    $client = self::setClient();
        $response = $client->request('POST', 'add', [
	        'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'object_to_add',
                    'contents' => fopen(realpath($objectPath), "r")
                ]
            ],
	        'query' => [
	            'H' => $hidden,
	            'n' => $onlyHash,
                'p' => false,
                'pin' => $pin,
                'r' => $recursive,
                't' => $trickle,
                'w' => $wrap
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bitswap/ledger
     *
     * show the current ledger for a peer
     *
     * @param string $peerID peer ID of ledger to inspect (hash/full location)?
     */
    public static function bitswapLedger($peerID)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'bitswap/ledger', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bitswap/stat
     *
     * show some diagnostic information on the bitswap agent
     */
    public static function bitswapStat()
    {
        return self::statsBitswap();
    }

    /*
     * bitswap/unwant
     *
     * remove a given block from your wantlist
     *
     * @param string $hash
     */
    public static function bitswapUnwant(...$hash)
    {
        $client = self::setClient();
        foreach($hash as $arg)
        {
            $response = $client->request('POST', 'bitswap/unwant', [
                'query' => [
                    'arg' => $arg
                ]
            ]);
        }
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bitswap/wantlist
     *
     * show blocks currently on the wantlist
     */
    public static function bitswapWantlist($peer)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'bitswap/wantlist', [
            'query' => [
                'peer' => $peer
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * block/get
     *
     * get a raw IPFS block
     *
     * @param string $hash the b58 multihash of an existing block to get
     */
    public static function blockGet($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'block/get', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * block/put
     *
     * store input as an IPFS block
     *
     * @param string $dataPath /path/to/data to store as block
     * @param string $format CID format to use for block creation
     * @param string $mhtype multihash hash function
     * @param number $mhlen multihash hash length
     */
    public static function blockPut($dataPath, $format = 'v0', $mhtype = 'sha2-256', $mhlen = '-1')
    {
        $client = self::setClient();
        $response = $client->request('POST', 'block/put', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'raw_block_data',
                    'content' => fopen(realpath($dataPath), "r")
                ]
            ],
            'query' => [
                'format' => $format,
                'mhtype' => $mhtype,
                'mhlen' => $mhlen
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * block/rm
     *
     * remove IPFS block(s)
     *
     * @param string $hash hash of block(s) to remove
     * @param boolean $force ignore nonexistent blocks
     */
    public static function blockRm($force = false, ...$hash)
    {
        $client = self::setClient();
        foreach($hash as $arg)
        {
            $response = $client->request('POST', 'block/rm', [
                'query' => [
                    'arg' => $arg,
                    'force' => $force
                ]
            ]);
        }
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * block/stat
     *
     * print information of a raw IPFS block
     */
    public static function blockStat($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'block/stat', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bootstrap/add/default
     *
     * add default peers to the bootstrap list
     */
    public static function bootstrapAddDefault()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'bootstrap/add/default');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bootstrap/list
     *
     * show peers in the bootstrap list
     */
    public static function bootstrapList()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'bootstrap/list');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * bootstrap/rm
     *
     * remove peers from the bootstrap list
     */
    public static function boostrapRmAll()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'bootstrap/rm/all');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * cat
     *
     * show IPFS object data
     *
     * @param string $hash the hash of the IPFS content to be pulled
     */
    public static function cat($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'cat', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * commands
     *
     * list all available commands
     *
     * @param boolean $flags show command flags
     */
    public static function commands($flags = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'commands', [
            'query' => [
                'flags' => $flags
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * config/replace
     *
     * replace config with local file
     *
     * @param string $configFilePath local location of new config file
     */
    public static function configReplace($configFilePath)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'config/replace', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'new_config_file',
                    'contents' => fopen(realpath($configFilePath), "r")
                ]
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * config/show
     *
     * output config file contents
     */
    public static function configShow()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'config/show');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dat/get
     *
     * get a DAG node from IPFS
     *
     * $param string $hash the object to get
     */
    public static function dagGet($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dag/get', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dag/put
     *
     * add a DAG node to IPFS
     *
     * @param string $filePath the object to put
     * @param string $format format that the object will be added as
     * @param string $encoding format that the input object will be
     */
    public static function dagPut($filePath, $format = 'cbor', $encoding = 'json')
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dag/put', [
            'multipart' => [
                [
                    'Content-Type' => 'multipart/formdata',
                    'name' => 'object_to_put_as_dag',
                    'contents' => fopen(realpath($filePath), "r")
                ]
            ],
            'query' => [
                'format' => $format,
                'input-enc' => $encoding
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/findpeer
     *
     * query the DHT for all of the multiaddresses
     * associated with a peer ID
     *
     * @param string $peerID hash of peer to target
     * @param boolean $verbose print extra information
     */
    public static function dhtFindpeer($peerID, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dht/findpeer', [
            'query' => [
                'arg' => $peerID,
                'verbose' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/findprovs: finds remote IPFS nodes that are hosting target Merkle-Dag hash
     *
     * find peers in the DHT that can provide the value for a given key
     *
     * @param string $hash IPFS content hash to find providers for
     * @param boolean $verbose print extra information
     */
    public static function dhtFindprovs($hash, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dht/findprovs', [
           'query' => [
               'arg' => $hash,
               'verbose' => $verbose
           ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/get
     *
     * given a key, query the DHT for its best value
     *
     * @param string $hash content hash to find providers for
     * @param boolean $verbose print extra information
     */
    public static function dhtGet($hash, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dht/get', [
            'query' => [
                'arg' => $hash,
                'verbose' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/provide
     *
     * announce to the network that you are providing given values
     *
     * @param string $hash the key(s) to provide records for
     * @param boolean $recursive recursively provide entire graph
     * @param boolean $verbose print extra information
     */
    public static function dhtProvide($verbose = false, $recursive = false, ...$hash)
    {
        $client = self::setClient();
        foreach($hash as $arg)
        {
            $response = $client->request('POST', 'dht/provide', [
                'query' => [
                    'arg' => $arg,
                    'recursive' => $recursive,
                    'verbose' => $verbose
                ]
            ]);
        }
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/put
     *
     * write a key-value pair to the DHT
     *
     * @param string $key key part of key-value pair to write to IPFS
     * @param string $value value part of key-value
     * @param boolean $verbose print extra information
     *
     * @todo test that using 'arg1' and 'arg2' actually works
     */
    public static function dhtPut($key, $value, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dht/put', [
            'query' => [
                'arg1' => $key,
                'arg2' => $value,
                'verbose' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dht/query
     *
     * find the closest peer IDs to a given peer ID by querying the DHT
     *
     * @param string $peerID hash of peer to find similar peers for
     * @param boolean $verbose print extra information
     */
    public static function dhtQuery($peerID, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dht/query', [
            'query' => [
                'arg' => $peerID,
                'verbose' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * diag/cmds/clear
     *
     * clear inactive requests from the log
     */
    public static function diagCmdsClear()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'diag/cmds/clear');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * diag/cmds/set-time
     *
     * set how long to keep inactive requests in the log
     *
     * @param string $time time to keep inactive requests in log
     */
    public static function diagCmdsSetTime($time)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'diag/cmds/set-time', [
            'query' => [
                'arg' => $time
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * diag/net
     *
     * generate a network diagnostics report
     *
     * @param string $format what format to use for output. Possible values: text, d3, dot
     */
    public static function diagNet($format = 'text')
    {
        $client = self::setClient();
        $response = $client->request('POST', 'diag/net', [
            'query' => [
                'vis' => $format
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * diag/sys
     *
     * print system diagnostic information
     */
    public static function diagSys()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'diag/sys');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * dns
     *
     * resolve DNS links
     *
     * @param string $domainName domain name to resolve
     * @param boolean $recursive resolve until the result is not a DNS link
     */
    public static function dns($domainName, $recursive = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'dns', [
            'query' => [
                'arg' => $domainName,
                'recursive' => $recursive
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * file/ls
     *
     * list directory contents for Unix filesystem objects
     *
     * @param string $hash IPFS object to list links for
     */
    public static function fileLs($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'file/ls', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/cp
     *
     * copy files into MFS
     *
     * @param string $apiFilePath1 files api location source
     * @param string $apiFilePath2 files api location destination
     */
    public static function filesCp($apiFilePath1, $apiFilePath2)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/cp', [
            'query' => [
                'arg' => $apiFilePath1,
                'arg2' => $apiFilePath2
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/flush
     *
     * flush a given path's data to disk
     *
     * @param string $apiFilePath path to flush
     *
     * @todo check for difference in API action between no argument and empty argument
     */
    public static function filesFlush($apiFilePath)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/flush', [
            'query' => [
                'arg' => $apiFilePath
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/ls
     *
     * list directories in the local mutable namespace
     *
     * @param string $apiFilePath files api location to list links for
     * @param boolean $longFormat use long listing format
     */
    public static function filesLs($apiFilePath = '/', $longFormat = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/ls', [
            'query' => [
                'arg' => $apiFilePath,
                'l' => $longFormat
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/mkdir
     *
     * make directory
     *
     * @param string $apiDirPath path to target directory
     * @param boolean $parents make parent directories if necessary
     */
    public static function filesMkdir($apiDirPath, $parents = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/mkdir', [
            'query' => [
                'arg' => $apiDirPath,
                'parents' => $parents
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/mv
     *
     * @param string $apiFilePath1 files api location source
     * @param string $apiFilePath2 files api location destination
     */
    public static function filesMv($apiFilePath1, $apiFilePath2)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/mv', [
            'query' => [
                'arg' => $apiFilePath1,
                'arg2' => $apiFilePath2
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
 * files/read
 *
 * @param string $apiFilePath files api location to read
 * @param number $offset byte offset to begin reading from
 * @param number $count maximum number of bytes to read
 */
    public static function filesRead($apiFilePath, $offset = 0, $count = false
    {
        $client = self::setClient();
        if($count !== false)
        {
            $response = $client->request('POST', 'files/read', [
                'query' => [
                    'arg' => $apiFilePath,
                    'o' => $offset,
                    'n' => $count
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'files/read', [
                'query' => [
                    'arg' => $apiFilePath,
                    'o' => $offset
                ]
            ]);
        }
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/rm
     *
     * @param string $apiFilePath file location in files API to remove
     * @param boolean $recursive recursively remove directories
     */
    public static function filesRm($apiFilePath, $recursive = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/rm', [
            'query' => [
                'arg' => $apiFilePath,
                'r' => $recursive
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * files/stat
     *
     * @param string $apiFilePath path to file to stat
     * @param string $format print statistics in given format
     * @param boolean $hash print only hash
     * @param boolean $size print only size
     *
     * @todo check this in API Go code, might be broken as indicated by documentation
     */
    public static function filesStat($apiFilePath, $format = false, $hash = false, $size = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'files/stat', [
            'query' => [
                'arg' => $apiFilePath,
                'format' => $format,
                'hash' => $hash,
                'size' => $size
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
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
    public static function filesWrite($apiFilePath, $dataPath, $offset = 0, $count, $create = true, $truncate = false)
    {
        $client = self::setClient();
        if($count !== false)
        {
            $response = $client->request('POST', 'files/write', [
                'query' => [
                    'arg1' => $apiFilePath,
                    'arg2' => $dataPath,
                    'offset' => $offset,
                    'count' => $count,
                    'create' => $create,
                    'truncate' => $truncate
                ]
            ]);
        }
        else
        {
            $response = $client->request('POST', 'files/write', [
                'query' => [
                    'arg1' => $apiFilePath,
                    'arg2' => $dataPath,
                    'offset' => $offset,
                    'create' => $create,
                    'truncate' => $truncate
                ]
            ]);
        }
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * filestore/dups
     *
     * list blocks that are both in the filestore and standard block storage
     */
    public static function filestoreDups()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'filestore/dups');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * filestore/ls
     *
     * list objects in filestore
     *
     * @param string $object CID of object to list
     */
    public static function filestoreLs($object = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'filestore/ls', [
            'query' => [
                'arg' => $object
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * filestore/verify
     *
     * verify objects in filestore
     *
     * @param string $object CID of object to verify
     */
    public static function filestoreVerify($object = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'filestore/verify', [
            'query' => [
                'arg' => $object
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * get
     *
     * download IPFS object
     *
     * @param string $hash IPFS hash to output data from
     * @param string $localLocation the path to where the output should be stored
     * @param boolean $tar output tar
     * @param boolean $gzip output gzip
     * @param number $compression level of compression from 0-9
     */
    public static function get($hash, $localLocation = false, $tar = false, $gzip = false, $compression = -1)
    {
        $client = self::setClient();
        $response = $client->request('POST','get', [
            'query' => [
                'arg' => $hash,
                'output' => $localLocation,
                'archive' => $tar,
                'compress' => $gzip,
                'compression' => $compression
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * id
     *
     * @param string $peerID hash peerID of node to look up
     * @param string $format optional output format
     */
    public static function id($peerID = false, $format = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'id', [
            'query' => [
                'arg' => $peerID,
                'format' => $format
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * key/gen
     *
     * create a new keypair
     *
     * @param string $keyName name of key to create
     * @param string $type type of the key to create. Possible values: rsa, ed25519
     * @param number $size size of the key to create
     */
    public static function keyGen($keyName, $type = false, $size = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'key/gen', [
            'query' => [
                'arg' => $keyName,
                'type' => $type,
                'size' => $size
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * key/list
     *
     * list all local keypairs
     *
     * @param boolean $verbose show extra information about keys
     */
    public static function keyList($verbose)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'key/list', [
            'query' => [
                'l' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * log/level
     *
     * change the logging level
     *
     * @param string $system which subsystem to output logging identifiers for. bitswap/blockstore/dht/merkledag/all
     * @param string $debugLevel what level of debugging to use. critical/error/warning/notice/info/debug
     */
    public static function logLevel($system, $debugLevel)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'log/level', [
            'query' => [
                'arg1' => $system,
                'arg2' => $debugLevel
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * log/ls
     *
     * list the logging subsystems
     */
    public static function logLs()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'log/ls');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * log/tail
     *
     * read the event log
     */
    public static function logTail()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'log/tail');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * ls
     *
     * list directory contents for Unix filesystem objects
     *
     * @param string $hash IPFS content hash
     * @param boolean $headers print table headers
     * @param boolean $resolve resolve linked objects to find out their types
     */
    public static function ls($hash, $headers = false, $resolve = true)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'ls', [
            'query' => [
                'arg' => $hash,
                'headers' => $headers,
                'resolve' => $resolve
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * mount
     *
     * mounts IPFS to the filesystem
     *
     * @param string $ipfsMountPath /path/to/ipfs/mount/point
     * @param string $ipnsMountPath /path/to/ipns/mount/point
     */
    public static function mount($ipfsMountPath, $ipnsMountPath)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'mount', [
            'query' => [
                'ipfs-path' => $ipfsMountPath,
                'ipns-path' => $ipnsMountPath
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * name/publish
     *
     * publish IPNS names
     *
     * @param string $hash IPFS path of the object to be published
     * @param boolean $resolve resolve given path before publishing
     * @param time $lifetime time duration that the record will be valid
     * @param string $ttl time duration this record should be cached for
     * @param string $key name of the key to be used as listed by key list
     */
    public static function namePublish($hash, $resolve = true, $lifetime = false, $ttl = false, $key = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'name/publish', [
            'query' => [
                'arg' => $hash,
                'resolve' => $resolve,
                'lifetime' => $lifetime,
                'ttl' => $ttl,
                'key' => $key
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * name/resolve
     *
     * resolve IPNS names
     *
     * @param string $hash IPNS hash to resolve
     * @param boolean $recursive resolve until the result is not an IPNS name
     * @param boolean $nocache do not use cached entries
     */
    public static function nameResolve($hash, $recursive = false, $nocache = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'name/resolve', [
            'query' => [
                'arg' => $hash,
                'recursive' => $recursive,
                'nocache' => $nocache
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/data
     *
     * output the raw bytes of an IPFS object
     *
     * @param string $hash key of the object to retrieve
     */
    public static function objectData($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/data', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/diff
     *
     * display the differences between 2 IPFS objects
     *
     * @param string $hash1 hash of object to diff against
     * @param string $hash2 hash of object to diff
     * @param boolean $verbose print extra information
     */
    public static function objectDiff($hash1, $hash2, $verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/diff', [
            'query' => [
                'arg1' => $hash1,
                'arg2' => $hash2,
                'verbose' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/get
     *
     * get and serialize the DAG node named by $hash
     *
     * @param string $hash key of the object to retrieve
     */
    public static function objectGet($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/get', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/links
     *
     * output the links pointed to by the object specified by $hash
     *
     * @param string $hash key of the object to retrieve
     * @param boolean $headers print table headers (hash, size, name)
     */
    public static function objectLinks($hash, $headers = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/links', [
            'query' => [
                'arg' => $hash,
                'headers' => $headers
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/new
     *
     * create a new object from an IPFS template
     *
     * @param $template template to use
     */
    public static function objectNew($template = 'unixfs')
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/new', [
            'query' => [
                'arg' => $template
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/patch/add-link
     *
     * add a link to a given object
     *
     * @param string $node hash of the node to modify
     * @param string $name name of link to create
     * @param string $hash IPFS object to link to
     * @param boolean $create create intermediary nodes
     */
    public static function objectPatchAddLink($node, $name, $hash, $create = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/patch/add-link', [
            'query' => [
                'arg1' => $node,
                'arg2' => $name,
                'arg3' => $hash,
                'create' => $create
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/patch/append-data
     *
     * append data to the data segment of a DAG node
     *
     * @param string $hash hash of the object to modify
     * @param string $dataPath path to data to append
     */
    public static function objectPatchAppendData($hash, $dataPath)
    {
        $client = self::setClient();
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
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/patch/rm-link
     *
     * remove a link from an object
     *
     * @param string $node hash of the node to modify
     * @param string $name name of the link to remove
     */
    public static function objectPatchRmLink($node, $name)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/patch/rm-link',[
            'query' => [
                'arg1' => $node,
                'arg2' => $name
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/patch/set-data
     *
     * set the data field of an IPFS object
     *
     * @param string $node hash of the node to modify
     * @param string $dataPath data to set the object to
     */
    public static function objectPatchSetData($node, $dataPath)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/patch/set-data', [
            'multipart' => [
                'Content-Type' => 'multipart/formdata',
                'name' => 'data_to_set',
                'contents' => fopen(realpath($dataPath), "r")
            ],
            'query' => [
                'arg' => $node
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/put
     *
     * store input as a DAG object, print its key
     *
     * @param string $dataPath data to be stored as a DAG object
     * @param string $encoding encoding type of input data. Possible values: protobuf, json
     */
    public static function objectPut($dataPath, $encoding = 'json')
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/put', [
            'multipart' => [
                'Content-Type' => 'multipart/formdata',
                'name' => 'data_to_store',
                'contents' => fopen(realpath($dataPath), "r")
            ],
            'query' => [
                'inputenc' => $encoding
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * object/stat
     *
     * get stats for the DAG node named by $hash
     *
     * @param string $hash key of the object to retreieve
     */
    public static function objectStat($hash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'object/stat', [
            'query' => [
                'arg' => $hash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pin/add
     *
     * pin object to local storage
     *
     * @param string $hash object to pin
     * @param boolean $recursive recursively pin the objects linked to by the specified object
     * @param boolean $progress show progress
     */
    public static function pinAdd($hash, $recursive = true, $progress = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pin/add', [
            'query' => [
                'arg' => $hash,
                'recursive' => $recursive,
                'progress' => $progress
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pin/ls
     *
     * @param string $hash path to object to be listed
     * @param string $type type of pinned keys to list. Possible values: all, direct, indirect, recursive
     * @param boolean $quiet write just hashes of objects
     */
    public static function pinLs($hash, $type = 'all', $quiet = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pin/ls', [
            'query' => [
                'arg' => $hash,
                'type' => $type,
                'quiet' => $quiet
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pin/rm
     *
     * remove pinned object from local storage
     *
     * @param string $hash IPFS object to be unpinned
     * @param boolean $recursive recursively unpin linked objects
     */
    public static function pinRm($hash, $recursive = true)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pin/rm', [
            'query' => [
                'arg' => $hash,
                'recursive' => $recursive
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * ping
     *
     * send echo request packets to IPFS hosts
     *
     * @param string $peer hash of peer to ping
     * @param number $count number of ping messages to send
     */
    public static function ping($peer, $count = 10)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'ping', [
            'query' => [
                'arg' => $peer,
                'count' => $count
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pubsub/ls
     *
     * list subscribed topics by name
     */
    public static function pubsubLS()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pubsub/ls');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pubsub/peers
     *
     * list peers the local node is currently pubsubbing with
     *
     * @param string $topic topic to list connected peers of
     */
    public static function pubsubPeers($topic = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pubsub/peers', [
            'query' => [
                'arg' => $topic
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pubsub/pub
     *
     * publish a message to a given pubsub topic
     *
     * @param string $topic topic to publish to
     * @param string $message payload of message
     */
    public static function pubsubPub($topic, $message)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pubsub/pub', [
            'query' => [
                'arg1' => $topic,
                'arg2' => $message
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * pubsub/sub
     *
     * subscribe to messages on a given topic
     *
     * @param string $topic name of topic to subscribe to
     * @param boolean $discover try to discover other peers subscribed to the same topic
     */
    public static function pubsubSub($topic, $discover = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'pubsub/sub', [
            'query' = [
                'arg' => $topic,
                'discover' => $discover
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * refs/local
     *
     * list all local references
     */
    public static function refsLocal()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'refs/local');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * repo/fsck
     *
     * remove repo lockfiles
     */
    public static function repoFsck()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'repo/fsck');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * repo/gc
     *
     * perform a garbage collection sweep on the repo
     *
     * @param boolean $errors stream errors
     */
    public static function repoGc($errors = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'repo/gc', [
            'query' => [
                'stream-errors' => $errors
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * repo/stat
     *
     * get stats for the currently used repo
     *
     * @param boolean $human output repo size in MiB
     */
    public static function repoStat($human = false)
    {
        return self::statsRepo($human);
    }

    /*
     * repo/verify
     *
     * verify all blocks in repo are not corrupted
     */
    public static function repoVerify()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'repo/verify');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * repo/version
     *
     * show the repo version
     */
    public static function repoVersion()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'repo/version');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * resolve
     *
     * resolve the value of names in IPFS
     *
     * @param string $hash IPFS object to resolve
     * @param boolean $recursive resolve until the result is a (direct?) object
     */
    public static function resolve($hash, $recursive = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'resolve', [
            'query' => [
                'arg' => $hash,
                'recursive' => $recursive
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * stats/bitswap
     *
     * show some diagnostic information on the bitswap agent
     */
    public static function statsBitswap()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'stats/bitswap');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * stats/bw: prints ipfs bandwidth information
     *
     * Call $peer or $proto but not both!
     *
     * @param string $peer peer id
     * @param string $proto protocol to print bw stats for
     * @param boolean $poll print bandwidth at an interval
     * @param string $interval time interval to wait between updating output if poll is true
     *
     * @return array
     */
    public static function statsBw($peer = null, $proto = null, $poll = false, $interval = '1s')
    {
        $client = self::setClient();
        // @todo dynamically generate request with peer or protocol
        // @todo must run with no arguments or with either but not both
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * stats/repo
     *
     * get stats for the currently used repo
     *
     * @param boolean $human output repo size in MiB
     */
    public static function statsRepo($human = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'stats/repo', [
            'query' => [
                'human' => $human
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/addrs/local
     *
     * list local addresses
     *
     * @param boolean $id show peer ID in addresses
     */
    public static function swarmAddrsLocal($id = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/addrs/local', [
            'query' => [
                'id' => $id
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/connect
     *
     * open connection to a given address
     *
     * @param string $peerID peer to connect to in /ip4/$ip/tcp/$port/ipfs/$hash format
     */
    public static function swarmConnect($peerID)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/connect', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/disconnect
     *
     * close connection to an address specified by $peerID
     *
     * param string $peerID peer to connect to in /ip4/$ip/tcp/$port/ipfs/$hash format
     */
    public static function swarmDisconnect($peerID)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/disconnect', [
            'query' => [
                'arg' => $peerID
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/filters
     */
    public static function swarmFilters()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/filters');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/filters/add
     *
     * @param string $address address to add to filter list in format (/ip4/$ip/ipcidr/16)?
     */
    public static function swarmFiltersAdd($address)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/filters/add', [
            'query' => [
                'arg' => $address
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/filters/rm
     *
     * @param string $address address to add to filter list in format (/ip4/$ip/ipcidr/16)?
     */
    public static function swarmFiltersRm($address)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/filters/rm', [
            'query' => [
                'arg' => $address
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * swarm/peers
     *
     * @param boolean $verbose also display latency along with peer info
     */
    public static function swarmPeers($verbose = false)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'swarm/peers', [
            'query' => [
                'v' => $verbose
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
    * tar/add
    *
    * @param string $tarPath /path/to/tar
    */
    public static function tarAdd($tarPath)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tar/add', [
            'multipart' => [
                'Content-Type' => 'multipart/formdata',
                'name' => 'tar_to_add',
                'contents' => fopen(realpath($tarPath), "r")
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * tar/cat
     *
     * @param string $tarHash IPFS object that is a tar
     */
    public static function tarCat($tarHash)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tar/cat', [
            'query' => [
                'arg' => $tarHash
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * tour
     *
     * @param string $section tour section to go to
     */
    public static function tour($section)
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tour', [
            'query' => [
                'arg' => $section
            ]
        ]);
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * tour/list
     */
    public static function tourList()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tour/list');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * tour/next
     */
    public static function tourNext()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tour/next');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * tour/restart
     */
    public static function tourRestart()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'tour/restart');
        return self::getReturnContent($response->getBody()->getContents());
    }

    /*
     * queries local ipfs node for ipfs version
     *
     * @return string indicates ipfs version running on queried ipfs node
     */
    public static function version()
    {
        $client = self::setClient();
        $response = $client->request('POST', 'version');
        return self::getReturnContent($response->getBody()->getContents());
    }
}
?>