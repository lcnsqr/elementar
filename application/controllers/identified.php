<?php
/*
 *      identified.php
 *      
 *      Copyright 2012 Luciano Siqueira <lcnsqr@gmail.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/** 
 * Identified Class 
 * 
 * Returns true if session is not anonymous
 * 
 * @package Elementar 
 * @author Luciano Siqueira <lcnsqr@gmail.com>
 * @link https://github.com/lcnsqr/elementar 
 */
class Identified extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		
		// Elementar DB
		$this->elementar = $this->load->database('elementar', TRUE);

		// Storage model 
		$this->load->model('Storage', 'storage');
		
		// Load encryption key before session library
		$this->config->set_item('encryption_key', $this->storage->get_config('encryption_key'));

		// Session library
		$this->load->library('session');
		
	}

	/**
	 * Just show true if there is a valid
	 * authenticated session for the client
	 * 
	 * @access public
	 * @return null
	 */
	function index()
	{
		// Check identified session
		echo ( (bool) $this->session->userdata('account_id') ) ? 'true' : 'false';
	}
	
	/**
	 * Locate a valid authenticated session using
	 * the sent session_id
	 *
	 * @access public
	 * @return null
	 */
	function session_id()
	{
		$session_id = $this->uri->segment(3);
		$this->elementar->select('user_data');
		$this->elementar->from('session');
		$this->elementar->where('session_id', $session_id);
		$this->elementar->limit(1);
		$query = $this->elementar->get();
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$session = @unserialize(stripslashes($row->user_data));
			if (is_array($session) && array_key_exists('account_id', $session) )
			{
				echo ( (bool) $session['account_id'] ) ? 'true' : 'false';
				return;
			}
		}
		echo 'false';
	}

}