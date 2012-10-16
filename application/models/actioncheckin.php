<?php

class ActionCheckin extends Action {

	private $places = ['157110550980379', '139072726134873'];

	protected function _check()
	{
		$fbapp = IoC::resolve('facebook-app');
		$checkins = $fbapp->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT page_id FROM checkin WHERE author_uid = me() AND page_id IN (' .
				join(',', $this->places) . ')',
		));

		// User has to checkin in at least one page
		return !empty($checkins);
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
