<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

// @todo dynamically get peer hash to test
echo Ipfs::bootstrapRm('tEsTpEeRhAsH');
echo Ipfs::bootstrapRm('all');