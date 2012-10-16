<?php

Route::any('', function()
{
	return Redirect::to_route('profile');
});

Route::get('picture/(:num)', ['as' => 'picture', function($id)
{
	$filename = 'pictures/' . $id . '.jpg';
	$path = path('public') . $filename;

	if (File::exists($path))
		return Redirect::to(asset($filename));

	$fbapp = IoC::resolve('facebook-app');
	$call = "$id/picture?type=large";
	$url = 'https://graph.facebook.com/' . $call;
	$picture = $fbapp->api($call . '&redirect=false');

	if (!isset($picture['data']) || $picture['data']['is_silhouette'])
		return Redirect::to($url);

	$image = imagecreatefromjpeg($picture['data']['url']);

	if (!$image)
		return Redirect::to($url);

	if (!imagefilter($image, IMG_FILTER_GRAYSCALE))
		return Redirect::to($url);

	imagejpeg($image, $path, 75);
	return Redirect::to(asset($filename));
}]);

Route::group(['before' => 'auth'], function()
{

	Route::any('profile', ['as' => 'profile', function()
	{
		return View::make('profile', [
			'user' => User::me(),
		]);
	}]);

	Route::get('check/(:num)', ['as' => 'check', function($id)
	{
		try {
			$action = Action::find($id);
			if (!$action)
				throw new \Exception('Action not found');
			
			return Response::json(['done' => $action->check()]);
		} catch (\Exception $e) {
			return Response::json(['error' => $e->getMessage()]);
		}
	}]);

	Route::get('spin', ['as' => 'spin', function()
	{
		try {
			return Response::eloquent(User::me()->spin());
		} catch (\Exception $e) {
			return Response::json(['error' => $e->getMessage()]);
		}
	}]);

});


Event::listen('404', function()
{
	return Response::error('404');
});

Event::listen('500', function()
{
	return Response::error('500');
});


Route::filter('auth', function()
{
	$fbapp = IoC::resolve('facebook-app');
	$fbapp->auth();

	$user = User::ensure($fbapp->me());
});

Route::filter('csrf', function()
{
	if (Request::forged()) return Response::error('500');
});
