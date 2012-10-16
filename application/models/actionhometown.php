<?php

class ActionHometown extends Action {

	private $cities = [108581069173026];

	protected function _check()
	{
		$info = User::me()->info();

		if (in_array($info['hometown']['id'], $this->cities))
			return true;
		
		if (in_array($info['location']['id'], $this->cities))
			return true;
		
		return false;
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
