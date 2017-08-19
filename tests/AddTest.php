<?php

use PHPUnit\Framework\TestCase;
use PhpIpfs\Ipfs;

class AddTest extends TestCase
{
    public function testDefaultAdd()
    {
        $testResponse = Ipfs::add('tests/testFiles/test.txt');
        $expectedResponse = [
                'Name' => 'test.txt',
                'Hash' => 'QmSQsAdv3cCJuuL5rVc5iJ6nWtxMNWChuuNAEmuVVMCFTf'
        ];
        $this->assertEquals($testResponse, $expectedResponse);
    }
}
?>