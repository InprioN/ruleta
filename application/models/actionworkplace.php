<?php

class ActionWorkplace extends Action {

	private $works = [365293368610];

	protected function _check()
	{
		$workplaces = User::me()->info()['work'];
		
		foreach ($workplaces as $workplace)
			if (in_array($workplace['employer']['id'], $this->works))
				return true;

		return false;
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
