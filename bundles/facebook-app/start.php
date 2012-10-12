<?php
/**
 * @author Martino di Filippo <puntodifuga@gmail.com>
 * @copyright 2012 Martino di Filippo
 * @license http://opensource.org/licenses/mit-license.php
 * @package FacebookApp (Laravel Bundle)
 * @version 1.0 - 2012-10-11
 */

Autoloader::map(array(
	'Facebook' => Bundle::path('facebook-app').'facebook-sdk/facebook.php',
	'FacebookApp' => Bundle::path('facebook-app').'facebookapp.php',
));

Laravel\IoC::singleton('facebook-app', function()
{
	$config = array();
	$config['appId'] = Config::get('facebook.app_id');
	$config['secret'] = Config::get('facebook.secret');
	$config['scope'] = Config::get('facebook.scope', []);
	$config['fileUpload'] = Config::get('facebook.file_upload', true);

	return new FacebookApp($config);
});

Event::listen('facebook.auth_required', function()
{
	Session::forget('facebook.user');

	$fbapp = IoC::resolve('facebook-app');
	$content = '<script>window.top.location = "' . $fbapp->authURL() . '";</script>';

	echo Response::prepare($content)->render();
	exit(1);
});
