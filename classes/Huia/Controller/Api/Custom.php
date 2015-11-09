<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Controller_Api_Custom extends Controller {

  public function action_index()
  {
    $model = $this->request->param('model');
    $action = 'action_' . ($this->request->param('model_action') ? $this->request->param('model_action') : 'index');
    $api = 'Api_'.ORM::get_model_name($model);
    $model_api = new $api;
    $this->response->json($model_api->{$action}());
  }

}