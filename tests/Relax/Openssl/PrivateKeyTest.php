<?php

class Relax_Openssl_PrivateKeyTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->privateKey = tmpfile();
        $this->passPhrase = 'llamas love long passphrases';

        $this->pkey = openssl_pkey_new();
        openssl_pkey_export($this->pkey, $pkey, $this->passPhrase);
        fwrite($this->privateKey, $pkey);
        fflush($this->privateKey);
        $pkey_metadata = stream_get_meta_data($this->privateKey);
        $this->privateKeyPath = $pkey_metadata['uri'];
    }

    public function testValidPassphrase()
    {
        $pkey = new Relax_Openssl_PrivateKey(
            $this->privateKeyPath, false,
            $this->passPhrase
        );

        $this->assertTrue($pkey->isValid());
    }

    public function testInvaludPassphrase()
    {
        $pkey = new Relax_Openssl_PrivateKey(
            $this->privateKeyPath, false,
            'llamas hate invalid passphrases'
        );

        $this->assertFalse($pkey->isValid());
    }
}
