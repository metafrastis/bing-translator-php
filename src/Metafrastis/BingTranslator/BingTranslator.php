<?php

namespace Metafrastis\BingTranslator;

class BingTranslator {

    protected $queue = [];

    public function translate($args = [], $opts = []) {
        $args['from'] = isset($args['from']) ? $args['from'] : null;
        $args['to'] = isset($args['to']) ? $args['to'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        if (!$args['from']) {
            return false;
        }
        if (!$args['to']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        $url = 'https://www.bing.com/ttranslate';
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: https://www.bing.com/translator/',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:67.0) Gecko/20100101 Firefox/67.0',
        ];
        $params = ['from' => $args['from'], 'to' => $args['to'], 'text' => $args['text']];
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (!$json || !isset($json['statusCode']) || $json['statusCode'] !== 200 || !isset($json['translationResponse'])) {
            return false;
        }
        return $json['translationResponse'];
    }

    public function detect($args = [], $opts = []) {
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        if (!$args['text']) {
            return false;
        }
        $url = 'https://www.bing.com/tdetect';
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: https://www.bing.com/translator/',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:67.0) Gecko/20100101 Firefox/67.0',
        ];
        $params = ['text' => $args['text']];
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if ($queue) {
            return;
        }
        if (!$response['body'] || !in_array(strlen($response['body']), [2, 3, 5, 6, 7, 8])) {
            return false;
        }
        return $response['body'];
    }

    public function post($url, $headers = [], $params = [], $options = [], $queue = false) {
        $opts = [];
        $opts[CURLINFO_HEADER_OUT] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 5;
        $opts[CURLOPT_ENCODING] = '';
        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 10;
        $opts[CURLOPT_URL] = $url;
        foreach ($opts as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
        if ($queue) {
            $this->queue[] = ['options' => $options];
            return;
        }
        $follow = false;
        if ($options[CURLOPT_FOLLOWLOCATION]) {
            $follow = true;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        $errors = 2;
        $redirects = isset($options[CURLOPT_MAXREDIRS]) ? $options[CURLOPT_MAXREDIRS] : 5;
        while (true) {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            if ($error || $errno) {
                if ($errors > 0) {
                    $errors--;
                    continue;
                }
            } elseif ($info['redirect_url'] && $follow) {
                if ($redirects > 0) {
                    $redirects--;
                    $options[CURLOPT_URL] = $info['redirect_url'];
                    continue;
                }
            }
            break;
        }
        return $response;
    }

    public function multi($args = []) {
        if (!$this->queue) {
            return [];
        }
        $mh = curl_multi_init();
        $chs = [];
        foreach ($this->queue as $key => $request) {
            $ch = curl_init();
            $chs[$key] = $ch;
            curl_setopt_array($ch, $request['options']);
            curl_multi_add_handle($mh, $ch);
        }
        $running = 1;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        $responses = [];
        foreach ($chs as $key => $ch) {
            curl_multi_remove_handle($mh, $ch);
            $body = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $options = $this->queue[$key]['options'];
            if (strpos($options[CURLOPT_URL], '/ttranslate') !== false) {
                $json = json_decode($body, true);
                if (!$json || !isset($json['statusCode']) || $json['statusCode'] !== 200 || !isset($json['translationResponse'])) {
                    $responses[$key] = false;
                    continue;
                }
                $responses[$key] = $json['translationResponse'];
            } elseif (strpos($options[CURLOPT_URL], '/tdetect') !== false) {
                if (!$body || !in_array(strlen($body), [2, 3, 5, 6, 7, 8])) {
                    continue;
                }
                $responses[$key] = $body;
            } else {
                $responses[$key] = $body;
            }
        }
        curl_multi_close($mh);
        $this->queue = [];
        return $responses;
    }

}
