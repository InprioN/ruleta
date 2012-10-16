<?php

class Action extends Eloquent {

	public static $table = 'actions';
	public static $timestamps = false;

	private static $types = [
		// 1 => 'Picture',
		2 => 'Relationship',
		// 3 => 'Publishing',
		4 => 'Birthday',
		5 => 'Checkin',
		6 => 'Workplace',
		7 => 'Like',
		8 => 'Friends',
		9 => 'Hashtag',
		10 => 'Hometown',
	];

	public static function create_for_user(User $user, $type)
	{
		if ($action = $user->actions()->where('type', '=', $type)->get())
			return $action->subclass_me();

		$class = self::subclass($type);

		$action = new $class([
			'user_id' => $user->id,
			'type' => $type,
		]);

		$action->_initialize();

		if (!$action->save())
			return null;

		return $action;
	}

	public static function subclass($type)
	{
		if (!array_key_exists($type, self::$types))
			throw new \Exception('Unknown action type: ' . $type);

		$class = 'Action' . self::$types[$type];

		if (!class_exists($class))
			throw new \Exception('Could not find Action subclass: ' . $class);

		return $class;
	}

	public static function types()
	{
		return array_keys(self::$types);
	}

	public function check()
	{
		if (!is_a($this, 'Action'))
			throw new \Exception('You can only call check() on the parent model Action');

		if ($this->user_id != User::me()->id)
			throw new \Exception('A user can only check its own actions');

		if ($this->completed)
			return $this->completed;

		$completed = $this->subclass_me()->_check();

		if (!$this->completed && $completed)
		{
			$this->completed = true;
			$this->save();
		}

		return $completed;
	}

	public function subclass_me()
	{
		if (get_parent_class($this) == 'Action')
			return $this;

		$class = self::subclass($this->type);
		return $class::find($this->id);
	}

	public function user()
	{
		return $this->belongs_to('User');
	}

	protected function getData()
	{
		return unserialize($this->data);
	}

	protected function setData($data)
	{
		$this->data = serialize($data);
	}

}
