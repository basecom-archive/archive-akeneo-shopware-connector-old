<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Api;

/**
 * Shopware ApiClient. Provides the functions for API calls.
 *
 * Class ApiClient
 */
class ApiClient
{
    const METHOD_GET    = 'GET';
    const METHOD_PUT    = 'PUT';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';
    /**
     * Holds all valid methodes for API calls.
     *
     * @var array
     */
    protected $validMethods = [
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE
    ];
    protected $apiUrl;
    protected $cURL;

    /**
     * ApiClient constructor.
     *
     * @param $apiUrl
     * @param $username
     * @param $apiKey
     */
    public function __construct($apiUrl, $username, $apiKey) {
    $this->apiUrl = rtrim($apiUrl, '/') . '/';
    //Initializes the cURL instance
    $this->cURL = curl_init();
    curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($this->cURL, CURLOPT_USERAGENT, 'Shopware ApiClient');
    curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($this->cURL, CURLOPT_USERPWD, $username . ':' . $apiKey);
    curl_setopt($this->cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; 
         charset=utf-8;
         qop=auth;
         cnonce="17289d3348dfc6f2";
         opaque="d75db7b160fe72d1346d2bd1f67bfd10";
         nonce="73dd363a242fcd9db88e54f86c1ae089"',
    ));
}

    /**
     * Calls the Shopware API.
     *
     * @param        $url
     * @param string $method
     * @param array  $data
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function call($url, $method = self::METHOD_GET, $data = array(), $params = array())
    {
        if (!in_array($method, $this->validMethods)) {
            throw new \Exception('Invalid HTTP-Methode: ' . $method);
        }
        $queryString = '';
        if (!empty($params)) {
            $queryString = http_build_query($params);
        }
        $url = rtrim($url, '?') . '?';
        $url = $this->apiUrl . $url . $queryString;
        $dataString = json_encode($data);
        curl_setopt($this->cURL, CURLOPT_URL, $url);
        curl_setopt($this->cURL, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->cURL, CURLINFO_HEADER_OUT, true);

        $result   = curl_exec($this->cURL);
        $httpCode = curl_getinfo($this->cURL, CURLINFO_HTTP_CODE);

        return $this->prepareResponse($result, $httpCode);
    }


    /**
     * Calls the Shopware API with GET parameter.
     *
     * @param       $url
     * @param array $params
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function get($url, $params = [])
    {
        return $this->call($url, self::METHOD_GET, [], $params);
    }

    /**
     * Calls the Shopware API with POST parameter.
     *
     * @param       $url
     * @param array $data
     * @param array $params
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function post($url, $data = [], $params = [])
    {
        return $this->call($url, self::METHOD_POST, $data, $params);
    }

    /**
     * Calls the Shopware API with PUT parameter.
     *
     * @param       $url
     * @param array $data
     * @param array $params
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function put($url, $data = [], $params = [])
    {
        return $this->call($url, self::METHOD_PUT, $data, $params);
    }

    /**
     * Calls the Shopware API with DELETE parameter.
     *
     * @param       $url
     * @param array $params
     *
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function delete($url, $params = [])
    {
        return $this->call($url, self::METHOD_DELETE, [], $params);
    }

    /**
     * Delivers Shopware Response to an API call.
     *
     * @param $result
     * @param $httpCode
     *
     * @return mixed|void
     */
    protected function prepareResponse($result, $httpCode)
    {
        if (null === $decodedResult = json_decode($result, true)) {
            return false;
        }

        if (!isset($decodedResult['success'])) {
            return false;
        }

        return $decodedResult;
    }
}
