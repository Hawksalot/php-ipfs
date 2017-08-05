<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

//echo Ipfs::version();

//echo Ipfs::addLocalObjectFromPath(realpath('testFiles/test.txt'), 'testFile');
//echo Ipfs::findRemoteProvidersByObjectHash('QmYwAPJzv5CZsnA625s3Xf2nemtYgPpHdWEz79ojWnPbdG');
/*bwStats = Ipfs::getBandwidthStats(null, '/ipfs/dht', true);
foreach($bwStats as $stat)
{
    echo $stat;
    print "\n";
}*/
//echo Ipfs::getLatencyToRemoteHost('QmQQwTjbQc5GhJsYjbynNRRgA1n2YK4dNcptmKUwJiDdmM');
echo Ipfs::addLocalObjectFromPath(realpath('testFiles/test.txt'), 'testDir', false, false, false, true, false);