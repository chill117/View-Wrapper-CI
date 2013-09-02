<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 
	|----------------------------------------------------------------
	| Description
	|----------------------------------------------------------------
 
		Force client browser to download fresh CSS/JS file whenever the file is modified on the server.
 
 
	|----------------------------------------------------------------
	| Dependencies
	|----------------------------------------------------------------
 
		CodeIgniter
 
 
	|----------------------------------------------------------------
	| Example Usage
	|----------------------------------------------------------------
 
	<link rel="stylesheet" href="/css/reset.css<?= cache_buster('/css/reset.css'); ?>" />

		OR

	<script src="/js/modernizr.js<?= cache_buster('/js/modernizr.js'); ?>"></script>
 
*/

function cache_buster($path, $hash_query_string = true)
{
	$file = FCPATH . $path;

	if (!file_exists($file))
		return '';

	$mtime = filemtime($file);

	if ($hash_query_string)
		$query_string = sha1($mtime);
	else
		$query_string = date('Y-m-d', $mtime) . 'T' . date('H:i:s', $mtime);

	return '?' . $query_string;
}


/* End of file cache_buster_helper.php */
/* Location: ./application/helpers/cache_buster_helper.php */