<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Controller_Api_User extends Controller_Api_App {

	public function get()
	{
		// return only user data
		if ( ! Auth::instance()->logged_in())
		{
			throw HTTP_Exception::factory(404);
		}

		$user = Auth::instance()->get_user()->all_as_array();
		$result = $this->filter_expected(Arr::get($user, 0), 'user');

		$this->json($result);
	}

	public function action_login()
	{
		$this->request->headers('allow', Request::POST);

		if ($this->request->method() === Request::POST)
		{
			$success = Auth::instance()->login($this->request->post('username'), $this->request->post('password'));
			$this->json(array(
				'success' => $success
			));
		}
		else
		{
			$required = HTTP_Exception::factory(405);
			$required->allowed(Request::POST);
			throw $required;
		}
	}

	public function action_logout()
	{
		Auth::instance()->logout(TRUE, TRUE);
	}

}