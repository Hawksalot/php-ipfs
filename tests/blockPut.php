<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

// @todo create test hash
echo Ipfs::blockPut('this/is/not/a/real/argument');