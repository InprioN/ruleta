<?php

class ActionFriends extends Action {

	protected function _check()
	{
		$previous = $this->getData()['most'];
		return $previous != $this->getGenderWithMostFriends();
	}

	protected function _initialize()
	{
		$this->setData(array('most' => $this->getGenderWithMostFriends()));
	}

	private function getFriendsGenders()
	{
		$fbapp = IoC::resolve('facebook-app');
		$friends = $fbapp->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT sex FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())',
		));

		return array_count_values(array_pluck($friends, 'sex'));
	}

	private function getGenderWithMostFriends()
	{
		$genders = array_diff_key($this->getFriendsGenders(), ['' => 0]);
		return array_search(max($genders), $genders);
	}
	
}
