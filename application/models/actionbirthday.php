<?php

class ActionBirthday extends Action {

	protected function _check()
	{
		$bday = User::me()->info()['birthday'];
		return date('d-m', strtotime($bday)) === date('d-m');
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
