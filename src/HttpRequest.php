<?php

namespace Vzaar;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * HttpRequest
 *
 * @author Skitsanos
 */
//application/x-www-form-urlencoded
//application/json
//text/xml

class HttpRequest
{

    protected $c;
    protected $url;
    var $method = "GET";
    var $preventCaching = true;
    var $useSsl = true;
    var $headers = array();
    var $verbose = false;
    var $uploadMode = false;

    function __construct($url)
    {
        $url = 'http://www.web07.vivastreet.com/videos/vzaar_notification.php';

        if (!function_exists('curl_init')) {
            echo "Function curl_init, used by HttpRequest does not exist.\n";
        }
        $this->url = $url;
        $this->c = curl_init($this->url);

        var_dump($this->url);

        $this->client = new Client();
    }

    function send($data = null, $filepath = null)
    {
        if (count($this->headers) > 0) {
            $headers = [];

            foreach ($this->headers as $header) {
                $param = explode(":", $header);
                $headers[$param[0]] = $param[1];
            }

            $this->client->setDefaultOption('headers', $headers);
        }

        var_dump($headers, $data, $this->method);

        switch (strtoupper($this->method)) {
            case 'POST':
                if ($data !== null) {
                    // try {
                        // One way to upload a file but it then we end up not sending the data
                        $fp = fopen($filepath, 'r');

                        $response = $this->client->post(
                            $this->url,
                            $headers,
                            $fp
                        );
                    // } catch (\GuzzleHttp\Exception\ServerException $e) {
                    //     $response = $e->getResponse();
                    // }
                }

                // curl_setopt($this->c, CURLOPT_POST, true);
                // if ($data != null)
                //     curl_setopt($this->c, CURLOPT_POSTFIELDS, $data);
                break;

            case 'HEAD':
                $request = $this->client->head($this->url);
                // curl_setopt($this->c, CURLOPT_NOBODY, true);
                break;

            case 'DELETE':
                $request = $this->client->delete($this->url);
                // curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            case 'PUT':
                if ($data !== null) {
                    // One way to upload a file but it then we end up not sending the data
                    $fp = fopen($filepath, 'r');

                    $response = $this->client->put(
                        $this->url,
                        $headers,
                        $fp
                    );

                    // try {
                    //     $response = $this->client->put($this->url, $headers, $data);
                    // } catch (\GuzzleHttp\Exception\ServerException $e) {
                    //     $response = $e->getResponse();
                    // }

                }

                // curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "PUT");
                // if ($data != null)
                //     curl_setopt($this->c, CURLOPT_POSTFIELDS, $data);
                break;
        }

        var_dump('$response', $response);

        return $response;
    }

    function sendCurl($data = null, $filepath = null)
    {
        $new_filepath = __DIR__ . '/../../sample.mp4';
        $filepath = $new_filepath;

        // $this->sendGuzzle($data, $filepath);

        // var_dump($filepath);
        // $client = new Client(['verify' => false]);

        // var_dump('$client');

        var_dump($filepath, $data, $this->method);

        if (count($this->headers) > 0) {
            curl_setopt($this->c, CURLOPT_HEADER, false);
            curl_setopt($this->c, CURLOPT_HTTPHEADER, $this->headers);
        }

        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, true);

        if ($this->useSsl) {
            curl_setopt($this->c, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->c, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($this->preventCaching) {
            curl_setopt($this->c, CURLOPT_FORBID_REUSE, true);
            curl_setopt($this->c, CURLOPT_FRESH_CONNECT, true);
        }

        if ($this->uploadMode) {
            //curl_setopt($this->c, CURLOPT_URL, $filepath);
            //curl_setopt($this->c, CURLOPT_UPLOAD, true);
            curl_setopt($this->c, CURLOPT_POST, true);
            $fp = fopen($filepath, 'r');
            curl_setopt($this->c, CURLOPT_INFILE, $fp);
            curl_setopt($this->c, CURLOPT_INFILESIZE, filesize($filepath));
        }

        switch (strtoupper($this->method)) {
            case 'POST':
                curl_setopt($this->c, CURLOPT_POST, true);
                if ($data != null)
                    curl_setopt($this->c, CURLOPT_POSTFIELDS, $data);
                break;

            case 'HEAD':
                curl_setopt($this->c, CURLOPT_NOBODY, true);
                break;

            case 'DELETE':
                curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            case 'PUT':
                curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data != null)
                    curl_setopt($this->c, CURLOPT_POSTFIELDS, $data);
                break;
        }

        curl_setopt($this->c, CURLOPT_VERBOSE, $this->verbose);

        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($this->c, CURLOPT_FOLLOWLOCATION, true);
            $output = curl_exec($this->c);
        } else {
            curl_setopt($this->c, CURLOPT_FOLLOWLOCATION, false);
            $output = $this->curlExec($this->c);
        }

        // echo '$output';


        var_dump(file_exists($new_filepath));

        var_dump($new_filepath, $this->headers, $filepath, $this->url, '$output', $output);

        return $output;
    }

    function curlExec($ch)
    {
        $newUrl = '';
        $maxRedirection = 10;
        do {
            if ($maxRedirection < 1) die('Error: reached the limit of redirections');
            if (!empty($newUrl)) curl_setopt($ch, CURLOPT_URL, $newUrl); // redirect needed

            $curlResult = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($code == 301 || $code == 302 || $code == 303 || $code == 307) {
                preg_match('/Location:(.*?)\n/', $curlResult, $matches);
                $newUrl = trim(array_pop($matches));
                curl_close($ch);

                $maxRedirection--;
                continue;
            } else // no more redirection
            {
                $code = 0;
                curl_close($ch);
            }
        } while ($code);
        return $curlResult;
    }
}

?>
