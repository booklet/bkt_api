<?php
class BKTApi
{
    public static function get($resource)
    {
        $api = new self;
        return $api->request('GET', $resource);
    }

    public static function post($resource, $data, $options = [])
    {
        $api = new self;
        return $api->request('POST', $resource, $data, $options);
    }

    public static function put($resource, $data)
    {
        $api = new self;
        return $api->request('PUT', $resource, $data);
    }

    public static function putByPost($resource, $data)
    {
        if (!isset($data['_method'])) {
            $data = ['_method' => 'PUT'];
        }
        $api = new self;
        return $api->request('POST', $resource, $data);
    }

    public static function delete($resource)
    {
        $api = new self;
        return $api->request('DELETE', $resource);
    }

    public static function deleteByPost($resource)
    {
        $data = [];
        $data = ['_method' => 'DELETE'];
        $api = new self;
        return $api->request('POST', $resource, $data);
    }

    private function request($method, $resource, $data = null, $options = [])
    {
        $request = new BKTApiRequest($method, $resource, $data, $options);
        return $request->makeRequest();
    }
}
