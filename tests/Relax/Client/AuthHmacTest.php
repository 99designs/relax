<?php

/**
 * @author Paul Annesley
 */
class Relax_Client_AuthHmacTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // a known-good date and header for this request signed with "the_secret"
        date_default_timezone_set('UTC');
        $this->timeFunc = function() {
            return strtotime("Tue, 22 Feb 2011 00:00:00 GMT");
        };
    }

    public function test_sign_and_verify_request()
    {
        $request = $this->_request('POST', '/path', "the\nbody");
        $expected_header = 'AuthHMAC test:jdVqXv9UZGWktvk/3YGx8dktjLc=';

        $filter = $this->_filter();
        $filter->request($request);

        $headers = $request->getHeaders();

        $this->assertEquals($headers->value('Authorization'), $expected_header);
        $this->assertEquals($headers->value('Date'), 'Tue, 22 Feb 2011 00:00:00 GMT');

        $this->assertTrue($filter->verify($request), '%s: $filter->verify($request)');
    }

    public function test_sign_without_access_id_fails()
    {
        $filter = new Relax_Openssl_AuthHmac(array("test" => "the_secret"), NULL, $this->timeFunc);
        $this->_expectSigningException();
        $filter->request($this->_request());
    }

    public function test_unsigned_request_verify_fails()
    {
        $this->_expectSigningException($this->_request());
    }

    public function test_invalid_authorization_header()
    {
        $request = $this->_request();
        $request->getHeaders()->add('Authorization: something');
        $this->_expectSigningException($request);
    }

    public function test_invalid_authorization_type()
    {
        $request = $this->_request();
        $request->getHeaders()->add('Authorization: Foreign id:signature');
        $this->_expectSigningException($request);
    }

    public function test_invalid_signature()
    {
        $request = $this->_request();
        $request->getHeaders()->add('Authorization: AuthHMAC test:incorrect');
        $this->_expectSigningException($request);
    }

    // ----------------------------------------

    private function _filter()
    {
        return new Relax_Openssl_AuthHmac(array("test" => "the_secret"), "test",
            $this->timeFunc
        );
    }

    private function _request($method = 'GET', $path = '/', $body = null)
    {
        $headers = array('Content-Type: application/json');
        if ($body) $headers []= sprintf('Content-MD5: %s', md5($body));
        return new \Ergo\Http\Request(
            $method,
            new \Ergo\Http\Url("http://example.org$path"),
            $headers,
            $body
        );
    }

    private function _expectSigningException($request = null)
    {
        $this->setExpectedException('Relax_Openssl_SigningException');
        if ($request) $this->_filter()->verify($request);
    }
}
