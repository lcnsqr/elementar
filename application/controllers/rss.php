<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rss extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		/*
		 * CI helpers
		 */
		$this->load->helper(array('string', 'security', 'cookie', 'url'));
		
		/*
		 * Session CI library
		 */
		$this->load->library('session');

		/*
		 * Account DB
		 */
		$this->db_acc = $this->load->database('account', TRUE);

		/*
		 * Account model
		 */
		$this->load->model('M_account', 'account', TRUE);

		/*
		 * Session model
		 */
		$this->load->model('M_session', 'sess', TRUE);

		/*
		 * CMS DB
		 */
		$this->db_cms = $this->load->database('cms', TRUE);

		/*
		 * CMS Model
		 */
		$this->load->model('M_cms', 'cms', TRUE);
	}

	function index()
	{
		header("Content-Type: application/xml; charset=utf-8");

/*
			$details = '<?xml version="1.0" encoding="UTF-1" ?>
				<rss version="2.0">
					<channel>
						<title>'. $row['title'] .'</title>
						<link>'. $row['link'] .'</link>
						<description>'. $row['description'] .'</description>
						<language>'. $row['language'] .'</language>
						<image>
							<title>'. $row['image_title'] .'</title>
							<url>'. $row['image_url'] .'</url>
							<link>'. $row['image_link'] .'</link>
							<width>'. $row['image_width'] .'</width>
							<height>'. $row['image_height'] .'</height>
						</image>';
			$items .= '<item>
				<title>'. $row["title"] .'</title>
				<link>'. $row["link"] .'</link>
				<description><![CDATA['. $row["description"] .']]></description>
			</item>';
		}
		$items .= '</channel>
				</rss>';
*/

	}

}

/* End of file rss.php */
/* Location: ./application/controllers/rss.php */
