<?php
namespace App\Freedom;

class WS1C
{
    private $guzzle_client;

    public function __construct() {
        $this->guzzle_client = new \GuzzleHttp\Client([
            'base_uri' => getenv('JSON_PATH_1C'),
            'timeout'  => 20.0
        ]);
    }

    public function call_ws_method($method_name, $params) {
        if(preg_match('/(?<prefix>.*)\/(?<method>.*)/', $method_name, $m)) {
            $prefix = $m['prefix'];
            $method_name = $m['method'];
        } else {
            $prefix = 'Cabinet';
        }
        try {
            $resp = $this->guzzle_client->request('POST', $prefix . '/' . $method_name, ['json' => $params]);
            $data = $resp->getBody();
            $data = json_decode($data->getContents(), true);
            return $data;
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            $ret = [];
            $ret['ws_error'] = 'Ошибка вызова веб-сервиса: ' . $e->getMessage();
            $ret['ws_exception'] = get_class($e);
            return $ret;
        }
    }
}

?>
