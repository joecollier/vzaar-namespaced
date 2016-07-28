<?php

namespace Vzaar;

class GuzzleRequest
{
    protected $client;
    protected $url;
    var $method = 'GET';
    var $preventCaching = true;
    var $useSsl = true;
    var $headers = [];
    var $verbose = false;
    var $uploadMode = true;

    function __construct($url, $client)
    {
        $this->url = $url;
        $this->client = $client;
    }

    protected function array2xml($array, $node_name="root")
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElement($node_name);
        $dom->appendChild($root);

        $array2xml = function ($node, $array) use ($dom, &$array2xml) {
            foreach($array as $key => $value){
                if (is_array($value)) {
                    $n = $dom->createElement($key);
                    $node->appendChild($n);
                    $array2xml($n, $value);
                } else {
                    $attr = $dom->createAttribute($key);
                    $attr->value = $value;
                    $node->appendChild($attr);
                }
            }
        };

        $array2xml($root, $array);

        return $dom->saveXML();
    }

    public function send($data = null, $filepath = null)
    {
        switch (strtoupper($this->method)) {
            case 'POST':
                if ($data != null) {
                    $request = $this->client->createRequest(
                        'POST',
                        $this->url,
                        ['body' => $data]
                    );

                    $request->setHeader('User-Agent', 'Vzaar API Client');
                    $request->setHeader('Enclosure-Type', 'multipart/form-data');
                    $request->setHeader('x-amz-acl', 'private');

                    $postBody = $request->getBody();
                    $postBody->addFile(
                        new \GuzzleHttp\Post\PostFile('file', fopen($filepath, 'r'))
                    );
                }
                break;

            case 'HEAD':
                    $request = $this->client->createRequest(
                        'HEAD',
                        $this->url
                    );
                break;

            case 'DELETE':
                    $request = $this->client->createRequest(
                        'DELETE',
                        $this->url
                    );
                break;

            case 'PUT':
                if ($data != null)
                    $request = $this->client->createRequest(
                        'PUT',
                        $this->url,
                        ['body' => $data]
                    );

                    $request->setHeader('User-Agent', 'Vzaar API Client');
                    $request->setHeader('Enclosure-Type', 'multipart/form-data');
                    $request->setHeader('x-amz-acl', 'private');
                break;

            case 'GET':
                $request = $this->client->createRequest(
                    'GET',
                    $this->url
                );

                foreach ($this->headers as $header) {
                    $params = explode(': ', $header);
                    $request->setHeader($params[0], $params[1]);
                }

                break;
        }

        try {
            $response = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
        }

        return $response->getBody()->getContents();
    }

    public function detectMimeType($fn) {
        $mimetype = false;

        if(function_exists('finfo_fopen')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $fn);
            finfo_close($finfo);
        } elseif(function_exists('getimagesize')) {
            $size = getimagesize($fn);
            $mimetype = $size['mime'];
        } elseif(function_exists('mime_content_type')) {
            $mimetype = mime_content_type($fn);
        }
        return $mimetype;
    }
}

?>