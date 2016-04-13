<?php

namespace Basecom\Bundle\ShopwareConnectorBundle\Api;

class ApiClient
{
    const METHODE_GET    = 'GET';
    const METHODE_PUT    = 'PUT';
    const METHODE_POST   = 'POST';
    const METHODE_DELETE = 'DELETE';
    protected $validMethods = array(
        self::METHODE_GET,
        self::METHODE_PUT,
        self::METHODE_POST,
        self::METHODE_DELETE
    );
    protected $apiUrl;
    protected $cURL;

    public function __construct($apiUrl, $username, $apiKey) {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        //Initializes the cURL instance
        $this->cURL = curl_init();
        curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->cURL, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($this->cURL, CURLOPT_USERPWD, $username . ':' . $apiKey);
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
        ));
    }

    public function call($url, $method = self::METHODE_GET, $data = array(), $params = array()) {
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
        $result   = curl_exec($this->cURL);
        $httpCode = curl_getinfo($this->cURL, CURLINFO_HTTP_CODE);
        return $this->prepareResponse($result, $httpCode);
    }

    public function get($url, $params = array()) {
        return $this->call($url, self::METHODE_GET, array(), $params);
    }

    public function post($url, $data = array(), $params = array()) {
        return $this->call($url, self::METHODE_POST, $data, $params);
    }

    public function put($url, $data = array(), $params = array()) {
        return $this->call($url, self::METHODE_PUT, $data, $params);
    }

    public function delete($url, $params = array()) {
        return $this->call($url, self::METHODE_DELETE, array(), $params);
    }

    protected function prepareResponse($result, $httpCode) {
        echo "<h2>HTTP: $httpCode</h2>";
        if (null === $decodedResult = json_decode($result, true)) {
            $jsonErrors = array(
                JSON_ERROR_NONE => 'Es ist kein Fehler aufgetreten',
                JSON_ERROR_DEPTH => 'Die maximale Stacktiefe wurde erreicht',
                JSON_ERROR_CTRL_CHAR => 'Steuerzeichenfehler, möglicherweise fehlerhaft kodiert',
                JSON_ERROR_SYNTAX => 'Syntaxfehler',
            );
            echo "<h2>Could not decode json</h2>";
            echo "json_last_error: " . $jsonErrors[json_last_error()];
            echo "<br>Raw:<br>";
            echo "<pre>" . print_r($result, true) . "</pre>";
            return;
        }
        if (!isset($decodedResult['success'])) {
            echo "Invalid Response";
            return;
        }
        if (!$decodedResult['success']) {
            echo "<h2>No Success</h2>";
            echo "<p>" . $decodedResult['message'] . "</p>";
            return;
        }
        echo "<h2>Success</h2>"."\n";
        if (isset($decodedResult['data'])) {
            //echo "<pre>" . print_r($decodedResult['data'], true) . "</pre>";
        }
        return $decodedResult;
    }
}