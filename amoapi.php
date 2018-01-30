<?php

Class AmoAPI
{
	protected
		/** @var bool */
		$_auth,
		/** @var string */
		$_subdomain,
		/** @var string */
		$_login,
		/** @var string */
		$_hash,
		/** @var string */
		$_domain,
		/** @var string */
		$_link;

	protected $_error = [
		301	=>	'Moved permanently',
		400	=>	'Bad request',
		401	=>	'Unauthorized',
		403	=>	'Forbidden',
		404	=>	'Not found',
		500	=>	'Internal server error',
		502	=>	'Bad gateway',
		503	=>	'Service unavailable'
	];

	public function __construct ($subdomain, $login, $hash, $domain = 'ru')
	{
		$this->_subdomain = $subdomain;
		$this->_login = $login;
		$this->_hash = $hash;
		$this->_domain = $domain;
		$this->_link = 'https://'.$this->_subdomain.'.amocrm.'.$this->_domain;
	}

	protected function query($link, $type = 'GET', $data = NULL)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
		curl_setopt($curl,CURLOPT_URL, $this->_link . $link);
		curl_setopt($curl,CURLOPT_HEADER,FALSE);
		curl_setopt($curl,CURLOPT_COOKIEFILE,__DIR__.'/cookie.txt');
		curl_setopt($curl,CURLOPT_COOKIEJAR,__DIR__.'/cookie.txt');
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

		if ($type === 'POST') {
			curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
			curl_setopt($curl,CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($curl,CURLOPT_TIMEOUT,30);
		}
		$response = curl_exec($curl);
		$code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
		$code = (int) $code;
		curl_close($curl);
		try
		{
			if($code!=200 && $code!=204)
				throw new Exception(isset($this->_error[$code]) ? $this->_error[$code] : 'Undescribed error', $code);
		}
		catch(Exception $E)
		{
			die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
		}

		$response = json_decode($response, TRUE);
		return $response;
	}

	public function get($link)
	{
		return $this->query($link);
	}

	public function post($link, $data)
	{
		return $this->query($link, 'POST', $data);
	}

	public function auth()
	{
		$user = [
			'USER_LOGIN' => $this->_login,
			'USER_HASH' => $this->_hash
		];
		$this->post('/private/api/auth.php?type=json', $user);
	}

}