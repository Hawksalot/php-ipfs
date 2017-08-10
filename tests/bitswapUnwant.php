<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

// @todo figure out a useful set of test variables
echo Ipfs::bitswapUnwant('this is not a real argument');