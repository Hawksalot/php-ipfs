<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

// @todo figure out a way to dynamically generate a test value for $peer
echo Ipfs::bitswapWantlist('this is not a real argument');