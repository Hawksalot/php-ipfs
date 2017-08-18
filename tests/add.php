<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

$array = Ipfs::add('testFiles/test.txt');