<?php

class FacebookApp {

	private $fb = null;
	private $me = null;
	private $namespace = null;
	private $scope = null;

	public function __construct($config)
	{
		$this->fb = new Facebook(array_only($config, ['appId', 'secret', 'fileUpload']));
		$this->namespace = $config['namespace'];
		$this->scope = $config['scope'];

		// Convince IE to accept third-party cookies
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

		// Trigger facebook-php-sdk so that it reads the signed_request
		$this->fb->getUser();
	}

	public function accessToken()
	{
		return $this->fb->getAccessToken();
	}

	public function api(/* polymorphic */)
	{
		$args = func_get_args();
		$fb = $this->fb;
		$ret = null;

		if (is_array($args[0]))
			switch ($args[0]['method']) {
				case 'fql.query':
					$name = $args[0]['query'];
					break;
				default:
					$name = $args[0];
			}
		else
			$name = $args[0];
		
		try
		{
			Log::debug("API call $name");

			Profiler::time(function() use ($args, $fb, &$ret) {
				$ret = call_user_func_array(array($fb, 'api'), $args);
			}, "API call $name");
		}
		catch (FacebookApiException $e)
		{
			$res = $e->getResult();
			if (isset($res['error']))
				$code = $res['error']['code'];
			elseif (isset($res['error_code']))
				$code = $res['error_code'];

			if (in_array($code, [101,190,2500]))
				Event::fire('facebook.auth_required', ['API exception: ' . $e]);

			throw $e;
		}

		return $ret;
	}

	public function auth()
	{
		if (Session::has('facebook.user') || $this->isFacebook())
			return;

		$id = $this->fb->getUser();

		if (!$id)
			Event::fire('facebook.auth_required', ['No Facebook ID']);

		$granted = $this->api('/me/permissions')['data'][0];
		$permissions = array_filter($this->scope, function ($perm) use ($granted) {
			return !( isset($granted[$perm]) && $granted[$perm] == 1 );
		});

		if (!empty($permissions))
			Event::fire('facebook.auth_required',
				['Missing permissions ' . join(',', $permissions)]);

		$this->me = $this->api('/me');
		
		Session::put('facebook.user', $this->me);
		return true;
	}

	public function authURL()
	{
		return $this->fb->getLoginUrl([
			'scope' => join(',', $this->scope),
			'redirect_uri' => 'http://apps.facebook.com/' . $this->namespace . '/' . URI::current(),
		]);
	}

	public function isFacebook()
	{
		return strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit/') === 0;
	}

	public function me()
	{
		if ($this->me)
			return $this->me;

		$this->me = Session::get('facebook.user', null);
		return $this->me;
	}

}
