<?php if (!defined('SK_PATH')) die ('No direct script access allowed');

class Welcome extends Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/welcome
	 *	- or -
	 * 		http://example.com/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * the config settings, it's displayed at http://example.com/
	 *
	 * So any other public methods will map
	 * to /index.php/welcome/<method_name>
	 */
	public function index()
	{
		v::set('name', 'Jackie Chan');
		v::render('welcome_message');
	}
}

/* End of file welcome.php
  Location: ./application/controllers/welcome.php */