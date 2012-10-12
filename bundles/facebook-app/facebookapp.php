<?php

class FacebookApp {

	private $fb = null;
	private $me = null;
	private $scope = null;

	public function __construct($config)
	{
		$this->fb = new Facebook(array_only($config, array('appId', 'secret', 'fileUpload')));
		$this->scope = $config['scope'];

		// Convince IE to accept third-party cookies
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	}

	public function api(/* polymorphic */)
	{
    $args = func_get_args();
    $fb = $this->fb;
    $name = is_array($args[0]) ? $args[0]['method'] : $args[0];
    $ret = null;

    try
    {
	    Profiler::time(function() use ($args, $fb, &$ret) {
	    	$ret = call_user_func_array(array($fb, 'api'), $args);
	    }, "API call $name");
	  }
	  catch (\FacebookApiException $e)
	  {
	  	$code = $e->getData()['code'];
			if (in_array($code, array(190)))
				Event::fire('facebook.auth_required');

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
			Event::fire('facebook.auth_required');

		try
		{
			$granted = $this->api('/me/permissions')['data'][0];
			$permissions = array_filter($this->scope, function ($perm) use ($granted) {
				return !( isset($granted[$perm]) && $granted[$perm] == 1 );
			});

			if (!empty($permissions))
				Event::fire('facebook.auth_required');

			$user = $this->api('/me');
		}
		catch(\FacebookApiException $e)
		{
			Event::fire('facebook.auth_required');
		}
		
		Session::put('facebook.user', $user);
		return true;
	}

	public function authURL()
	{
		return $this->fb->getLoginUrl(array(
			'scope' => join(',', $this->scope),
			'redirect_uri' => 'http://apps.facebook.com/aperolruleta',
		));
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
