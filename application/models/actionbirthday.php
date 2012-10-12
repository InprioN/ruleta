<?php

class ActionBirthday extends Action {

	protected function _check()
	{
		$bday = $this->user->fb_api('')['birthday'];
		$ret = date('d-m', strtotime($bday)) === date('d-m');

		echo 'Checked action, ' . $ret;
		return $ret;
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
