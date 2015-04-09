<?php defined('SYSPATH') or die('No direct script access.');

return array(
	
	'permissions' => array(
		'write' => FALSE,
		'read' => FALSE,

		'self_write' => TRUE,
		'self_read' => TRUE,
		
		'role_write' => NULL,
		'role_read' => NULL,
	),

	'custom_permissions' => array(
		/*
		'product' => array(
			'write' => TRUE,
		),
		*/
	),
	
	'filters' => array(
		'ignored' => '^user$|password|_at$',
		'expected' => NULL,
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