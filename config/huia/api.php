<?php defined('SYSPATH') or die('No direct script access.');

return array(

	'queries' => array(
		
		'directions' => array(
			'ASC', 
			'DESC', 
			'RAND()'
		),

		'operations' => array(
			'=', 
			'<', 
			'<=', 
			'>', 
			'>=', 
			'!=', 
			'in', 
			'between', 
			'not in', 
			'not between', 
			'like', 
			'null', 
			'not null'
		),

		'fields' => NULL,
	),
	
	'permissions' => array(
		'write' => FALSE,
		'read' => TRUE,

		'query' => TRUE,

		'self_write' => TRUE,
		'self_read' => TRUE,
		
		'role_write' => NULL,
		'role_read' => NULL,
	),

	'custom_permissions' => array(
		'user' => array(
			'read' => FALSE,
		),
		'log' => array(
			'read' => FALSE,
		),
		/*
		'product' => array(
			'write' => TRUE,
		),
		'category' => array(
			'read' => TRUE,
		),
		*/
	),
	
	'filters' => array(
		'ignored' => '^user$|password|_at$',
		'expected' => NULL,
		'query' => NULL,
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
			'ignored' => '^gastronomies|published|posts|user$|password|_at$',
			'query' => array(
				array('where', 'published', '=', 1),
			),
		),
		*/
	),
);