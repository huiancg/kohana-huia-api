<?php defined('SYSPATH') or die('No direct script access.');

Route::set('api_actions', 'api(/<model>(/<id>))', array(
		'id' => '\d+',
	))
	->defaults(array(
		'controller' => 'app',
		'action'     => 'index',
		'directory' => 'api'
	));

Route::set('api', 'api(/<controller>(/<action>(/<id>)))', array(
		'id' => '\d+',
	))
	->defaults(array(
		'controller' => 'app',
		'action'     => 'index',
		'directory' => 'api'
	));