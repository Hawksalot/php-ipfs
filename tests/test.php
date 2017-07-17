<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use IpfsApi\Ipfs;

echo 'this is working';
echo Ipfs::version();