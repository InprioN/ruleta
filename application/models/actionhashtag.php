<?php

class ActionHashtag extends Action {

	private $tags = ['#aperol', '#test'];

	protected function _check()
	{
		$fbapp = IoC::resolve('facebook-app');
		$since = $this->getData()['since'];
		$posts = $fbapp->api("/me/statuses?fields=message&limit=1000&since=$since");

		foreach ($posts['data'] as $post)
			foreach ($this->tags as $tag)
				if (strpos($post['message'], $tag) !== false)
					return true;
		
		return false;
	}

	protected function _initialize()
	{
		$this->setData(array('since' => time()));
	}
	
}
