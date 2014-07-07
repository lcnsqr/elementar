<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Clinica {

	/*
	 * CodeIgniter Instance
	 */
	private $CI;
	
	/*
	 * i18n settings
	 */
	private $LANG = 'pt';
	private $URI_PREFIX = '';
	
	/*
	 * Enabling
	 */
	public static $ENABLED = TRUE; 
	
	function __construct($params) {
		$this->CI =& get_instance();
		
		$this->CI->output->disable_cache();

		/*
		 * i18n: Default language
		 */
		$this->LANG = $params['lang'];
		$this->URI_PREFIX = $params['uri_prefix'];
		
		// Clinica DB
		$dsn = 'mysqli://clinica:5tcfth0t5@localhost/clinicasistema';
		$this->CI->clinica_db = $this->CI->load->database($dsn, TRUE);
		$this->CI->load->model('Clinica_mdl', 'clinica_mdl');

		// Access model
		$this->CI->load->model('Access', 'access');
	}

	function index(){
		$this->CI->output->set_output(".");
	}

	function salas(){
		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( $group_id != 1 ){
			$this->_auth();
		}

		// Data de referência
		$ano = trim($this->CI->input->get('ano', TRUE));
		$mes = trim($this->CI->input->get('mes', TRUE));
		$dia = trim($this->CI->input->get('dia', TRUE));
		if ( $ano == "" || $mes == "" || $dia == ""){
			$date = date("Y-m-d");
		}
		else {
			$date = "$ano-$mes-$dia";
		}
		$salas = $this->CI->clinica_mdl->get_salas();
		$data = array(
			'title' => $this->CI->config->item('site_name'),
			'js' => array(
				JQUERY,
				'/js/jquery.mask.min.js'
			),
			'atendentes' => $this->CI->clinica_mdl->get_atendentes(),
			'salas' => array()
		);
		foreach($salas as $sala){
			$data['salas'][] = array(
				'id' => $sala['id'],
				'nome' => $sala['nome'],
				'tipo' => $sala['tipo'],
				'horarios' => $this->CI->clinica_mdl->get_salas_horarios($sala['id'], $date)
			);
		}
		$this->CI->load->view('clinica/salas', $data);
	}
	
	function xhr_salas_associar_mensal(){
		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( $group_id != 1 ){
			$response = array(
				'done' => FALSE,
				'message' => "Sessão expirada"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		if ( ! $this->CI->input->is_ajax_request() )
			exit($this->CI->lang->line('elementar_no_direct_script_access'));

		// Datas de início e término da associação
		$inicio = trim($this->CI->input->post('atendenteInicio', TRUE));
		if ( strlen($inicio) == 0 ){
			// Padrão inicio = hoje
			$inicio = new DateTime(date("Y-m-d"));
		}
		else {
			$inicio = new DateTime($inicio);
		}
		$termino = trim($this->CI->input->post('atendenteTermino', TRUE));
		if ( strlen($termino) == 0 ){
			// Padrão termino = inicio + 1 ano
			$termino = new DateTime($inicio->format('Y-m-d'));
			$termino->add(new DateInterval("P1Y"));
		}
		else {
			$termino = new DateTime($termino);
		}

		$data = array(
			'id' => $this->CI->input->post('atendenteId', TRUE),
			'elementar_id' => $this->CI->input->post('accountId', TRUE),
			'nome' => trim($this->CI->input->post('atendenteNome', TRUE)),
			'telefone1' => trim($this->CI->input->post('atendenteTelefone1', TRUE)),
			'telefone2' => trim($this->CI->input->post('atendenteTelefone2', TRUE)),
			'registro' => trim($this->CI->input->post('atendenteRegistro', TRUE)),
			'endereco' => trim($this->CI->input->post('atendenteEndereco', TRUE)),
			'cidade' => trim($this->CI->input->post('atendenteCidade', TRUE)),
			'uf' => trim($this->CI->input->post('atendenteUf', TRUE)),
			'cep' => trim($this->CI->input->post('atendenteCep', TRUE)),
			'notificar' => trim($this->CI->input->post('notificar', TRUE))
		);
		if ( strlen($data['nome']) == 0 ){
			$response = array(
				'done' => FALSE,
				'message' => "Campo “Nome” ausente"
			);
			$this->CI->output->set_output_json($response);
			return;
		}
		if ( strlen($data['telefone1']) == 0 ){
			$response = array(
				'done' => FALSE,
				'message' => "Campo “Telefone 1” ausente"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		$lotacao = (int)$this->CI->input->post('lotacao', TRUE);
		if ($lotacao != 0){
			$data['lotacao'] = $lotacao;
		}
		// Armazenar atendente
		$atendentes_id = $this->CI->clinica_mdl->put_atendente($data);

		// Antes de associar, expirar previamente associados
		$this->CI->clinica_mdl->put_atendente_horarios_expirados($atendentes_id, $inicio->format('Y-m-d'));

		$horarios = json_decode($this->CI->input->post('horarios', TRUE), TRUE);
		foreach($horarios as $horario){
			// TODO: Checar se horário está livre
			$data = array(
				'periodo' => $horario['periodo'],
				'plano' => 'mensal',
				'atendentes_id' => $atendentes_id,
				'salas_id' => $horario['sala'],
				'hora' => $horario['horario'],
				'dia' => $horario['dia'],
				'inicio' => $inicio->format('Y-m-d'),
				'termino' => $termino->format('Y-m-d'),
				'lotacao' => $this->CI->clinica_mdl->get_atendente_lotacao($atendentes_id)
			);
			$this->CI->clinica_mdl->put_horario($data);
		}

		// Response
		$response = array(
			'done' => TRUE,
			'atendente' => $atendentes_id,
			'atendentes' => $this->CI->clinica_mdl->get_atendentes(),
			'message' => "Horários registrados com sucesso"
		);
		$this->CI->output->set_output_json($response);
	}

	/**
	 * Cobranças no período
	 */
	function cobrancas(){
		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( $group_id != 1 ){
			$this->_auth();
		}

		$data = array(
			'title' => $this->CI->config->item('site_name'),
			'js' => array(
				JQUERY
			),
			'atendentes' => $this->CI->clinica_mdl->get_atendentes()
		);
		$this->CI->load->view('clinica/cobrancas', $data);
	}
	
	/**
	 * Cobranças no mês
	 */
	function xhr_cobrancas(){
		if ( ! $this->CI->input->is_ajax_request() )
			exit($this->CI->lang->line('elementar_no_direct_script_access'));

		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( $group_id != 1 ){
			$response = array(
				'done' => FALSE,
				'message' => "Sessão expirada"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		// Ano da agenda
		$ano = intval($this->CI->input->post('ano', TRUE));
		// Mês da agenda
		$mes = intval($this->CI->input->post('mes', TRUE));

		// Obter todos os atendentes e seus 
		// respectivos dias de vencimento
		$vencimentos = $this->CI->clinica_mdl->get_atendentes_vencimento($ano, $mes);
		// Acumulado dos atendentes
		$contas = array();
		foreach($vencimentos as $vencimento){
			// Incluir atendente
			$conta = array(
				'atendente_id' => $vencimento['id'],
				'atendente_nome' =>  $vencimento['nome'], 
				'dia_vencimento' => $vencimento['dia_vencimento'],
				'extrato' => array(),
				'valor' => 0
			);
			$dia_vencimento = $vencimento['dia_vencimento'];
			$data_vencimento = new DateTime($ano . "-" . $mes . "-" . $dia_vencimento);

			// Começar no dia do vencimento no mês anterior
			$referencia = new DateTime($ano . "-" . $mes . "-" . $dia_vencimento);
			$referencia->modify('-1 months');
			// Listar todos os dias do mês de referência
			while ( $referencia->format('Y-m-d') != $data_vencimento->format('Y-m-d') ){
				// Acumular toda vez que aparecer o dia da 
				// semana do atendente numa data do período
				$uso = $this->CI->clinica_mdl->get_atendente_uso($vencimento['id'], $referencia->format('w'));
				if ( count($uso) > 0 ){
					// Incluir dia no extrato
					$conta['extrato'] = array_merge($conta['extrato'], array($referencia->format('Y-m-d') => array()));
					foreach( $uso as $hora ){
						// Detalhes
						$conta['extrato'][$referencia->format('Y-m-d')][] = $hora;
						// Somar custo
						$conta['valor'] += (float)$this->CI->clinica_mdl->get_preco_mensal($hora['periodo']);
					}
				}
				// Dia seguinte
				$referencia->add(new DateInterval("P1D"));
			}
			$contas[] = $conta;
		}

		// Response
		$response = array(
			'conta' => $contas,
			'done' => TRUE
		);
		$this->CI->output->set_output_json($response);
	}

	/**
	 * Agendamento de atendimento
	 */
	function agenda(){
		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( ! ( $group_id == 1 || $group_id == 5 ) ){
			$this->_auth();
		}

		$data = array(
			'title' => $this->CI->config->item('site_name'),
			'js' => array(
				JQUERY,
				'/js/jquery.mask.min.js'
			),
			'atendentes' => $this->CI->clinica_mdl->get_atendentes(),
			'atendidos' => $this->CI->clinica_mdl->get_atendidos()
		);
		$this->CI->load->view('clinica/agenda', $data);
	}
	
	/**
	 * Agenda do atendente (para agendamentos)
	 */
	function xhr_agenda_atendente(){
		if ( ! $this->CI->input->is_ajax_request() )
			exit($this->CI->lang->line('elementar_no_direct_script_access'));

		// Atendente
		$id = $this->CI->input->post('atendenteId', TRUE);

		// Autorização
		$account_id = $this->CI->clinica_mdl->get_atendente_account_id($id);
		$session_id = $this->CI->session->userdata('account_id');
		$group_id = $this->CI->session->userdata('group_id');
		if ( ! ( $group_id == 1 || $group_id == 5 ) && ($session_id != $account_id) ){
			$response = array(
				'done' => FALSE,
				'message' => "Sessão expirada"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		// Ano da agenda
		$ano = trim($this->CI->input->post('ano', TRUE));
		// Mês da agenda
		$mes = trim($this->CI->input->post('mes', TRUE));

		// Response
		$response = array(
			'done' => TRUE,
			'agenda' => $this->_agenda_atendente($id, $ano, $mes)
		);
		$this->CI->output->set_output_json($response);
	}

	/*
	 * Gerar agenda do atendente para o ano e mês atuais
	 */
	function _agenda_atendente($id, $ano, $mes){
		// Listar todos os dias do respectivo mês
		$dia = new DateTime($ano . "-" . $mes . "-01");
		$agenda = array();
		while ( $dia->format('m') == $mes ){
			$atendenteAgenda = $this->CI->clinica_mdl->get_atendente_agenda($id, $dia->format('Y-m-d'), $dia->format('w'));
			if ( count($atendenteAgenda) > 0 ){
				$agenda[] = array($dia->format('d') => $atendenteAgenda);
			}
			$dia->add(new DateInterval("P1D"));
		}
		return $agenda;
	}

	/**
	 * Marcar agendamento
	 */
	function xhr_agendamento(){
		if ( ! $this->CI->input->is_ajax_request() )
			exit($this->CI->lang->line('elementar_no_direct_script_access'));

		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( ! ( $group_id == 1 || $group_id == 5 ) ){
			$response = array(
				'done' => FALSE,
				'message' => "Sessão expirada"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		// Dados do agendamento
		$atendenteId = $this->CI->input->post('atendenteId', TRUE);
		$ano = $this->CI->input->post('ano', TRUE);
		$mes = $this->CI->input->post('mes', TRUE);
		$dia = $this->CI->input->post('dia', TRUE);
		$wdia = $this->CI->input->post('wdia', TRUE);
		$hora = $this->CI->input->post('hora', TRUE);
		$min = $this->CI->input->post('min', TRUE);
		$horarios_id = (int)$this->CI->input->post('horarioId', TRUE);

		// Dados do atendido
		$data = array(
			'id' => $this->CI->input->post('atendidoId', TRUE),
			'nome' => trim($this->CI->input->post('atendidoNome', TRUE)),
			'telefone1' => trim($this->CI->input->post('atendidoTelefone1', TRUE)),
			'telefone2' => trim($this->CI->input->post('atendidoTelefone2', TRUE)),
			'endereco' => trim($this->CI->input->post('atendidoEndereco', TRUE)),
			'cidade' => trim($this->CI->input->post('atendidoCidade', TRUE)),
			'uf' => trim($this->CI->input->post('atendidoUf', TRUE)),
			'cep' => trim($this->CI->input->post('atendidoCep', TRUE))
		);
		if ( strlen($data['nome']) == 0 ){
			$response = array(
				'done' => FALSE,
				'message' => "Campo “Nome” ausente"
			);
			$this->CI->output->set_output_json($response);
			return;
		}
		if ( strlen($data['telefone1']) == 0 ){
			$response = array(
				'done' => FALSE,
				'message' => "Campo “Telefone 1” ausente"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		// Armazenar atendido
		$atendidos_id = $this->CI->clinica_mdl->put_atendido($data);
		// Armazenar agendamento
		$horario = new DateTime($ano . "-" . $mes . "-" . $dia . " " . $hora . ":" . $min);
		// Verificar se horário não está ocupado
		$horarios = $this->CI->clinica_mdl->get_horario_agendamentos($horarios_id, $horario->format('Y-m-d'));
		foreach( $horarios as $atendido ){
			$agendado = new DateTime($atendido['horario']);
			if ( (int)$min == (int)$agendado->format('i') ){
				// Horário ocupado
				$response = array(
					'done' => FALSE,
					'message' => "Ocupado"
				);
				$this->CI->output->set_output_json($response);
				return;
			}
		}
		$data = array(
			'atendidos_id' => $atendidos_id,
			'horarios_id' => $horarios_id,
			'horario' => $horario->format('Y-m-d H:i'),
			'procedimento' => trim($this->CI->input->post('atendidoProcedimento', TRUE))
		);
		$agendamentos_id = $this->CI->clinica_mdl->put_agendamento($data);

		// Response
		$response = array(
			'done' => TRUE,
			'atendidosId' => $atendidos_id,
			'agenda' => $this->_agenda_atendente($atendenteId, $ano, $mes),
			'atendidos' => $this->CI->clinica_mdl->get_atendidos()
		);
		$this->CI->output->set_output_json($response);
	}

	/**
	 * Desmarcar agendamento
	 */
	function xhr_agendamento_cancelar(){
		if ( ! $this->CI->input->is_ajax_request() )
			exit($this->CI->lang->line('elementar_no_direct_script_access'));

		// Autorização
		$group_id = $this->CI->session->userdata('group_id');
		if ( ! ( $group_id == 1 || $group_id == 5 ) ){
			$response = array(
				'done' => FALSE,
				'message' => "Sessão expirada"
			);
			$this->CI->output->set_output_json($response);
			return;
		}

		$atendenteId = $this->CI->input->post('atendenteId', TRUE);
		$ano = $this->CI->input->post('ano', TRUE);
		$mes = $this->CI->input->post('mes', TRUE);

		// Dados do agendamento
		$id = $this->CI->input->post('agendamento_id', TRUE);
		$this->CI->clinica_mdl->put_agendamento_cancelamento($id, 1);

		// Response
		$response = array(
			'done' => TRUE,
			'agenda' => $this->_agenda_atendente($atendenteId, $ano, $mes),
			'message' => "Cancelado"
		);
		$this->CI->output->set_output_json($response);
	}

	/**
	 * Página do atendente
	 */
	function atendente(){
		$account_id = $this->CI->session->userdata('account_id');
		if ( $account_id == 0 ) $this->_auth();

		$account_id = $this->CI->session->userdata('account_id');
		$data = array(
			'title' => $this->CI->config->item('site_name'),
			'nome' => $this->CI->clinica_mdl->get_atendente_nome($this->CI->clinica_mdl->get_atendente_id($account_id)),
			'js' => array(
				JQUERY
			),
			'account_id' => $account_id,
			'atendente_id' => $this->CI->clinica_mdl->get_atendente_id($account_id)
		);
		$this->CI->load->view('clinica/atendente', $data);
	}
	
	/**
	 * Exit if is not an authorized session
	 * 
	 * @access public
	 * @return void
	 */
	function _auth(){
		$data = array(
			'is_logged' => FALSE,
			'title' => "Clínica",
			'js' => array(
				JQUERY,
				JS_ACCOUNT
			),
			'css' => array(),
			'action' => '/' . uri_string()
		);
		$login = $this->CI->load->view('clinica/account', $data, TRUE);
		exit($login);
	}

	/**
	 * Trocar senha
	 * 
	 * @access public
	 * @return void
	 */
	function senha(){
		$data = array(
			'is_logged' => FALSE,
			'title' => "Clínica",
			'js' => array(
				JQUERY,
				JS_ACCOUNT
			),
			'css' => array(),
			'action' => '/' . uri_string()
		);
		$login = $this->CI->load->view('clinica/account_password', $data, TRUE);
		exit($login);
	}
}
