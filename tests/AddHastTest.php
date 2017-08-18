<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use PhpIpfs\Ipfs;

class AddObjectTest extends TestCase
{
    public function testAdd()
    {
        $testResponse = Ipfs::add('testFiles/test.txt');
        $expectedResponse = [
            [
                'Name' => 'test.txt',
                'Hash' => 'QmSQsAdv3cCJuuL5rVc5iJ6nWtxMNWChuuNAEmuVVMCFTf'
            ]
        ];
        $this->assertEquals($testResponse, $expectedResponse);
    }
}