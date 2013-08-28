<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View_wrapper
{
	/*
	 
		|----------------------------------------------------------------
		| Description
		|----------------------------------------------------------------
	
			Easily manage wrapper views around a stack of content views.

			Also handles:
				- Passing of data to ALL views ("global variables")
				- Loading of JS and CSS files (with builds)
				- Meta data such as:
					* Page Title
					* Robots
					* Description
					* Canonical URL
					* .. Easily add any number of additional meta data

	
	
		|----------------------------------------------------------------
		| Dependencies
		|----------------------------------------------------------------
	
			CodeIgniter

				application/helpers/array_replace_recursive_helper.php
				application/helpers/cache_buster_helper.php --> https://gist.github.com/chill117/5971376
	
	
		|----------------------------------------------------------------
		| Example Usage
		|----------------------------------------------------------------

			See 'application/controllers/examples.php'
	
	*/

	protected $_wrappers;

	protected $_wrapper_vars = array();

	protected $_global_vars = array();

	protected $_stack = array();
	protected $_sticky = array();

	protected $_css = array();
	protected $_js = array();

	protected $_config;

	function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->config->load('view_wrapper', true);

		$this->_config = $this->ci->config->item('view_wrapper');

		$this->_wrappers = $this->_config['wrappers'];
		
		$this->_css = $this->_config['load_css'];
		$this->_js = $this->_config['load_js'];

		if ($this->_config['build_css'] || $this->_config['build_js'])
			$this->ci->load->library('Build');

		$this->ci->load->helper('array_replace_recursive');

		if ($this->_config['use_cache_busting'])
			$this->ci->load->helper('cache_buster');

		$this->prepare_view_data();
	}

	protected function prepare_view_data()
	{
		$this->set_meta_variable('robots', 'index,follow');

		$this->prepare_view_data__body_classes();
	}

	protected function prepare_view_data__body_classes()
	{
		$uri_string = $this->ci->uri->uri_string();

		// Is this the Home view?
		if (trim($uri_string, '/') === '')
			$uri_string = '/home';

		$body_classes = explode('/', trim($uri_string, '/'));
		
		$this->set_wrapper_variable(reset($this->_wrappers['above']), 'body_classes', $body_classes);
	}

	public function push($return = false)
	{
		$html = '';

		$this->prepare_css();
		$this->prepare_js();

		$html .= $this->push_wrappers('above', true);

		if (count($this->_sticky) > 0)
			foreach ($this->_sticky as $args)
				$this->prepend_to_stack($args['view'], $args['data']);

		if (count($this->_stack) > 0)
			foreach ($this->_stack as $args)
				$html .= $this->ci->load->view($args['view'], $args['data'], true);
		
		$html .= $this->push_wrappers('below', true);

		if ($return)
			return $html;

		echo $html;
		exit;
	}

	public function set_page_title($title)
	{
		$this->set_meta_variable('title', $title);
	}

	public function append_to_page_title($title)
	{
		$current = $this->get_meta_variable('title');

		if (!empty($current))
			$title = $current . $this->_config['page_title_delimiter'] . $title;

		$this->set_meta_variable('title', $title);
	}

	public function prepend_to_page_title($title)
	{
		$current = $this->get_meta_variable('title');

		if (!empty($current))
			$title = $title . $this->_config['page_title_delimiter'] . $current;

		$this->set_meta_variable('title', $title);
	}

	public function sticky($view, $data = array())
	{
		$this->_sticky[] = array('view' => $view, 'data' => $data);
	}

	public function stack($view, $data = array())
	{
		$this->append_to_stack($view, $data);
	}

	public function append_to_stack($view, $data = array())
	{
		$this->_stack[] = array('view' => $view, 'data' => $data);
	}

	public function prepend_to_stack($view, $data = array())
	{
		array_unshift($this->_stack, array('view' => $view, 'data' => $data));
	}

	public function append_wrapper($where, $wrapper)
	{
		$this->_wrappers[$where][] = $wrapper;

		$this->_wrapper_vars[$wrapper] = array();
	}

	public function prepend_wrapper($where, $wrapper)
	{
		array_unshift($this->_wrappers[$where], $wrapper);

		$this->_wrapper_vars[$wrapper] = array();
	}

	public function set_wrappers($where, $wrappers)
	{
		$this->_wrappers[$where] = $wrappers;
	}

	public function prepend_to_wrapper_variable($wrapper, $variable, $value, $raw = false)
	{
		$current = $this->get_wrapper_variable($wrapper, $variable);

		if ($raw || !is_array($value))
			$value = array($value);

		if (!$current)
			// No current value; set it.
			return $this->set_wrapper_variable($wrapper, $variable, $value);

		if (!is_array($current))
			show_error('Cannot prepend to a wrapper variable that is not an array.');

		foreach ($value as $val)
			array_unshift($current, $val);

		$this->set_wrapper_variable($wrapper, $variable, $current);
	}

	public function append_to_wrapper_variable($wrapper, $variable, $value, $raw = false)
	{
		$current = $this->get_wrapper_variable($wrapper, $variable);

		if ($raw || !is_array($value))
			$value = array($value);

		if (!$current)
			// No current value; set it.
			return $this->set_wrapper_variable($wrapper, $variable, $value);

		if (!is_array($current))
			show_error('Cannot append to a wrapper variable that is not an array.');

		foreach ($value as $val)
			$current[] = $val;

		$this->set_wrapper_variable($wrapper, $variable, $current);
	}

	public function set_wrapper_variable($wrapper, $variable, $value)
	{
		$this->_wrapper_vars[$wrapper][$variable] = $value;
	}

	public function get_wrapper_variable($wrapper, $variable)
	{
		return 	isset($this->_wrapper_vars[$wrapper][$variable]) ?
					$this->_wrapper_vars[$wrapper][$variable] :
						null;
	}

	public function set_global_variable($name, $value)
	{
		$this->_global_vars[$name] = $value;
	}

	public function get_global_variable($name)
	{
		return 	isset($this->_global_vars[$name]) ?
					$this->_global_vars[$name] :
						null;
	}

	public function set_meta_variable($name, $value)
	{
		$meta = $this->get_meta_information();

		$meta[$name] = $value;

		$this->set_meta_information($meta);
	}

	public function get_meta_variable($name)
	{
		$meta = $this->get_meta_information();

		return isset($meta[$name]) ? $meta[$name] : null;
	}

	public function get_meta_information()
	{
		return $this->get_wrapper_variable(reset($this->_wrappers['above']), 'meta');
	}

	public function set_meta_information($meta)
	{
		$this->set_wrapper_variable(reset($this->_wrappers['above']), 'meta', $meta);
	}

	/*
		Add a JavaScript global variable to be included in the Footer wrapper.
	*/
	public function set_js_variable($variable, $value)
	{
		$js_variables = $this->get_wrapper_variable(end($this->_wrappers['below']), 'js_variables');

		$js_variables[$variable] = $value;

		$this->set_wrapper_variable(end($this->_wrappers['below']), 'js_variables', $js_variables);
	}

	/*
		Append to a JavaScript global variable.
	*/
	public function append_to_js_variable($variable, $value, $raw = false)
	{
		$current = $this->get_js_variable($variable);

		if ($raw || !is_array($value))
			$value = array($value);

		if (!$current)
			// No current value; set it.
			return $this->set_js_variable($variable, $value);

		if (!is_array($current))
			show_error('Cannot append to a js variable that is not an array.');

		foreach ($value as $key => $val)
			if (!is_int($key))
				$current[$key] = $val;
			else
				$current[] = $val;

		$this->set_js_variable($variable, $current);
	}

	public function get_js_variable($variable)
	{
		$js_variables = $this->get_wrapper_variable('footer', 'js_variables');

		return isset($js_variables[$variable]) ? $js_variables[$variable] : null;
	}

	/*
		Add a CSS file to be loaded in the Header wrapper.
	*/
	public function load_css($paths)
	{
		if (!is_array($paths))
			$paths = array($paths);

		foreach ($paths as $path)
			$this->_css[] = $path;
	}

	/*
		Add a JavaScript file to be loaded in the Footer wrapper.
	*/
	public function load_js($paths)
	{
		if (!is_array($paths))
			$paths = array($paths);

		foreach ($paths as $path)
			$this->_js[] = $path;
	}

	/*
		Use meta information to tell robots not to index the current page.
	*/
	public function no_robots()
	{
		$this->set_meta_variable('robots', 'noindex,follow');
	}

	/*
		If enabled, all CSS files will be combined into a single file.

		Adds all CSS files to the Header Wrapper's load_css variable.
	*/
	protected function prepare_css()
	{
		$this->set_wrapper_variable(reset($this->_wrappers['above']), 'load_css', array());

		/*
			Should all the CSS files be combined?
		*/
		if ($this->_config['build_css'])
		{
			$build_path = 	$this->ci->build->run(
								$this->_config['css_path'],
								$this->_css,
								'css'
							);

			$this->_css = array($build_path);
		}

		foreach ($this->_css as $path)
		{
			$path = $this->_config['css_path'] . '/' . $path;
			
			if ($this->_config['use_cache_busting'])
				$path .= cache_buster($path);

			$this->append_to_wrapper_variable(reset($this->_wrappers['above']), 'load_css', $path);
		}
	}

	/*
		If enabled, all CSS files will be combined into a single file.

		Adds all JavaScript files to the Footer Wrapper's load_js variable.
	*/
	protected function prepare_js()
	{
		$this->set_wrapper_variable(end($this->_wrappers['below']), 'load_js', array());

		/*
			Should all the JavaScript files be combined?
		*/
		if ($this->_config['build_js'])
		{
			$build_path = 	$this->ci->build->run(
								$this->_config['js_path'],
								$this->_js,
								'js'
							);

			$this->_js = array($build_path);
		}

		foreach ($this->_js as $path)
		{
			$path = $this->_config['js_path'] . '/' . $path;

			if ($this->_config['use_cache_busting'])
				$path .= cache_buster($path);

			$this->append_to_wrapper_variable(end($this->_wrappers['below']), 'load_js', $path);
		}
	}

	protected function push_wrappers($where, $return)
	{
		$html = '';

		$wrappers = $this->_wrappers[$where];

		if (!is_array($wrappers))
			$wrappers = explode(',', $wrappers);

		if (count($wrappers) > 0)
			foreach ($wrappers as $k => $view)
				$wrappers[$k] = trim($view);

		if (count($wrappers) > 0)
			foreach ($wrappers as $wrapper)
			{
				$vars = isset($this->_wrapper_vars[$wrapper]) ?
							$this->_wrapper_vars[$wrapper] :
								array();

				$vars = array_replace_recursive($this->_global_vars, $vars);

				$html .= $this->ci->load->view($wrapper, $vars, $return);
			}

		return $html;
	}

		/*
			Deprecated Methods
		*/
		public function get_wrapper_vars()
		{
			show_error('get_wrapper_vars() has been deprecated. Use get_wrapper_variable() instead.');
		}

		public function set_wrapper_vars()
		{
			show_error('set_wrapper_vars() has been deprecated. Use set_wrapper_variable() instead.');
		}

		public function set_wrappers_above()
		{
			show_error('set_wrappers_above() has been deprecated. Use set_wrappers() instead.');
		}

		public function set_wrappers_below()
		{
			show_error('set_wrappers_below() has been deprecated. Use set_wrappers() instead.');
		}

		public function set_global_vars()
		{
			show_error('set_global_vars() has been deprecated. Use set_global_variable() instead.');
		}
		
		public function set_meta_tag()
		{
			show_error('set_meta_tag() has been deprecated. Use set_meta_variable() instead.');
		}
		/*
			Deprecated Methods
		*/

}


/* End of file View_wrapper.php */
/* Location: ./application/libraries/View_wrapper.php */