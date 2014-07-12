<?php
/*
 *     clinica.php
 *      
 *      Copyright 2011 Luciano Siqueira <lcnsqr@gmail.com>
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


/*
 * Create, read, update and delete in database
 */

class Clinica_mdl extends CI_Model {
	
	function __construct() {
		// Call the Model constructor
		parent::__construct();

	}
	
	/*
	 * Listar salas
	 */
	function get_salas() {
		$fields = array();
		$this->clinica_db->select('id, nome, tipo');
		$this->clinica_db->from('salas');
		$query = $this->clinica_db->get();
		foreach ($query->result() as $row) {
			$fields[] = array(
				'id' => $row->id,
				'nome' => $row->nome,
				'tipo' => $row->tipo
			);
		}
		return $fields;
	}

	/*
	 * Verificar atendente ocupando a sala no horário
	 */
	function get_salas_atendente($salas_id, $dia, $hora, $data){
		$this->clinica_db->select('atendentes.id AS atendente');
		$this->clinica_db->from('horarios');
		$this->clinica_db->join('salas', 'horarios.salas_id = salas.id', 'inner');
		$this->clinica_db->join('atendentes', 'horarios.atendentes_id = atendentes.id', 'inner');
		$this->clinica_db->where('salas.id', $salas_id);
		$this->clinica_db->where('horarios.dia', $dia);
		$this->clinica_db->where('horarios.hora', $hora);
		$this->clinica_db->where('horarios.inicio <=', $data);
		$this->clinica_db->where('horarios.termino >=', $data);
		$query = $this->clinica_db->get();
		if ($query->num_rows() > 0) {
			$row = $query->row();
			return $row->atendente;
		}
		else {
			return NULL;
		}
	}
	
	// Gerar faixas de horários para cada 
	// sala e verificar disponibilidade
	function get_salas_horarios($sala_id, $data){
		// 1 : segunda → 6 : sábado
		$horarios = array(1, 2, 3, 4, 5, 6);
		for ($d = 1; $d < 7; $d++){
			$horarios[$d] = array('manha' => array(), 'tarde' => array(), 'noite' => array());
			for ( $h = 8; $h < 12; $h++ ){
				// Consultar manhã
				$atendente = $this->clinica_mdl->get_salas_atendente($sala_id, $d, $h, $data);
				$horarios[$d]['manha'][] = array('horario' => $h, 'atendente' => intval($atendente), 'ocupado' => ! is_null($atendente));
			}
			for ( $h = 12; $h < 16; $h++ ){
				// Consultar tarde
				$atendente = $this->clinica_mdl->get_salas_atendente($sala_id, $d, $h, $data);
				$horarios[$d]['tarde'][] = array('horario' => $h, 'atendente' => intval($atendente), 'ocupado' => ! is_null($atendente));
			}
			for ( $h = 16; $h < 20; $h++ ){
				// Consultar noite
				$atendente = $this->clinica_mdl->get_salas_atendente($sala_id, $d, $h, $data);
				$horarios[$d]['noite'][] = array('horario' => $h, 'atendente' => intval($atendente), 'ocupado' => ! is_null($atendente));
			}
		}
		return $horarios;
	}

	/*
	 * Armazenar atendente
	 */
	function put_atendente($data){
		if ( (bool) $data['id'] ){
			/*
			 * Update
			 */
			$this->clinica_db->where('id', $data['id']);
			$this->clinica_db->update('atendentes', $data);
			return $data['id'];
		}
		else {
			/*
			 * Insert
			 */
			$inserted = $this->clinica_db->insert('atendentes', $data);
			if ($inserted) {
				return $this->clinica_db->insert_id();
			}
			else {
				return FALSE;
			}
		}
	}

	/*
	 * Remover atendente
	 */
	function delete_atendente($id){
		$this->clinica_db->query('DELETE agendamentos.* FROM agendamentos INNER JOIN horarios ON agendamentos.horarios_id = horarios.id WHERE horarios.atendentes_id = ' . intval($id));
		$this->clinica_db->where('horarios.atendentes_id', $id);
		$this->clinica_db->delete('horarios');
		$this->clinica_db->where('atendentes.id', $id);
		$this->clinica_db->delete('atendentes');
	}

	/*
	 * Armazenar ocupação da sala no horário
	 */
	function put_horario($data){
		$inserted = $this->clinica_db->insert('horarios', $data);
		if ($inserted) {
			return $this->clinica_db->insert_id();
		}
		else {
			return FALSE;
		}
	}

	/*
	 * listar todos Atendentes
	 */
	function get_atendentes() {
		$fields = array();
		$this->clinica_db->select('id, elementar_id, nome, telefone1, telefone2, registro, endereco, cidade, uf, cep, notificar');
		$this->clinica_db->from('atendentes');
		$this->clinica_db->order_by('nome', 'asc');
		$query = $this->clinica_db->get();
		$fields = array();
		foreach ($query->result() as $row) {
			$fields[] = array(
				'id' => $row->id,
				'elementar_id' => $row->elementar_id,
				'nome' => $row->nome,
				'telefone1' => $row->telefone1,
				'telefone2' => $row->telefone2,
				'registro' => $row->registro,
				'endereco' => $row->endereco,
				'cidade' => $row->cidade,
				'uf' => $row->uf,
				'cep' => $row->cep,
				'notificar' => $row->notificar,
				'username' => $this->access->get_account_username($row->elementar_id),
				'email' => $this->access->get_account_email($row->elementar_id),
				'lotacao' => $this->get_atendente_lotacao($row->id),
				'inicio' => $this->get_atendente_inicio($row->id),
				'termino' => $this->get_atendente_termino($row->id)
			);
		}
		return $fields;
	}

	/*
	 *  Lotacao da sala para o atendente
	 */
	function get_atendente_lotacao($atendentes_id){
		$this->clinica_db->select('lotacao');
		$this->clinica_db->from('atendentes');
		$this->clinica_db->where('id', $atendentes_id);
		$query = $this->clinica_db->get();
		$row = $query->row();
		return $row->lotacao;
	}

	/*
	 *  Data de inicio da ocupação da sala para o atendente
	 */
	function get_atendente_inicio($atendentes_id){
		$this->clinica_db->select('DATE(inicio) AS inicio');
		$this->clinica_db->from('horarios');
		$this->clinica_db->where('atendentes_id', $atendentes_id);
		$this->clinica_db->order_by('inicio', 'asc');
		$this->clinica_db->limit(1);
		$query = $this->clinica_db->get();
		if ( $query->num_rows() > 0 ){
			$row = $query->row();
			return $row->inicio;
		}
		return "1970-01-01";
	}

	/*
	 *  Data de término da ocupação da sala para o atendente
	 */
	function get_atendente_termino($atendentes_id){
		$this->clinica_db->select('DATE(termino) AS termino');
		$this->clinica_db->from('horarios');
		$this->clinica_db->where('atendentes_id', $atendentes_id);
		$this->clinica_db->order_by('termino', 'desc');
		$this->clinica_db->limit(1);
		$query = $this->clinica_db->get();
		if ( $query->num_rows() > 0 ){
			$row = $query->row();
			return $row->termino;
		}
		return "1970-01-01";
	}

	/*
	 *  Nome do atendente
	 */
	function get_atendente_nome($atendente_id){
		$this->clinica_db->select('nome');
		$this->clinica_db->from('atendentes');
		$this->clinica_db->where('id', $atendente_id);
		$query = $this->clinica_db->get();
		$row = $query->row();
		return $row->nome;
	}

	/*
	 *  Todos os horários do atendente
	 */
	function get_atendente_horarios($atendentes_id){
		$fields = array();
		$this->clinica_db->select('horarios.id AS id, horarios.inicio AS inicio, horarios.termino AS termino');
		$this->clinica_db->from('horarios');
		$this->clinica_db->join('atendentes', 'horarios.atendentes_id = atendentes.id', 'inner');
		$this->clinica_db->where('atendentes.id', $atendentes_id);
		$query = $this->clinica_db->get();
		return $query->result_array();
	}

	/*
	 *  Apenas os horários em vigor do atendente
	 */
	function get_atendente_horarios_vigentes($atendentes_id){
		$fields = array();
		$this->clinica_db->select('horarios.id AS id, horarios.inicio AS inicio, horarios.termino AS termino');
		$this->clinica_db->from('horarios');
		$this->clinica_db->join('atendentes', 'horarios.atendentes_id = atendentes.id', 'inner');
		$this->clinica_db->where('atendentes.id', $atendentes_id);
		$this->clinica_db->where('horarios.inicio <=', date("Y-m-d"));
		$this->clinica_db->where('horarios.termino >=', date("Y-m-d"));
		$query = $this->clinica_db->get();
		return $query->result_array();
	}

	/*
	 *  Dias de vencimento dos atendentes com aluguel vigente
	 */
	function get_atendentes_vencimento($ano, $mes){
		$query = $this->clinica_db->query("SELECT atendentes.id, atendentes.nome, DAY(vigencia.inicio) AS dia_vencimento FROM atendentes INNER JOIN (SELECT MIN(inicio) AS inicio, MAX(termino) AS termino, atendentes_id FROM horarios GROUP BY horarios.atendentes_id) AS vigencia ON atendentes.id = vigencia.atendentes_id WHERE UNIX_TIMESTAMP(vigencia.termino) > UNIX_TIMESTAMP('".$ano."-".$mes."-01');");
		return $query->result_array();
	}

	/*
	 *  Preço da hora do plano mensal no período
	 */
	function get_preco_mensal($periodo){
		$this->clinica_db->select('valor');
		$this->clinica_db->from('precos_mensal');
		$this->clinica_db->where('periodo', $periodo);
		$this->clinica_db->limit(1);
		$query = $this->clinica_db->get();
		$row = $query->row();
		return $row->valor;
	}

	/*
	 *  Uso da sala pelo atendente no dia da semana
	 */
	function get_atendente_uso($atendentes_id, $dia){
		$this->clinica_db->select('periodo, plano, salas_id, hora');
		$this->clinica_db->from('horarios');
		$this->clinica_db->where('atendentes_id', $atendentes_id);
		$this->clinica_db->where('dia', $dia);
		$query = $this->clinica_db->get();
		return $query->result_array();
	}

	/*
	 *  Id do atendente associado à conta
	 */
	function get_atendente_id($account_id){
		$this->clinica_db->select('id');
		$this->clinica_db->from('atendentes');
		$this->clinica_db->where('elementar_id', $account_id);
		$query = $this->clinica_db->get();
		if ( $query->num_rows() == 0 ) return;
		$row = $query->row();
		return $row->id;
	}

	/*
	 *  Id da conta associada ao atendente
	 */
	function get_atendente_account_id($atendente_id){
		$this->clinica_db->select('elementar_id');
		$this->clinica_db->from('atendentes');
		$this->clinica_db->where('id', $atendente_id);
		$query = $this->clinica_db->get();
		$row = $query->row();
		return $row->elementar_id;
	}

	/*
	 *  Agenda do atendente no dia informado
	 */
	function get_atendente_agenda($atendentes_id, $data, $dia){
		$this->clinica_db->select('horarios.id AS id, horarios.periodo AS periodo, horarios.hora AS hora, horarios.lotacao AS lotacao');
		$this->clinica_db->from('horarios');
		$this->clinica_db->where('horarios.dia', $dia);
		$this->clinica_db->where('horarios.atendentes_id', $atendentes_id);
		$this->clinica_db->where('horarios.inicio <=', $data);
		$this->clinica_db->where('horarios.termino >=', $data);
		$query = $this->clinica_db->get();
		$fields = array();
		foreach ($query->result() as $row) {
			$fields[] = array(
				'id' => $row->id,
				'periodo' => $row->periodo,
				'hora' => $row->hora,
				'lotacao' => $row->lotacao,
				'atendidos' => $this->clinica_mdl->get_horario_agendamentos($row->id, $data)
			);
		}
		return $fields;
	}

	/*
	 *  Expirar os horários em vigor do atendente
	 */
	function put_atendente_horarios_expirados($atendentes_id, $date){
		$termino = strtotime('-1 day' , strtotime($date));
		$data = array(
			'termino' => date('Y-m-d', $termino)
		);
		// Apagar onde inicio superior ao termino
		$this->clinica_db->where('horarios.atendentes_id', $atendentes_id);
		$this->clinica_db->where('horarios.inicio >=', $data['termino']);
		$this->clinica_db->delete('horarios');
		// Expirar vigentes
		$this->clinica_db->where('horarios.atendentes_id', $atendentes_id);
		$this->clinica_db->where('horarios.inicio <=', $data['termino']);
		$this->clinica_db->where('horarios.termino >=', $data['termino']);
		$this->clinica_db->update('horarios', $data);
	}

	/*
	 * Remover horario
	 */
	function delete_horario($horario_id){
		$this->clinica_db->delete('horarios', array('id' => intval($horario_id)));
	}
	
	/*
	 * Armazenar atendido
	 */
	function put_atendido($data){
		if ( (bool) $data['id'] ){
			/*
			 * Update
			 */
			$this->clinica_db->where('id', $data['id']);
			$this->clinica_db->update('atendidos', $data);
			return $data['id'];
		}
		else {
			/*
			 * Insert
			 */
			$inserted = $this->clinica_db->insert('atendidos', $data);
			if ($inserted) {
				return $this->clinica_db->insert_id();
			}
			else {
				return FALSE;
			}
		}
	}

	/*
	 * Armazenar agendamento
	 */
	function put_agendamento($data){
		/*
		 * Insert
		 */
		$inserted = $this->clinica_db->insert('agendamentos', $data);
		if ($inserted) {
			return $this->clinica_db->insert_id();
		}
		else {
			return FALSE;
		}
	}

	/*
	 * Desmarcar (ou reativar) agendamento
	 */
	function put_agendamento_cancelamento($id, $ativo){
		$this->clinica_db->where('id', $id);
		$this->clinica_db->update('agendamentos', array('cancelado' => $ativo));
		return $id;
	}

	/*
	 *  Agendamentos (com atendidos) do horario numa data específica
	 */
	function get_horario_agendamentos($horarios_id, $data){
		$this->clinica_db->select('agendamentos.id AS id, agendamentos.horario AS horario, atendidos.id AS atendidos_id, atendidos.nome AS nome, atendidos.telefone1 AS telefone1, agendamentos.procedimento AS procedimento');
		$this->clinica_db->from('agendamentos');
		$this->clinica_db->join('atendidos', 'agendamentos.atendidos_id = atendidos.id', 'inner');
		$this->clinica_db->where('agendamentos.horarios_id', $horarios_id);
		$this->clinica_db->where('DATE(agendamentos.horario)', $data);
		$this->clinica_db->where('agendamentos.cancelado', 0);
		$this->clinica_db->order_by('agendamentos.horario');
		$query = $this->clinica_db->get();
		$fields = array();
		foreach ($query->result() as $row) {
			$agendado = new DateTime($row->horario);
			$fields[] = array(
				'id' => $row->id,
				'horario' => $row->horario,
				'minuto' => $agendado->format('i'),
				'atendidos_id' => $row->atendidos_id,
				'nome' => $row->nome,
				'telefone1' => $row->telefone1,
				'procedimento' => $row->procedimento
			);
		}
		return $fields;
	}

	/*
	 *  Listar cadastros de atendidos
	 */
	function get_atendidos(){
		$this->clinica_db->from('atendidos');
		$this->clinica_db->order_by('id', 'desc');
		$query = $this->clinica_db->get();
		return $query->result();
	}

	/*
	 * Registro de log
	 */
	function put_log($descricao){
		$this->clinica_db->insert('log', array('descricao' => $descricao));
	}

}
