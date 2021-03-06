<?php defined('SYSPATH') or die('No direct script access.');

Route::set('api_user', 'api/user(/<action>)', array(
    'id' => '\d+',
  ))
  ->defaults(array(
    'controller' => 'user',
    'action'     => 'index',
    'directory' => 'api'
  ));

Route::set('api_model_actions', 'api(/<model>(/<model_action>(/<id>)))', array(
    'id' => '\d+',
  ))
  ->filter(function($route, $params, $request) {
    $api = 'Api_'.ORM::get_model_name(Arr::get($params, 'model'));
    if ( ! class_exists($api))
    {
      return FALSE;
    }
    if ( ! method_exists($api, 'action_' . Arr::get($params, 'model_action', 'index')))
    {
      return FALSE;
    }
  })
  ->defaults(array(
    'controller'   => 'custom',
    'action'       => 'index',
    'model_action' => 'index',
    'directory'    => 'api'
  ));

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