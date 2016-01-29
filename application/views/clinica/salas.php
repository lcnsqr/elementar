<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $title; ?> &ndash; Salas</title>
<link rel="stylesheet" href="/css/clinica/style.css">

<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>

<!-- jquery ui -->
<link href="/js/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="/js/jquery-ui/jquery-ui.js"></script>
<script src="/js/jquery-ui/jquery.ui.datepicker-pt-BR.js"></script>

</head>
<body>


<div id="salas-dias">
<h2>Ocupação de Salas</h2>
<h2>Data de referência</h2>
<p>Ocupação na data</p>

<div id="cal-month">
<div id="cal-month-nav">
<p id="cal-month-name"></p>
<div id="cal-month-back"><p><a href="«">«</a></p></div>
<div id="cal-month-forward"><p><a href="»">»</a></p></div>
</div>
<div id="cal-month-header">
<div class="cal-day even"><p>D</p></div>
<div class="cal-day odd"><p>S</p></div>
<div class="cal-day even"><p>T</p></div>
<div class="cal-day odd"><p>Q</p></div>
<div class="cal-day even"><p>Q</p></div>
<div class="cal-day odd"><p>S</p></div>
<div class="cal-day even"><p>S</p></div>
<hr>
</div>
<div id="cal-month-table"></div>
</div> <!-- cal-month -->
<script>
$("#cal-month-back > p > a, #cal-month-forward > p > a").on("click", function(e){
	e.preventDefault();
	var divMonth = document.getElementById("cal-month");
	var year = divMonth.getAttribute("data-year");
	var month = divMonth.getAttribute("data-month");
	if ( $(this).parents("div").first().attr("id") == "cal-month-back" ) month--;
	else month++
	var mes = new Date(year, month);
	// Alterar mês
	calMonth(mes.getFullYear(), mes.getMonth());
});

var calMonth = function(year, month, day){
	var dia = new Date(year, month);
	var divMonthName = document.getElementById("cal-month-name");
	switch ( month ){
		case 0: divMonthName.innerHTML = "Janeiro &ndash; " + year; break;
		case 1: divMonthName.innerHTML = "Fevereiro &ndash; " + year; break;
		case 2: divMonthName.innerHTML = "Março &ndash; " + year; break;
		case 3: divMonthName.innerHTML = "Abril &ndash; " + year; break;
		case 4: divMonthName.innerHTML = "Maio &ndash; " + year; break;
		case 5: divMonthName.innerHTML = "Junho &ndash; " + year; break;
		case 6: divMonthName.innerHTML = "Julho &ndash; " + year; break;
		case 7: divMonthName.innerHTML = "Agosto &ndash; " + year; break;
		case 8: divMonthName.innerHTML = "Setembro &ndash; " + year; break;
		case 9: divMonthName.innerHTML = "Outubro &ndash; " + year; break;
		case 10: divMonthName.innerHTML = "Novembro &ndash; " + year; break;
		case 11: divMonthName.innerHTML = "Dezembro &ndash; " + year; break;
	}
	var divMonthTable = document.getElementById("cal-month-table");
	divMonthTable.innerHTML = "";
	for ( d = 0; d < dia.getDay(); d++ ){
		var div = document.createElement("div");
		if ( d == 0 ) var col = "inative";
		else var col = ( d % 2 == 0 ) ? "even" : "odd";
		div.setAttribute("class", "cal-day " + col);
		var p = document.createElement("p");
		p.innerHTML = "&nbsp;";
		div.appendChild(p);
		divMonthTable.appendChild(div);
	}
	while ( dia.getMonth() == month ){
		var div = document.createElement("div");
		if ( dia.getDay() == 0 ) var col = "inative";
		else var col = ( dia.getDay() % 2 == 0 ) ? "even" : "odd";
		div.setAttribute("class", "cal-day " + col);
		div.setAttribute("data-day", dia.getDate());
		div.setAttribute("data-wday", dia.getDay());
		var p = document.createElement("p");
		if ( dia.getDay() == 0 ){
			p.innerHTML = dia.getDate();
		}
		else {
			var anchor = document.createElement("a");
			anchor.innerHTML = dia.getDate();
			anchor.setAttribute("href", "?ano=" + year + "&mes=" + (month+1) + "&dia=" + dia.getDate());
			anchor.setAttribute("data-ano", year);
			anchor.setAttribute("data-mes", month + 1);
			anchor.setAttribute("data-dia", dia.getDate());
			anchor.addEventListener("click", function(event){
				event.preventDefault();
				var href = "/clinica/salas?ano="+this.getAttribute("data-ano")+"&mes="+this.getAttribute("data-mes")+"&dia="+this.getAttribute("data-dia");
				var atendenteId = document.querySelector("input#atendente-id").getAttribute("value"); 
				if ( atendenteId != "" ){
					href += "&atendente="+atendenteId;
				}
				href += "&x="+window.scrollX+"&y="+window.scrollY;
				window.location.href = href;
			});
			p.appendChild(anchor);
			if ( dia.getDate() == day ) div.setAttribute("class", div.getAttribute("class") + " selected");
		}
		div.appendChild(p);
		// Bug dia duplicado
		if ( $("div.cal-day[data-day=\"" + dia.getDate() + "\"]").length == 0 ){
			divMonthTable.appendChild(div);
		}
		dia.setDate(dia.getDate() + 1);
	}
	if ( dia.getDay() != 0 ){
		dia.setDate(dia.getDate() - 1);
		for ( d = dia.getDay() + 1; d <= 6; d++ ){
			var div = document.createElement("div");
			if ( d == 0 ) var col = "inative";
			else var col = ( d % 2 == 0 ) ? "even" : "odd";
			div.setAttribute("class", "cal-day " + col);
			var p = document.createElement("p");
			p.innerHTML = "&nbsp;";
			div.appendChild(p);
			divMonthTable.appendChild(div);
		}
	}
	var hr = document.createElement("hr");
	divMonthTable.appendChild(hr);

	var divMonth = document.getElementById("cal-month");
	divMonth.setAttribute("data-year", year);
	divMonth.setAttribute("data-month", month);
	divMonth.setAttribute("data-day", day);
}

$.urlParam = function(name){
	var results = new RegExp('[\\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
	if ( results == null ) return "";
	return results[1] || 0;
}

if ( $.urlParam("ano") == "" || $.urlParam("mes") == "" || $.urlParam("dia") == "" ){ 
	var agora = new Date();
	calMonth(agora.getFullYear(), agora.getMonth(), agora.getDate());
}
else {
	var agora = new Date($.urlParam("ano"), $.urlParam("mes"), $.urlParam("dia"));
	calMonth(agora.getFullYear(), agora.getMonth() - 1, agora.getDate());
}
</script>
</div> <!-- salas-dias -->

<div id="atendente">
<div id="salas-atendente">
<h2>Dados do atendente</h2>
<form id="atendente-antigo" action="/clinica/salas-atendente">
<select id="atendente-id" name="atendente-id">
<?php foreach($atendentes as $atendente): ?>
<option data-registro="<?php echo $atendente['registro']; ?>" data-notificar="<?php echo $atendente['notificar']; ?>" data-lotacao="<?php echo $atendente['lotacao']; ?>" data-email="<?php echo $atendente['email']; ?>" data-username="<?php echo $atendente['username']; ?>" data-elementar-id="<?php echo $atendente['elementar_id']; ?>" data-atendente="<?php echo $atendente['id']; ?>" data-nome="<?php echo $atendente['nome']; ?>" data-telefone1="<?php echo $atendente['telefone1']; ?>" value="<?php echo $atendente['id']; ?>"><?php echo $atendente['nome']; ?></option>
<?php endforeach; ?> <!-- atendentes -->
<option value="0" selected>Novo...</option>
</select>
</form>

<p class="enviar-remover"><a href="remover" id="atendente-remover">Excluir atendente</a></p>

<form autocomplete="off" id="atendente-form" action="/clinica/salas-alugar">
	<input type="hidden" name="atendente-elementar-id" id="atendente-elementar-id" value="0">
	<input type="hidden" name="atendente-id" id="atendente-id" value="0">
	<p><label for="atendente-username">Nome de usuário:</label><br><input type="text" name="atendente-username" id="atendente-username" value=""></p>
	<p><label for="atendente-email">Email:</label><br><input type="text" name="atendente-email" id="atendente-email" value=""></p>
	<p><label for="atendente-password">Senha:</label><br><input type="password" name="atendente-password" id="atendente-password" value=""></p>
	<p><label for="atendente-nome">Nome:</label><br><input type="text" name="atendente-nome" id="atendente-nome" value=""></p>
	<p><label for="atendente-telefone1">Telefone:</label><br><input type="text" name="atendente-telefone1" id="atendente-telefone1" value="" class="campo-telefone"></p>
	<p><label for="atendente-registro">CRX:</label><br><input type="text" name="atendente-registro" id="atendente-registro" value=""></p>
	<p><label for="atendente-lotacao">Lotação/hora:</label><br><input type="text" name="atendente-lotacao" id="atendente-lotacao" value=""></p>
	<!--
	<p><label for="atendente-inicio">Inicio:</label><br><input type="text" name="atendente-inicio" id="atendente-inicio" value=""></p>
	<p><label for="atendente-termino">Término:</label><br><input type="text" name="atendente-termino" id="atendente-termino" value=""></p>
	-->
	<p><input type="checkbox" name="atendente-notificar" id="atendente-notificar" value=""><label for="atendente-notificar">Notificar pacientes por SMS</label></p>
	<!-- <p><input type="submit" value="Salvar"></p> -->
	<p class="enviar-salvar"><a href="submit" id="atendente-salvar">Salvar</a></p>
</form>
</div> <!-- atendente -->

</div> <!-- salas-atendente -->

<div id="salas-quadro">
<h2>Ocupação das salas</h2>
<script>
var selecionarHorario = function(event, botao){
	event.preventDefault();
	// Marcar ou desmarcar
	var trocar = ! parseInt(botao.getAttribute("data-selecionado"));
	// Desmarcar atual
	var atual = document.querySelector("a.botao[data-selecionado='1']");
	if ( atual ){
		atual.setAttribute("data-selecionado", 0);
	}
	// Configurar form Editar horário
	var editarHorario = document.querySelector(".editar-horario[data-sala='"+botao.getAttribute("data-sala")+"']");
	// Checar se horário disponível
	if ( botao.getAttribute("data-ocupado") == "0" ){
		// Editar horário apenas se há atendente selecionado
		var atendenteId = document.querySelector("input#atendente-id").getAttribute("value"); 
		if ( atendenteId == "0" ){
			alert("Escolha um atendente antes de selecionar um horário vago");
			editarHorario.setAttribute("data-aberto", 0);
			return;
		}
		editarHorario.querySelector("input[name='atendente']").setAttribute("value", atendenteId);
		//var atendenteNome = document.querySelector("input#atendente-nome").getAttribute("value"); 
		editarHorario.querySelector("span.editar-horario-atendente").innerHTML = $("input#atendente-nome").val() + " (novo horário)";
	}
	else {
		editarHorario.querySelector("input[name='atendente']").setAttribute("value", botao.getAttribute("data-atendente"));
		editarHorario.querySelector("span.editar-horario-atendente").innerHTML = botao.getAttribute("data-nome");
	}
	editarHorario.querySelector("span.diasemana").innerHTML = botao.getAttribute("data-diasemana");
	editarHorario.querySelector("span.hora").innerHTML = botao.getAttribute("data-horario");
	editarHorario.querySelector("input[name='horario']").setAttribute("value", botao.getAttribute("data-id"));
	editarHorario.querySelector("input[name='dia']").setAttribute("value", botao.getAttribute("data-dia"));
	editarHorario.querySelector("input[name='hora']").setAttribute("value", botao.getAttribute("data-horario"));
	editarHorario.querySelector("input[name='periodo']").setAttribute("value", botao.getAttribute("data-periodo"));
	if ( botao.getAttribute("data-lotacao") != "" ){
		editarHorario.querySelector("input[name='lotacao']").setAttribute("value", botao.getAttribute("data-lotacao"));
	}
	else {
		editarHorario.querySelector("input[name='lotacao']").setAttribute("value", $("input#atendente-lotacao").val());
	}
	var inicio = new Date(botao.getAttribute("data-inicio"));
	inicio.setDate(inicio.getDate() + 1);
	inicio.setMonth(inicio.getMonth() + 1);
	var termino = new Date(botao.getAttribute("data-termino"));
	termino.setDate(termino.getDate() + 1);
	termino.setMonth(termino.getMonth() + 1);
	$(editarHorario).find( "input[name='periodo-inicio']" ).datepicker( "setDate", inicio.getDate()+"/"+inicio.getMonth()+"/"+inicio.getFullYear() );
	$(editarHorario).find( "input[name='periodo-termino']" ).datepicker( "setDate", termino.getDate()+"/"+termino.getMonth()+"/"+termino.getFullYear() );

	botao.setAttribute("data-selecionado", (trocar) ? 1 : 0);
	editarHorario.setAttribute("data-aberto", (trocar) ? 1 : 0);
}
</script>
<?php foreach($salas as $sala): ?>
<p>Sala: <strong><?php echo $sala['nome']; ?></strong></p>
<p>Modalidade: <strong><?php echo $sala['tipo']; ?></strong></p>
<div class="bot-painel" data-exclusivo="0">

<div class="bot-periodo-nomes">
<ul>
<li>Manhã</li>
<li>Tarde</li>
<li>Noite</li>
<hr class="salas-clear">
</ul>
</div> <!-- bot-periodo-nomes -->

<?php foreach($sala['horarios'] as $dia => $periodos): ?>
<?php if ( $dia == 0 ) continue; ?>
<div class="bot-dia">
<h3><?php switch($dia){
case 1: echo "Segunda"; break;
case 2: echo "Terça"; break;
case 3: echo "Quarta"; break;
case 4: echo "Quinta"; break;
case 5: echo "Sexta"; break;
case 6: echo "Sábado"; break;
} ?></h3>
<?php foreach($periodos as $periodo => $horas): ?>
<ul class="bot-periodo">
<?php foreach($horas as $hora): ?>
<li><a data-diasemana="<?php switch($dia){ case 1: echo "Segunda"; break; case 2: echo "Terça"; break; case 3: echo "Quarta"; break; case 4: echo "Quinta"; break; case 5: echo "Sexta"; break; case 6: echo "Sábado"; break; } ?>" onclick="selecionarHorario(event, this)" data-selecionado="0" class="botao" href="<?php echo $hora['horario']; ?>" data-id="<?php echo $hora['id']; ?>" data-ocupado="<?php echo ($hora['ocupado']) ? 1 : 0; ?>" data-sala="<?php echo $sala['id']; ?>" data-dia="<?php echo $dia; ?>" data-periodo="<?php echo $periodo; ?>" data-lotacao="<?php echo $hora['lotacao']; ?>" data-horario="<?php echo $hora['horario']; ?>" data-inicio="<?php echo $hora['inicio']; ?>" data-termino="<?php echo $hora['termino']; ?>" data-atendente="<?php echo $hora['atendente']; ?>" data-nome="<?php echo $hora['nome']; ?>"<?php if ($hora['nome'] != ""): ?> title="Ocupado por <?php echo $hora['nome']; ?>"<?php endif; ?> data-destaque="0"><?php echo $hora['horario']; ?></a></li>
<?php endforeach; ?> <!-- periodos -->
<hr class="salas-clear">
</ul>
<?php endforeach; ?> <!-- periodos -->
</div> <!-- bot-dia -->
<?php endforeach; ?> <!-- dias -->
<hr class="salas-clear">
<div class="editar-horario" data-sala="<?php echo $sala['id']; ?>" data-aberto="0">
<h3>Editar horário: <?php echo $sala['nome']; ?></h3>
<h4><span class="diasemana"></span>, <span class="hora"></span>h00</h4>
<form class="editar-horario-form" action="/clinica/salas-horario">
<input type="hidden" name="sala" value="<?php echo $sala['id']; ?>" />
<input type="hidden" name="atendente" value="0" />
<input type="hidden" name="horario" value="0" />
<input type="hidden" name="dia" value="" />
<input type="hidden" name="hora" value="" />
<input type="hidden" name="periodo" value="" />
<p>Atendente: <span class="editar-horario-atendente"></span></p>
<p><label>Lotação: <input size="9" type="text" name="lotacao" value=""></label></p>
<p><label>Inicio: <input size="9" type="text" name="periodo-inicio" value=""></label></p>
<p><label>Término: <input size="9" type="text" name="periodo-termino"value=""></label></p>
<p style="margin-top: .5em; padding: 1em 0;"><a href="salvar-horario">Salvar</a></p>
</form>
</div><!-- .editar-horario -->
</div> <!-- bot-painel -->
<?php endforeach; ?> <!-- salas -->

</div> <!-- salas-quadro -->

<div id="loading" class="cover"></div>

<script>
// Exibir informações de atendente antigo 
// ou formulário de novo atendente
$("select#atendente-id").on("change", function(e){
	var atendenteId = $(this).val();
	var username = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-username");
	var email = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-email");
	var elementarId = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-elementar-id");
	var atendenteNome = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-nome");
	var atendenteTelefone1 = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-telefone1");
	var atendenteRegistro = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-registro");
	var atendenteLotacao = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-lotacao");
	var atendenteNotificar = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-notificar");
	$("input#atendente-elementar-id").val(elementarId);
	$("input#atendente-username").val(username);
	$("input#atendente-email").val(email);
	$("input#atendente-id").val(atendenteId);
	$("input#atendente-nome").val(atendenteNome);
	$("input#atendente-telefone1").val(atendenteTelefone1);
	$("input#atendente-registro").val(atendenteRegistro);
	$("input#atendente-lotacao").val(atendenteLotacao);
	document.getElementById("atendente-notificar").checked = (atendenteNotificar == "1") ? true : false;
	// Redefinir todos os horários
	$("a.botao").each(function(index, element){
		this.setAttribute("data-destaque", 0);
		this.setAttribute("data-selecionado", 0);
	});
	if ( atendenteId != 0 ){
		$("a.botao[data-atendente=\""+atendenteId+"\"]").each(function(index, element){
			this.setAttribute("data-destaque", 1);
		});
	}
	$(".editar-horario").attr("data-aberto", 0);
});

// Criar/atualizar usuário no sistema
$("#atendente-salvar").on("click", function(event){
	event.preventDefault();
	$("form#atendente-form").submit();
});
$("form#atendente-form").on("submit", function(event){
	event.preventDefault();
	
	$("div#loading").fadeIn("fast");

	var account = {
		account_id: $("#atendente-elementar-id").val(),
		group_id: 4,
		username: $("#atendente-username").val(),
		email: $("#atendente-email").val(),
		password: $("#atendente-password").val()
	}

	$.post("/backend/account/xhr_write_account", account, function(data){
		if ( data.done == true ) {
			var accountId = data.account_id;
			$("#atendente-elementar-id").val(accountId);
			var atendente = {
				accountId: accountId, 
				atendenteId: $("input#atendente-id").val(), 
				atendenteNome: $("input#atendente-nome").val(), 
				atendenteTelefone1: $("input#atendente-telefone1").val(), 
				atendenteRegistro: $("input#atendente-registro").val(), 
				lotacao: $("input#atendente-lotacao").val(), 
				notificar: ( document.getElementById("atendente-notificar").checked == true ) ? 1 : 0
			};
			$.post("/clinica/xhr_salas_atendente", atendente, function(data){
				if ( data.done == true ) {
					// Recarregar atendentes
					$("#atendente-id").empty();
					var option = document.createElement("option");
					option.setAttribute("value", "0");
					option.innerHTML = "Novo...";
					$("#atendente-id").append(option);
					for(var a = 0; a < data.atendentes.length; a++){
						var option = document.createElement("option");
						option.innerHTML = data.atendentes[a].nome;
						option.setAttribute("value", data.atendentes[a].id);
						if ( data.atendente == data.atendentes[a].id ) option.setAttribute("selected", 1);
						else option.setAttribute("selected", 0);
						option.setAttribute("data-elementar-id", data.atendentes[a].elementar_id);
						option.setAttribute("data-username", data.atendentes[a].username);
						option.setAttribute("data-email", data.atendentes[a].email);
						option.setAttribute("data-atendente", data.atendentes[a].id);
						option.setAttribute("data-nome", data.atendentes[a].nome);
						option.setAttribute("data-telefone1", data.atendentes[a].telefone1);
						option.setAttribute("data-registro", data.atendentes[a].registro);
						option.setAttribute("data-lotacao", data.atendentes[a].lotacao);
						option.setAttribute("data-notificar", data.atendentes[a].notificar);
						$("#atendente-id").append(option);
					}
					// Carregar atendente
					$("select#atendente-id").val($("input#atendente-id").val());
					$("select#atendente-id").change();
				}
				else {
					alert(data.message);
				}
				$("div#loading").fadeOut("fast");
			}, "json");

		}
		else {
			alert(data.message);
		}
		$("div#loading").fadeOut("fast");
	}, "json");
});

$("a#atendente-remover").on("click", function(e){
	e.preventDefault();
	var atendenteId = $("select#atendente-id").val();
	if ( atendenteId == 0 ) return;
	if ( ! confirm("O atendente e todos os horarios e agendamentos associados a ele serão definitivamente excluídos.")){
		return;
	}
	var atendenteId = $("select#atendente-id").val();
	$.post("/clinica/xhr_salas_remover_atendente", {atendenteId: atendenteId}, function(data){
		if ( data.done == true ) {
			location.reload(); 
		}
		else {
			//alert(data.message);
		}
	}, "json");
});

if ( $.urlParam("atendente") != '' ){
	// Carregar dados atendente
	$("select#atendente-id").val($.urlParam("atendente"));
	$("select#atendente-id").change();
}
else {
	// Atendente em branco 
	$("select#atendente-id").val(0);
	$("form#atendente-form")[0].reset();
}
// Máscara telefone
$("form#atendente-form").find(".campo-telefone").mask("(00) 00009-0000");

// Campo de data
$( "input[name='periodo-inicio']" ).datepicker({
	inline: true
});
$( "input[name='periodo-termino']" ).datepicker({
	inline: true
});

// Abrir horário
if ( $.urlParam("horario") != '' ){
	var id = $.urlParam("horario");
	var botao = document.querySelector("a.botao[data-id='"+id+"']");
	$(botao).click();
}

// Rolar janela
if ( $.urlParam("y") != '' ){
	window.scroll($.urlParam("x"), $.urlParam("y"));
}

$("form.editar-horario-form").on("submit", function(event){
	event.preventDefault();
});

$("a[href='salvar-horario']").on("click", function(event){
	event.preventDefault();
	$("div#loading").fadeIn("fast");
	var form = $(this).parents("form.editar-horario-form");
	var inicio = $(form).find("input[name='periodo-inicio']").datepicker("getDate");
	var termino = $(form).find("input[name='periodo-termino']").datepicker("getDate");
	var horario = {
		sala: $(form).find("input[name='sala']").val(),
		dia: $(form).find("input[name='dia']").val(),
		periodo: $(form).find("input[name='periodo']").val(),
		lotacao: $(form).find("input[name='lotacao']").val(),
		hora: $(form).find("input[name='hora']").val(),
		atendente: $(form).find("input[name='atendente']").val(),
		horario: $(form).find("input[name='horario']").val(),
		inicio: inicio.getFullYear()+"-"+(1+inicio.getMonth())+"-"+inicio.getDate(), 
		termino: termino.getFullYear()+"-"+(1+termino.getMonth())+"-"+termino.getDate()
	};

	$.post("/clinica/xhr_salas_horario", horario, function(data){
		if ( data.done == true ) {
			// Recarregar página na sua data atual
			var divMonth = document.getElementById("cal-month");
			var ano = divMonth.getAttribute("data-year");
			var mes = parseInt(divMonth.getAttribute("data-month")) + 1;
			var dia = divMonth.getAttribute("data-day");
			var href = "/clinica/salas?ano="+ano+"&mes="+mes+"&dia="+dia;
			var atendenteId = document.querySelector("input#atendente-id").getAttribute("value"); 
			if ( atendenteId != "" ){
				href += "&atendente="+atendenteId;
			}
			href += "&x="+window.scrollX+"&y="+window.scrollY+"&horario="+data.horario_id;
			window.location.href = href;
		}
		else {
			alert(data.message);
		}
	}, "json");
});

</script>
</body>
</html>
