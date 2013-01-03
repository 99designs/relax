<?php

class Relax_OpensslTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->privateKey = Mockery::mock();
		$this->publicKey = Mockery::mock();
		$this->keyStore = Mockery::mock();
	}

	public function testSigningARequest()
	{
		$this->privateKey->shouldReceive('getKeyId')->andReturn('test');
		$this->privateKey->shouldReceive('sign')->andReturn('hash')->once();

		$signer = new Relax_Openssl_Signer($this->privateKey, null, 10);
		$headers = $signer->sign('GET','/test');

		$this->assertContainsHeader($headers,'X-Signature-Hash',base64_encode('hash'));
		$this->assertContainsHeader($headers,'X-Signature-Key','test');
		$this->assertContainsHeader($headers,'X-Signature-Expires',20);
	}

	public function testVerifyingARequest()
	{
		$this->publicKey->shouldReceive('getKeyId')->andReturn('test');
		$this->publicKey->shouldReceive('verify')->andReturn(true)->once();
		$this->keyStore->shouldReceive('get')->with('test')->andReturn($this->publicKey)->once();

		$headers = array(
			'X-Signature-Hash: testhash',
			'X-Signature-Expires: 10',
			'X-Signature-Key: test',
			);

		$signer = new Relax_Openssl_Signer(false, $this->keyStore);
		$signer->verify($headers,'GET','/test',null);
	}

	public function assertContainsHeader($headers,$header,$key=false)
	{
		$keyPattern = $key ? preg_quote($key,'/') : '(.+?)';
		$pattern = '/^'.preg_quote($header,'/').':\s*'.$keyPattern.'$/';

		if(count(preg_grep($pattern,$headers)) > 0)
		{
			$this->assertTrue(true,"contains header $header");
		}
		else
		{
			$this->fail("Doesn't contain header [$header: $key]");
		}
	}
}

