<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array();

/*
	String to put between appended/prepended page titles.
*/
$config['page_title_delimiter'] = ' | ';

/*
	Views to be loaded around the stacked views.
*/
$config['wrappers'] = array(
	'above' => array('header'),
	'below' => array('footer')
);

/*
	Whether or not to use cache busting on CSS/JS URLs.
*/
$config['use_cache_busting'] = false;

/*
	Base path to the CSS directory.
*/
$config['css_path'] = '/css';

/*
	Base path to the JS directory.
*/
$config['js_path'] = '/js';

/*
	Whether or not to combine CSS/JS files into build files.
*/
$config['build_css'] 	= false;
$config['build_js'] 	= false;

/*
	CSS files to load in the Header wrapper.
*/
$config['load_css'] = array();

/*
	JS files to load in the Footer wrapper.
*/
$config['load_js'] = array();


/*
	Environment-specific configurations.
*/
switch (ENVIRONMENT)
{
	//case 'development':
	case 'testing':
	case 'production':
		$config['build_css'] 	= true;
		$config['build_js'] 	= true;
	break;
}

/* End of file view_wrapper.php */
/* Location: ./application/config/view_wrapper.php */