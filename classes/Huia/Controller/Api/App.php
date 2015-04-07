<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Controller_Api_App extends Controller {

	public $models = array();

	public $model = NULL;

	public $model_name = NULL;

	protected static $_has_user = array();

	// Colocar isso no request
	public function json($data)
	{
		$this->template = NULL;
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		$this->response->body(json_encode($data));
	}

	protected function has_user()
	{
		if ( ! isset(Controller_Api_App::$_has_user[$this->model_name]))
		{
			Controller_Api_App::$_has_user[$this->model_name] = Arr::get($this->model->list_columns(), 'user_id');
		}
		return Controller_Api_App::$_has_user[$this->model_name];
	}

	public function before()
	{
		parent::before();

		$this->models = ORM::get_models();

		$this->model_name = ($this->request->param('model')) ? $this->request->param('model') : $this->request->controller();
		
		if ( ! $this->model_name OR $this->model_name === 'App')
		{
			return;
		}
		
		$this->model_name = ORM::get_model_name($this->model_name);
		$this->model = ORM::factory($this->model_name);

		if ($this->request->param('id'))
		{
			$this->model->where('id', '=', $this->request->param('id'));
		}
	}

	public function action_index()
	{
		if ( ! $this->model_name OR $this->model_name === 'App')
		{
			$services = array();
			$models = $this->models;
			$models[] = 'User';
			foreach ($models as $model)
			{
				$url = Kohana::$base_url . 'api/' . strtolower($model) . '/';
				$services[$model] = $url;
			}
			return $this->json($services);
		}

		if ($this->request->method() === Request::POST)
		{
			$this->post($this->request->post());
		}
		else if ($this->request->method() === Request::GET)
		{
			$this->get();
		}
		else if ($this->request->method() === Request::PUT)
		{
			$this->put($this->request->put());
		}
		else if ($this->request->method() === Request::DELETE)
		{
			$this->delete();
		}
	}

	public function get()
	{
		$count = clone $this->model;
		if ($this->request->param('id') AND ! $count->count_all())
		{
			throw HTTP_Exception_404::factory('Not found!');;
		}

		// return only user data
		if ($this->has_user())
		{
			$user = Auth::instance()->get_user();
			if ($this->request->param('id') AND ! $user)
			{
				throw HTTP_Exception::factory(403, 'This object is not yours!');
			}
			$this->model->where('user_id', '=', ($user) ? $user->id : NULL);
		}

		$result = ($this->request->param('id')) ? $this->model->find()->all_as_array() : $this->model->all_as_array();

		$result = $this->filter_expected($result);
		
		return $this->json($result);
	}

	public function config($type, $model_name = NULL)
	{
		$model_name = ($model_name) ? $model_name : $this->model_name;
		$config = Kohana::$config->load('huia/api');
		$custom_filters = $config->get('custom_filters');
		$regexp = Arr::path($custom_filters, strtolower(Inflector::singular($model_name)).'.'.$type);
		return ($regexp !== NULL) ? $regexp : Arr::get($config->get('filters'), $type);
	}

	public function filter_expected($values, $model_name = NULL)
	{
		$ignored = $this->config('ignored', $model_name);
		$expected = $this->config('expected', $model_name);

		foreach ($values as $key => $value)
		{
			if (is_array($value))
			{
				$_model_name = ( ! is_int($key)) ? $key : $model_name;
				$values[$key] = $this->filter_expected($value, $_model_name);
			}

			// remove unexpected
			if ($ignored AND preg_match('/('. $ignored .')/', $key))
			{
				unset($values[$key]);
			}

			// allow only expected
			if ($expected AND ! is_int($key) AND ! in_array($key, $expected))
			{
				unset($values[$key]);
			}
		}
		return $values;
	}

	public function filter_user($values)
	{
		if ($this->has_user())
		{
			$user_id = Auth::instance()->get_user()->id;

			if ($this->model->user_id != NULL AND Arr::get($values, 'user_id') != $user_id)
			{
				throw HTTP_Exception_403::factory(403, 'This object is not yours!');
			}
			
			$values['user_id'] = $user_id;
		}
		return $values;
	}

	// update
	public function post($values)
	{
		$values = $this->filter_expected($values);
		$values = $this->filter_user($values);
		
		$this->model->values($values);

		try
		{
			$this->model->save();
			
			// add has many
			foreach ($this->model->has_many() as $name => $values)
			{
				// through
				if (Arr::get($values, 'through'))
				{
					$ids = $this->request->post($name);

					if ( ! $ids)
					{
						continue;
					}
					
					$this->model->remove($name);
					$this->model->add($name, $ids);
				}
			}

			return $this->json($this->model->as_array());
		}
		catch (ORM_Validation_Exception $e)
		{
			$errors = $e->errors('models');
			if ( ! $errors)
			{
				$errors = array($e->getMessage());
			}
			$this->json(array('errors' => $errors));
		}
	}

	// create and overwrite
	public function put()
	{
		$this->json(array('PUT'));
	}

	public function delete()
	{
		$this->json(array('DELETE'));
	}

}
