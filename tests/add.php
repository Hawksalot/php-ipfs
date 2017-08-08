<?php

require_once __DIR__ . '/../vendor/autoload.php';
require '../src/IpfsApi.php';

use PhpIpfs\Ipfs;

// tests only hashing and not actually adding
echo "just hashing\n";
echo Ipfs::add('testFiles/test.txt', $n = true);

// tests basic add
echo "basic add\n";
echo Ipfs::add('testFiles/test.txt');

// tests hidden file add
echo "hidden with no H flag\n";
echo Ipfs::add('testFiles/.test.txt');
echo "then with it\n";
echo Ipfs::add('testFiles/.test.txt', $H = true);
// @todo progress flag tests

// @todo pin flag tests

// tests recursive add
echo "dir with no r flag\n";
echo Ipfs::add('./testFiles');
echo "then with it\n";
echo Ipfs::add('./testFiles', $r = true);

// @todo trickle tests

// tests wrap-with-directory
echo "wrap-with-directory\n";
echo Ipfs::add('testFiles/test.txt', $w = true);