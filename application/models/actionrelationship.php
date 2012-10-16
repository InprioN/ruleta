<?php

class ActionRelationship extends Action {

	protected function _check()
	{
		$data = $this->getData();
		$user = User::me()->info();

		if (! isset($data['was_married_to']))
			return $user['relationship_status'] == 'Married';
		else
			return $user['relationship_status'] == 'Married' &&
						 $data['was_married_to'] != $user['significant_other']['id'];
	}

	protected function _initialize()
	{
		$user = $this->user->info();
		
		if ($user['relationship_status'] != 'Married')
			return $this->setData(null);
		else
			return $this->setData(array('was_married_to' => $user['significant_other']['id']));
	}
	
}
