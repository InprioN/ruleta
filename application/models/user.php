<?php

class User extends Eloquent {

	public static $me = null;
	public static $timestamps = false;

	public static function ensure($fb)
	{
		if (Session::has('user'))
			return;

		$user = User::find($fb['id']);
		if (!$user)
			$user = User::create(array_only($fb, array('id', 'name', 'email')));

		if (!$user)
			throw new Exception('Couldn\'t save user to the DB');

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

		return self::$me = User::find($fbme['id']);
	}

	public function actions()
	{
		return $this->has_many('Action');
	}

	public function app_users()
	{
		$fbapp = IoC::resolve('facebook-app');
		$data = $fbapp->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1',
		));

		$ids = array_pluck($data, 'uid');

		if (empty($ids))
			return array();

		return User::where_in('id', $ids)->get();
	}

	public function is_me() {
		return $this->id === User::me()->id;
	}

	public function spin() {
		if (strtotime($this->last_spun) > strtotime('today midnight'))
			throw new Exception('User has already spun today');

		$already = array_pluck($this->actions()->get(), 'type');
		$available = array_diff(Action::types(), $already);

		if (empty($available))
			throw new Exception('User has finished all actions');

		$type = array_rand($available);
		$type = 4; // TODO Guaranteed to be random
		$action = Action::create_for_user($this, $type);
		if (!$action)
			throw new Exception('Couldn\'t create the action');

		$this->last_spun = new \DateTime;
		$this->save();
		
		return $action;
	}

}
