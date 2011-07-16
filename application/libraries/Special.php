<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Special {

	function __construct()
	{
		
	}
	
	function get_special_targets()
	{
		$dropdown = array(
			'/servicos' => 'Serviços (/servicos)',
			'/contato' => 'Contato (/contato)'
		);
		return $dropdown;
	}

}

/* End of file Special.php */
/* Location: ./application/libraries/Special.php */
