<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Examples extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->library('View_wrapper');
	}

	/*
		Single view wrapped inside header and footer.
	*/
	public function simple_page()
	{
		$this->view_wrapper->set_page_title('A Simple Page!');

		$this->view_wrapper->stack('examples/simple_page');
		$this->view_wrapper->push();
	}

	/*
		Multiple views stacked on top of one another.
	*/
	public function multiple_views()
	{
		$articles = array();

		$articles[0] = array();
		$articles[0]['title'] = 'Sample Article';
		$articles[0]['author'] = 'Author MacAuthorson';
		$articles[0]['body'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit amet vitae augue.';

		$articles[1] = array();
		$articles[1]['title'] = 'Another Article!';
		$articles[1]['author'] = 'Author MacAuthorson';
		$articles[1]['body'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit amet vitae augue.';

		foreach ($articles as $article)
			$this->view_wrapper->stack('examples/article', $article);

		$this->view_wrapper->set_page_title('This is an Example of an Article Index');

		$this->view_wrapper->push();
	}

}