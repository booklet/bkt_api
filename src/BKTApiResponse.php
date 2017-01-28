<?php
class BKTApiResponse
{
    public $http_code;
    public $header;
    public $body;

    public function __construct($http_code, $header, $body)
    {
        $this->http_code = $http_code;
        $this->header = $header;
        $this->body = $body;
    }

    public function body()
    {
        return json_decode($this->body);
    }
}
