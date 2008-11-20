<?php

Mock::generate('Relax_Openssl_KeyStore','Relax_Openssl_MockKeyStore');
Mock::generate('Relax_Openssl_PublicKey','Relax_Openssl_MockPublicKey');
Mock::generate('Relax_Openssl_PrivateKey','Relax_Openssl_MockPrivateKey');

/**
 * @author Lachlan Donald <lachlan@99designs.com>
 */
class Relax_Client_OpensslTest extends UnitTestCase
{
	public function setUp()
	{
		$this->privateKey = new Relax_Openssl_MockPrivateKey();
		$this->publicKey = new Relax_Openssl_MockPublicKey();
		$this->keyStore = new Relax_Openssl_MockKeyStore();
	}

	public function testSigningARequest()
	{
		$this->privateKey->setReturnValue('getKeyId','test');
		$this->privateKey->setReturnValue('sign','hash');

		$this->privateKey->expectOnce('sign');

		$signer = new Relax_Openssl_Signer($this->privateKey, null, 10);
		$headers = $signer->sign('GET','/test');

		$this->assertContainsHeader($headers,'X-Signature-Hash',base64_encode('hash'));
		$this->assertContainsHeader($headers,'X-Signature-Key','test');
		$this->assertContainsHeader($headers,'X-Signature-Expires',20);
	}

	public function testVerifyingARequest()
	{
		$this->publicKey->setReturnValue('getKeyId','test');
		$this->publicKey->setReturnValue('verify',true);
		$this->publicKey->expectOnce('verify');
		$this->keyStore->setReturnValue('get',$this->publicKey);
		$this->keyStore->expectOnce('get',array('test'));

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

