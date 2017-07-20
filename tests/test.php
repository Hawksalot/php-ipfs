<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use IpfsApi\Ipfs;

echo Ipfs::version();

//echo Ipfs::addLocalFileFromPath(realpath('testFiles/test.txt'), 'testFile');
//echo Ipfs::findRemoteProvidersByObjectHash('QmYwAPJzv5CZsnA625s3Xf2nemtYgPpHdWEz79ojWnPbdG');
var_dump(Ipfs::getBandwidthStats());