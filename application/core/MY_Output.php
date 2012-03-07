 <?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * My Output Class
 *
 * Responsible for sending final output to browser
 * write & display function modified to preserve 
 * content type header
 *
 * @author		Luciano Siqueira lcnsqr@gmail.com
 */
class MY_Output extends CI_Output {

	function set_output_json($response)
	{
		// execution time
		$CI =& get_instance();
		$elapsed = array('elapsed_time' => $CI->benchmark->elapsed_time('total_execution_time_start', 'total_execution_time_end'));
		$response = array_merge($response, $elapsed);
		
		/*
		 * Render JSON response
		 */
		$this->set_header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		$this->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		$this->set_header("Cache-Control: post-check=0, pre-check=0");
		$this->set_header("Pragma: no-cache");
		$this->set_header("Content-type: application/json");
		
		$data = json_encode($response);
		$this->set_output($data);
	}

	/**
	 * Write a Cache File
	 *
	 * @access	public
	 * @param 	string
	 * @return	void
	 */
	function _write_cache($output)
	{
		$CI =& get_instance();
		$path = $CI->config->item('cache_path');

		$cache_path = ($path == '') ? APPPATH.'cache/' : $path;

		if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
		{
			log_message('error', "Unable to write cache file: ".$cache_path);
			return;
		}

		$uri =	$CI->config->item('base_url').
				$CI->config->item('index_page').
				$CI->uri->uri_string();

		$cache_path .= md5($uri);

		if ( ! $fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE))
		{
			log_message('error', "Unable to write cache file: ".$cache_path);
			return;
		}

		$expire = time() + ($this->cache_expiration * 60);

		if (flock($fp, LOCK_EX))
		{
			fwrite($fp, json_encode(array($this->headers, $expire, $output)));
			flock($fp, LOCK_UN);
		}
		else
		{
			log_message('error', "Unable to secure a file lock for file at: ".$cache_path);
			return;
		}
		fclose($fp);
		@chmod($cache_path, FILE_WRITE_MODE);

		log_message('debug', "Cache file written: ".$cache_path);
	}

	// --------------------------------------------------------------------

	/**
	 * Update/serve a cached file
	 *
	 * @access	public
	 * @param 	object	config class
	 * @param 	object	uri class
	 * @return	void
	 */
	function _display_cache(&$CFG, &$URI)
	{
		$cache_path = ($CFG->item('cache_path') == '') ? APPPATH.'cache/' : $CFG->item('cache_path');

		// Build the file path.  The file name is an MD5 hash of the full URI
		$uri =	$CFG->item('base_url').
				$CFG->item('index_page').
				$URI->uri_string;

		$filepath = $cache_path.md5($uri);

		if ( ! @file_exists($filepath))
		{
			return FALSE;
		}

		if ( ! $fp = @fopen($filepath, FOPEN_READ))
		{
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$cache = '';
		if (filesize($filepath) > 0)
		{
			//$cache = fread($fp, filesize($filepath));
			$file = fread($fp, filesize($filepath));
		}

		flock($fp, LOCK_UN);
		fclose($fp);
		
		list($headers, $expire, $cache) = json_decode($file, TRUE);

		// Has the file expired? If so we'll delete it.
		if (time() >= intval($expire) )
		{
			if (is_really_writable($cache_path))
			{
				@unlink($filepath);
				log_message('debug', "Cache file has expired. File deleted");
				return FALSE;
			}
		}
		
		// Recover the headers
		$this->headers = $headers;

		// Display the cache
		$this->_display($cache);
		log_message('debug', "Cache file is current. Sending it to browser.");
		return TRUE;
	}

}
