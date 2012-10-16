<?php

class ActionLike extends Action {

	private $pages = ['484537154890796', '259636158405'];

	protected function _check()
	{
		$fbapp = IoC::resolve('facebook-app');
		$likes = $fbapp->api(array(
			'method' => 'fql.query',
			'query' => 'SELECT page_id FROM page_fan WHERE uid = me() AND page_id IN (' .
				join(',', $this->pages) . ')',
		));

		// User has to like all pages
		$missing = array_diff($this->pages, array_pluck($likes, 'page_id'));
		return empty($missing);

		// User has to like at least one page
		// return !empty($likes);
	}

	protected function _initialize()
	{
		$this->setData(null);
	}
	
}
