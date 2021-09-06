<?php

namespace Metafrastis\BingTranslator;

class BingTranslator {

	public $ig;
	public $iid;
	public $token;
	public $key;
	public $queue = [];
	public $response;
	public $responses = [];

	public function translate($args = [], $opts = []) {
		if (is_object($args)) {
			$args = json_decode(json_encode($args), true);
		}
		if (is_string($args)) {
			if (($arr = json_decode($args, true))) {
				$args = $arr;
			} else {
				parse_str($args, $arr);
				if ($arr) {
					$args = $arr;
				}
			}
		}
		$args = is_array($args) ? $args : [];
		$args['from'] = isset($args['from']) ? $args['from'] : null;
		$args['to'] = isset($args['to']) ? $args['to'] : null;
		$args['text'] = isset($args['text']) ? $args['text'] : null;
		$args['ig'] = isset($args['ig']) ? $args['ig'] : $this->ig;
		$args['iid'] = isset($args['iid']) ? $args['iid'] : $this->iid;
		$args['token'] = isset($args['token']) ? $args['token'] : $this->token;
		$args['key'] = isset($args['key']) ? $args['key'] : $this->key;
		if (!$args['ig'] || !$args['iid'] || !$args['token'] || !$args['key']) {
			$this->home($args, $opts);
			$args['ig'] = $this->ig ? $this->ig : $args['ig'];
			$args['iid'] = $this->iid ? $this->iid : $args['iid'];
			$args['token'] = $this->token ? $this->token : $args['token'];
			$args['key'] = $this->key ? $this->key : $args['key'];
		}
		if (!$args['from']) {
			return false;
		}
		if (!$args['to']) {
			return false;
		}
		if (!$args['text']) {
			return false;
		}
		if (!$args['ig']) {
			return false;
		}
		if (!$args['iid']) {
			return false;
		}
		if (!$args['token']) {
			return false;
		}
		if (!$args['key']) {
			return false;
		}
		$url = 'https://www.bing.com/ttranslatev3?isVertical=1&&IG='.$args['ig'].'&IID='.$args['iid'];
		$headers = [
			'Accept: '.'*'.'/'.'*',
			'Accept-Language: en-US,en;q=0.5',
			'Connection: keep-alive',
			'Content-Type: application/x-www-form-urlencoded',
			'Origin: https://www.bing.com',
			'Referer: https://www.bing.com/translator/',
			'Sec-Fetch-Dest: empty',
			'Sec-Fetch-Mode: cors',
			'Sec-Fetch-Site: same-origin',
			'TE: trailers',
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
		];
		$params = ['fromLang' => $args['from'], 'to' => $args['to'], 'text' => $args['text'], 'token' => $args['token'], 'key' => $args['key']];
		$options = [
			CURLOPT_CERTINFO => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 2,
		];
		$options = array_replace($options, $opts);
		$queue = isset($args['queue']) ? 'translate' : false;
		$response = $this->post($url, $headers, $params, $options, $queue);
		if (!$queue) {
			$this->response = $response;
		}
		if ($queue) {
			return;
		}
		$json = json_decode($response['body'], true);
		if (empty($json[0]['translations'][0]['text'])) {
			return false;
		}
		return $json[0]['translations'][0]['text'];
	}

	public function detect($args = [], $opts = []) {
		if (is_object($args)) {
			$args = json_decode(json_encode($args), true);
		}
		if (is_string($args)) {
			if (($arr = json_decode($args, true))) {
				$args = $arr;
			} else {
				parse_str($args, $arr);
				if ($arr) {
					$args = $arr;
				}
			}
		}
		$args = is_array($args) ? $args : [];
		$args['text'] = isset($args['text']) ? $args['text'] : null;
		$args['ig'] = isset($args['ig']) ? $args['ig'] : $this->ig;
		$args['iid'] = isset($args['iid']) ? $args['iid'] : $this->iid;
		$args['token'] = isset($args['token']) ? $args['token'] : $this->token;
		$args['key'] = isset($args['key']) ? $args['key'] : $this->key;
		if (!$args['ig'] || !$args['iid'] || !$args['token'] || !$args['key']) {
			$this->home($args, $opts);
			$args['ig'] = $this->ig ? $this->ig : $args['ig'];
			$args['iid'] = $this->iid ? $this->iid : $args['iid'];
			$args['token'] = $this->token ? $this->token : $args['token'];
			$args['key'] = $this->key ? $this->key : $args['key'];
		}
		if (!$args['text']) {
			return false;
		}
		if (!$args['ig']) {
			return false;
		}
		if (!$args['iid']) {
			return false;
		}
		if (!$args['token']) {
			return false;
		}
		if (!$args['key']) {
			return false;
		}
		$url = 'https://www.bing.com/ttranslatev3?isVertical=1&&IG='.$args['ig'].'&IID='.$args['iid'];
		$headers = [
			'Accept: '.'*'.'/'.'*',
			'Accept-Language: en-US,en;q=0.5',
			'Connection: keep-alive',
			'Content-Type: application/x-www-form-urlencoded',
			'Origin: https://www.bing.com',
			'Referer: https://www.bing.com/translator/',
			'Sec-Fetch-Dest: empty',
			'Sec-Fetch-Mode: cors',
			'Sec-Fetch-Site: same-origin',
			'TE: trailers',
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
		];
		$params = ['fromLang' => 'auto-detect', 'to' => 'pt', 'text' => $args['text'], 'token' => $args['token'], 'key' => $args['key']];
		$options = [
			CURLOPT_CERTINFO => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 2,
		];
		$options = array_replace($options, $opts);
		$queue = isset($args['queue']) ? 'detect' : false;
		$response = $this->post($url, $headers, $params, $options, $queue);
		if (!$queue) {
			$this->response = $response;
		}
		if ($queue) {
			return;
		}
		$json = json_decode($response['body'], true);
		if (empty($json[0]['detectedLanguage']['language'])) {
			return false;
		}
		return $json[0]['detectedLanguage']['language'];
	}

	public function home($args = [], $opts = []) {
		$args['force'] = isset($args['force']) ? $args['force'] : false;
		if ($this->ig && $this->iid && $this->token && $this->key && !$args['force']) {
			return ['ig' => $this->ig, 'iid' => $this->iid, 'token' => $this->token, 'key' => $this->key];
		}
		$url = 'https://www.bing.com/translator';
		$headers = [
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,'.'*'.'/'.'*'.';q=0.8',
			'Accept-Language: en-US,en;q=0.5',
			'Connection: keep-alive',
			'Sec-Fetch-Dest: document',
			'Sec-Fetch-Mode: navigate',
			'Sec-Fetch-Site: none',
			'Sec-Fetch-User: ?1',
			'Upgrade-Insecure-Requests: 1',
			'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0',
		];
		$params = null;
		$options = [
			CURLOPT_CERTINFO => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 2,
		];
		$options = array_replace($options, $opts);
		$queue = false;
		$response = $this->get($url, $headers, $params, $options);
		$this->response = $response;
		if (preg_match('`IG[\x00-\x20\x7f]*\:[\x00-\x20\x7f]*[\x222]([^\x22]+)[\x22]`', $response['body'], $match)) {
			$this->ig = $match[1];
		}
		if (preg_match('`\<div[^\x3e]*id[\x00-\x20\x7f]*\=[\x00-\x20\x7f]*[\x22]rich_tta[\x22][^\x3e]*data\-iid[\x00-\x20\x7f]*\=[\x00-\x20\x7f]*[\x22]([^\x22]+)[\x22]`', $response['body'], $match)) {
			$this->iid = $match[1].'.1';
		}
		if (preg_match('`var[\x00-\x20\x7f]*params_RichTranslateHelper[\x00-\x20\x7f]*\=[\x00-\x20\x7f]*[\x5b][\x00-\x20\x7f]*([0-9]+)[\x00-\x20\x7f]*\,[\x00-\x20\x7f]*[\x22]([^\x22]+)[\x22]`', $response['body'], $match)) {
			$this->key = $match[1];
			$this->token = $match[2];
		}
		return ['ig' => $this->ig, 'iid' => $this->iid, 'token' => $this->token, 'key' => $this->key];
	}

	public function request($method, $url, $headers = [], $params = null, $options = [], $queue = false) {
		if (is_string($headers)) {
			$headers = array_values(array_filter(array_map('trim', explode("\x0a", $headers))));
		}
		if (is_array($headers) && isset($headers['headers']) && is_array($headers['headers'])) {
			$headers = $headers['headers'];
		}
		if (is_array($headers)) {
			foreach ($headers as $key => $value) {
				if (is_string($key) && !is_numeric($key)) {
					$headers[$key] = sprintf('%s: %s', $key, $value);
				}
			}
		}
		$opts = [];
		$opts[CURLINFO_HEADER_OUT] = true;
		$opts[CURLOPT_CONNECTTIMEOUT] = 5;
		$opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
		$opts[CURLOPT_ENCODING] = '';
		$opts[CURLOPT_FOLLOWLOCATION] = false;
		$opts[CURLOPT_HEADER] = true;
		$opts[CURLOPT_HTTPHEADER] = $headers;
		if ($params !== null) {
			$opts[CURLOPT_POSTFIELDS] = is_array($params) || is_object($params) ? http_build_query($params) : $params;
		}
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
			$this->queue[] = ['options' => $options, 'queue' => $queue];
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

	public function get($url, $headers = [], $params = null, $options = [], $queue = false) {
		return $this->request('GET', $url, $headers, $params, $options, $queue);
	}

	public function post($url, $headers = [], $params = [], $options = [], $queue = false) {
		return $this->request('POST', $url, $headers, $params, $options, $queue);
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
			$response = [
				'info' => $info,
				'head' => $head,
				'body' => $body,
				'error' => $error,
				'errno' => $errno,
			];
			$this->responses[$key] = $response;
			$options = $this->queue[$key]['options'];
			if ($this->queue[$key]['queue'] === 'detect') {
				$json = json_decode($body, true);
				if (empty($json[0]['detectedLanguage']['language'])) {
					$responses[$key] = false;
					continue;
				}
				$responses[$key] = $json[0]['detectedLanguage']['language'];
			} elseif ($this->queue[$key]['queue'] === 'translate' || strpos($options[CURLOPT_URL], '/ttranslatev3') !== false) {
				$json = json_decode($body, true);
				if (empty($json[0]['translations'][0]['text'])) {
					$responses[$key] = false;
					continue;
				}
				$responses[$key] = $json[0]['translations'][0]['text'];
			} elseif (strpos($options[CURLOPT_URL], '/ttranslate') !== false) {
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
