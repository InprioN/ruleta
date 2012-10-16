<?php
/**
 * @author Martino di Filippo <puntodifuga@gmail.com>
 * @copyright 2012 Martino di Filippo
 * @license http://opensource.org/licenses/mit-license.php
 * @package FacebookApp (Laravel Bundle)
 * @version 1.0 - 2012-10-11
 */

Autoloader::map([
	'Facebook' => Bundle::path('facebook-app').'facebook-sdk/facebook.php',
	'FacebookApp' => Bundle::path('facebook-app').'facebookapp.php',
]);

Laravel\IoC::singleton('facebook-app', function()
{
	$config = [];
	$config['appId'] = Config::get('facebook.app_id');
	$config['secret'] = Config::get('facebook.secret');
	$config['namespace'] = Config::get('facebook.namespace');
	$config['scope'] = Config::get('facebook.scope', []);
	$config['fileUpload'] = Config::get('facebook.file_upload', true);

	return new FacebookApp($config);
});

Event::listen('facebook.auth_required', function($reason)
{
	Session::forget('facebook.user');

	Log::debug('FBAuth - ' . $reason);

	$fbapp = IoC::resolve('facebook-app');
	$content = '<script>window.top.location = "' . $fbapp->authURL() . '";</script>';

	while (ob_get_level() > 0)
		ob_end_clean();

	echo Response::make($content)->render();
	exit();
});

Route::filter('before', function()
{
	IoC::resolve('facebook-app');
});
