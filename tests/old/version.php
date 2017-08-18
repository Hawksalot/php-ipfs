<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/Ipfs.php';

use PhpIpfs\Ipfs;

echo Ipfs::version();