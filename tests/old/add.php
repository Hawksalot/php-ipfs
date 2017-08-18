<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require '../../src/Ipfs.php';

use PhpIpfs\Ipfs;

$array = Ipfs::add('../testFiles/test.txt');

foreach($array as $key => $value)
{
   echo $key. " = ". $value."\n";
}