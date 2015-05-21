<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Controller_Api_App extends Controller {

  public $models = array();

  public $model = NULL;

  public $model_name = NULL;

  protected static $_has_user = array();

  protected static $_config = NULL;

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

  public function config($group, $type, $model_name = NULL)
  {
    $model_name = ($model_name) ? $model_name : $this->model_name;
    $config = (self::$_config === NULL) ? self::$_config = Kohana::$config->load('huia/api') : self::$_config;
    $custom = $config->get('custom_'.$group);
    $regexp = Arr::path($custom, strtolower(Inflector::singular($model_name)).'.'.$type);
    return ($regexp !== NULL) ? $regexp : Arr::get($config->get($group), $type);
  }

  public function before()
  {
    parent::before();

    $this->models = ORM_Autogen::get_models();

    $this->model_name = ($this->request->param('model')) ? $this->request->param('model') : $this->request->controller();
    
    if ( ! $this->model_name OR $this->model_name === 'App')
    {
      return;
    }

    $this->model_name = ORM::get_model_name($this->model_name);
    
    $this->model = ORM::factory($this->model_name);
  }

  public function direction($direction)
  {
    if ( ! in_array($direction, $this->config('queries', 'directions')))
    {
      throw HTTP_Exception::factory(403, __('Invalid direction \':direction\'.', array(':operation' => $operation)));
    }
    return strtoupper($direction);
  }

  public function operation($operation)
  {
    if ( ! in_array($operation, $this->config('queries', 'operations')))
    {
      throw HTTP_Exception::factory(403, __('Invalid operation \':operation\'.', array(':operation' => $operation)));
    }
    return strtoupper($operation);
  }

  public function field($field)
  {
    $config = $this->config('queries', 'fields');
    if ($config AND ! in_array($field, $config))
    {
      throw HTTP_Exception::factory(403, __('Invalid field \':field\'.', array(':field' => $field)));
    }
    return $field;
  }

  public function query($model, $queries, $limit = NULL)
  {
    if ( ! $queries OR empty($queries))
    {
      return $model;
    }

    foreach ((array)$queries as $query)
    {
      switch ($query[0])
      {
        case 'where':
          $model->{$query[0]}(self::field($query[1]), self::operation($query[2]), $query[3]);
          break;
        
        case 'order_by':
          $field = Arr::get($query, 1);
          $direction = Arr::get($query, 2);
          
          // random
          if ($field === 'RAND()')
          {
            $field = DB::expr('RAND()');
          }
          
          $model->order_by(self::field($field), $direction);
          break;

        case 'or':
          $model->or_where(self::field($query[1]), self::operation($query[2]), $query[3]);
          break;

        case 'limit':
          $limit = $query[1];
          break;

        case 'offset':
        case 'distinct':
        case 'group_by':
          $model->{$query[0]}(self::field($query[1]));
          break;

        case 'where_open':
        case 'where_close':
          $model->{$query[0]}();
          break;
        
        case 'or_open':
          $model->or_where_open();
          break;
        
        case 'or_close':
          $model->or_where_close();
          break;

        case 'sum':
          $name = (isset($query[2]) AND $query[2]) ? $query[2] : 'total_'.$query[1];
          $model->select(array(DB::expr('SUM('.self::field($query[1]).')'), $name));
          break;

        default:
          throw HTTP_Exception::factory(403, __('Invalid method :method!', array(':method' => $query[0])));
          break;
      }
    }

    if ($limit)
    {
      $model->limit($limit);
    }

    return $model;
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

    $method = $this->request->post('_method');
    $method = ($method) ? $method : $this->request->method();

    if ($method === Request::POST)
    {
      $this->save($this->request->post());
    }
    else if ($method === Request::GET)
    {
      $this->get();
    }
    else if ($method === Request::DELETE)
    {
      $this->delete();
    }
  }

  public function get()
  {
    $_caching = $this->request->post('_caching');

    $caching = Kohana::$caching AND ! Session::instance()->get('auth_user') AND $_caching;
    
    $key = 'api.'. $this->model_name . '.' . 
                Request::current()->uri() . '.' . 
                http_build_query(Request::current()->query()) . '.' . 
                http_build_query(Request::current()->post());

    if ($caching)
    {
      if ($result = Cache::instance()->get($key))
      {
        $this->response->headers('From-Cache', '1');
        return $this->json($result);
      }
    }

    if ($this->config('permissions', 'query'))
    {
      $queries = $this->request->post('_query');
      $queries = ($queries) ? $queries : array(); 
    }
    else
    {
      $queries = array();
    }

    if ($this->request->param('id'))
    {
      $queries[] = array('where', 'id', '=', $this->request->param('id'));
      $queries[] = array('limit', 1);
    }
    
    if ($filter_queries = $this->config('filters', 'query'))
    {
      $queries = Arr::merge($queries, $filter_queries);
    }

    $this->model = $this->query($this->model, $queries, 100);

    $count = clone $this->model;
    if ($this->request->param('id') AND ! $count->count_all())
    {
      throw HTTP_Exception::factory(404, 'Not found!');;
    }

    if ( ! $this->request->param('id') AND empty($queries) AND ! $this->config('permissions', 'list'))
    {
        throw HTTP_Exception::factory(403, __('Cant list this object.'));
    }

    $read = $this->config('permissions', 'read');

    if ( ! $read AND $role_read = $this->config('permissions', 'role_read'))
    {
      $user = Auth::instance()->get_user();
      if ( ! $user OR ! $user->has('roles', ORM::factory('Role')->where('name', 'IN', $role_read)))
      {
        throw HTTP_Exception::factory(403, 'This object require role_read permission!');
      }

      $read = TRUE;
    }
    else if ( ! $read AND $this->has_user())
    {
      $self_read = $this->config('permissions', 'self_read');

      $user = Auth::instance()->get_user();
      if ( ! $user AND $self_read)
      {
        throw HTTP_Exception::factory(403, 'This object require self_read permission!');
      }
      
      if ($self_read)
      {
        $this->model->where('user_id', '=', ($user) ? $user->id : NULL);
      }

      $read = TRUE;
    }

    if ( ! $read)
    {
      throw HTTP_Exception::factory(403, 'This object require read permission!');
    }

    if ($this->request->post('_count_all'))
    {
      $result = $this->model->count_all();
    }
    else
    {
      $result = $this->model->all_as_array();
      $result = $this->filter_expected($result);
    }

    if ($caching)
    {
      Cache::instance()->set($key, $result, $_caching);
    }

    return $this->json($result);
  }

  public function filter_expected($values, $model_name = NULL)
  {
    $ignored = $this->config('filters', 'ignored', $model_name);
    $expected = $this->config('filters', 'expected', $model_name);

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
      $user = Auth::instance()->get_user();

      if ( ! $user)
      {
        throw HTTP_Exception::factory(403, 'Login required!');
      }

      if ($this->model->user_id != NULL AND $this->model->user_id != $user->id)
      {
        throw HTTP_Exception::factory(403, 'This object is not yours!');
      }
      
      $values['user_id'] = $user->id;
    }
    return $values;
  }

  public function files()
  {
    if (isset($_FILES))         
    {     
      foreach($_FILES as $name => $file)
      {
        if (Upload::not_empty($file))
        {
          $filename = uniqid().'_'.$file['name'];
          $filename = preg_replace('/\s+/u', '_', $filename);
          $dir = DOCROOT.'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.strtolower($this->model_name);

          create_dir($dir);

          Upload::save($file, $filename, $dir);
          $this->model->$name = $filename;
        }
      }
    }
  }

  public function filter_fields($values)
  {
    foreach ($this->model->as_array() as $key => $value)
    {
      if ($key === 'ip')
      {
        $values['ip'] = Request::$client_ip;
      }
      else if ($key === 'user_agent')
      {
        $values['user_agent'] = Request::$user_agent;
      }
    }
    return $values;
  }

  // save
  public function save($values, $update = FALSE)
  {
    $write = $this->config('permissions', 'write');
    if ( ! $write)
    {
      throw HTTP_Exception::factory(403, 'Cant write this object.');
    }

    $values = $this->filter_expected($values);
    $values = $this->filter_user($values);
    $values = $this->filter_fields($values);

    $this->model->values($values);

    try
    {
      $this->files();

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

      $result = $this->model->all_as_array();
      $result = $this->filter_expected($result);
      return $this->json($result);
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

  public function delete()
  {
    $values = $this->model->as_array();
    
    if ( ! Arr::get($values, 'id') OR ! $this->request->param('id'))
    {
      throw HTTP_Exception::factory(404, 'Not found!');
    }
    
    // check user
    $values = $this->filter_user($values);
    
    $this->model->delete();
  }

}
