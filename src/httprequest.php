<?php
class HttpRequest
{
    function __construct($url)
    {
        if (!function_exists('curl_init')) {
            echo "Function curl_init, used by HttpRequest does not exist.\n";
        }
        $this->url = $url;
        $this->c = curl_init($this->url);

        var_dump($this->url);
    }
}

$obj = new HttpRequest();

?>