<?php

class M_session extends CI_Model {
	
	/*
	 * Validade do cookie da sessão (em segundos)
	 */
	var $EXPIRATION;
	
	/*
	 * Identificador da sessão
	 */
	var $SESSION_ID;

	function __construct()
	{
		// Call the Model constructor
		parent::__construct();

		$this->EXPIRATION = 86400;  // 24h

		$cookie = get_cookie('__session', TRUE);
		
		if ( $cookie === FALSE )
		{
			// Criar cookie (24 horas de duração)
			$cookie = array(
				'name'   => 'session',
				'value'  => random_string('unique'),
				'expire' => $this->EXPIRATION,
				'domain' => $this->input->server(),
				'path'   => '/',
				'prefix' => '__'
			);
			set_cookie($cookie);

			$this->SESSION_ID = $cookie['value'];

			/*
			 * Registrar o código de sessão na tabela
			 */
			$data = array(
			   'hash' => $cookie['value'] ,
			   'ip_address' => $this->session->userdata('ip_address') ,
			   'user_agent' => $this->session->userdata('user_agent')
			);
			$this->db_acc->insert('acc_session', $data); 

		}
		else 
		{
			$this->SESSION_ID = $cookie;
		}
	}

	function session_id() 
	{
		return $this->SESSION_ID;
	}
	
}
