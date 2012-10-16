<?php

class User extends Eloquent {

	public static $timestamps = false;
	private static $me = null;

	private $info = null;

	public static function ensure($fb)
	{
		if (Session::has('user'))
			return;

		$user = User::find($fb['id']);
		if (!$user)
			$user = User::create(array_only($fb, ['id', 'name', 'email']));

		if (!$user)
			throw new \Exception('Couldn\'t save user to the DB');

		Session::put('user', $user);
	}

	public static function me()
	{
		if (self::$me)
			return self::$me;

		$fbapp = IoC::resolve('facebook-app');
		$fbme = $fbapp->me();

		if (!$fbme)
			return null;

		self::$me = User::find($fbme['id']);
		self::$me->setInfo($fbme);

		return self::$me;
	}

	public function actions()
	{
		return $this->has_many('Action');
	}

	public function friends()
	{
		$key = 'friends.' . $this->id;
		if (Cache::has($key))
			return Cache::get($key);

		$fbapp = IoC::resolve('facebook-app');
		$data = $fbapp->api([
			'method' => 'fql.query',
			'query' => 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1',
		]);

		$ids = array_pluck($data, 'uid');

		if (empty($ids))
			$friends = [];
		else
			$friends = User::where_in('id', $ids)->get();

		Cache::put($key, $friends, 10);

		return $friends;
	}

	public function info()
	{
		if ($this->info)
			return $this->info;

		$fbapp = IoC::resolve('facebook-app');

		if ($this->is_me())
			$this->info = $fbapp->api('/me');
		else
			$this->info = $fbapp->api('/' . $this->id);

		return $this->info;
	}

	public function setInfo($info)
	{
		$this->info = $info;
	}

	public function is_me()
	{
		return $this->id === User::me()->id;
	}

	public function picture()
	{
		$filename = 'pictures/' . $this->id . '.jpg';
		$path = path('public') . $filename;

		if (File::exists($path))
			return asset($filename);

		return route('picture', [$this->id]);
	}

	public function spin()
	{
		if (strtotime($this->last_spun) > strtotime('today midnight'))
			throw new \Exception('User has already spun today');

		$already = array_pluck($this->actions()->get(), 'type');
		$available = array_diff(Action::types(), $already);

		if (empty($available))
			throw new \Exception('User has initialized all actions');

		$type = array_rand($available);
		$action = Action::create_for_user($this, $available[$type]);
		if (!$action)
			throw new \Exception('Couldn\'t create the action');

		$this->last_spun = new \DateTime;
		$this->save();
		
		return $action;
	}

}
