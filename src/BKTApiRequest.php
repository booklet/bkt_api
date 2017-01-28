<?php
class BKTApiRequest
{
    public $method;
    public $resource;
    public $data;

    public function __construct($method, $resource, $data=null, $options = [])
    {
        $this->method = $method;
        $this->resource = $resource;
        $this->data = $data;
        $this->options = $options;
    }

    public function makeRequest()
    {
        $curl = curl_init();
        $base_url = Config::get('env') == 'production' ? 'https://api.booklet.pl' : 'http://api.booklet.dev';
        $url = $base_url . '/v1' . $this->resource;

        switch ($this->method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if (!empty($this->data)) {
                    $flatten_post_array_data = $this->options['flatten_arrays'] ?? false;
                    if ($flatten_post_array_data) {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                    } else {
                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->data));
                    }
                }

                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if (!empty($this->data)) {
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->data));
                }
                break;

            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

            // GET
            default:
                if (!empty($this->data))
                    $url = sprintf("%s?%s", $url, json_encode($this->data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 0); // dokładniejsze komunikaty o błędach
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, 1); // headers code

        $token = Config::get('access_token') ?? $_COOKIE['access_token'] ?? null;

        // TODO
        // if we set to Content-type: application/json
        // we cant send files

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
        //  "Content-type: application/json",
            "Authorization: $token"
        ]);

        $response = curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = $this->get_headers_from_curl_response(substr($response, 0, $header_size));
        $body = substr($response, $header_size);

        curl_close($curl);

        return new BKTApiResponse($http_code, $header, $body);
    }

    public function get_headers_from_curl_response($header_content)
    {
        $headers = array();

        // Split the string on every "double" new line.
        $arr_requests = explode("\r\n\r\n", $header_content);

        // Loop of response headers. The "count() -1" is to
        // avoid an empty row for the extra line break before the body of the response.
        for ($index = 0; $index < count($arr_requests) -1; $index++) {
            foreach (explode("\r\n", $arr_requests[$index]) as $i => $line) {
                if ($i === 0) {
                    $headers[$index]['http_code'] = $line;
                } else {
                    list ($key, $value) = explode(': ', $line);
                    $headers[$index][$key] = $value;
                }
            }
        }

        return $headers;
    }
}
