<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/Ipfs.php';

use PhpIpfs\Ipfs;

$stats = Ipfs::bitswapStat();
foreach($stats as $key => $stat)
{
    echo $key." = ".$stat."\n";
}