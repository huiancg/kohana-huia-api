<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'filters' => array(
		'ignored' => '^user$|password|_at$',
	),
	'custom_filters' => array(
		'user' => array(
			'expected' => array('id', 'email', 'username'),
		),
		/*
		'product' => array(
			'ignored' => FALSE,
		),
		'category' => array(
			'expected' => array('id', 'name'),
		),
		*/
	),
);