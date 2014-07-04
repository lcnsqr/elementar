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

<script>
window.onload=function(){
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
				p.appendChild(anchor);
				if ( dia.getDate() == day ) div.setAttribute("class", div.getAttribute("class") + " selected");
			}
			div.appendChild(p);
			divMonthTable.appendChild(div);
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

	$("a.botao").on("click", function(e){
		e.preventDefault();
		var botao = this;
		var estado = ( $(this).attr("data-estado") == 1 ) ? 0 : 1;
		var painel = $(botao).parents("div.bot-painel")[0];
		if ( $(painel).attr("data-exclusivo") == 1 ){
			// Desapertar demais botões
			$(painel).find("a.botao").each(function(index, element){
				$(this).attr("data-estado", 0);
				$(this).removeClass("ativo");
			});
		}
		// Verificar se horário já está ocupado
		if ( $(this).hasClass("ocupado") ){
			return;
		}

		// Marcar todos os horários contíguos no período
		var sala = $(this).attr("data-sala");
		var dia = $(this).attr("data-dia");
		var periodo = $(this).attr("data-periodo");
		var horario = $(this).attr("data-horario");
		if ( estado == 1 ){
			// Verificar se há horário ocupado no período
			if ( $("a.botao.ocupado[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"]").length == 0 ){
				// Todos horários do período livres
				$("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"]").addClass("ativo").attr("data-estado", 1);
			}
			else {
				// Verificar se há no mínimo 3 horários contíguos
				var horarios = [];
				horarios.push(horario);
				// Horários anteriores
				var h = horario - 1;
				while ( $("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"][data-horario=\"" + h + "\"]").length == 1 && ! $("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"][data-horario=\"" + h + "\"]").hasClass("ocupado")){
					horarios.push(h);
					h--;
				}
				// Horários posteriores
				var h = horario + 1;
				while ( $("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"][data-horario=\"" + h + "\"]").length == 1 && ! $("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"][data-horario=\"" + h + "\"]").hasClass("ocupado")){
					horarios.push(h);
					h++;
				}
				if ( horarios.length < 3 ){
					alert("Não é possível selecionar o período");
				}
				else  {
					for ( h = 0; h < horarios.length; h++ ){
						$("a.botao[data-sala=\"" + sala + "\"][data-dia=\"" + dia + "\"][data-periodo=\"" + periodo + "\"][data-horario=\"" + horarios[h] + "\"]").addClass("ativo").attr("data-estado", 1);
					}
				}
			}
		}
		else{
			$(this).parents("ul").find("a.botao.ativo").removeClass("ativo").attr("data-atendente", 0).attr("data-estado", 0).attr("data-ocupado", 0);
		}
		$(this).blur();
	});

	// Criar/atualizar usuário no sistema
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
				// Associar atendente a horário (alugar horário em sala)
				var horarios = [];
				$("a.botao.ativo").each(function(index, element){
					horarios.push({
						sala : $(this).attr("data-sala"),
						dia : $(this).attr("data-dia"),
						periodo : $(this).attr("data-periodo"),
						horario : $(this).attr("data-horario")
					});
				});
				var inicio = $( "input#atendente-inicio" ).datepicker( "getDate" );
				var termino = $( "input#atendente-termino" ).datepicker( "getDate" );
				var atendente = {
					accountId: accountId, 
					atendenteId: $("input#atendente-id").val(), 
					atendenteNome: $("input#atendente-nome").val(), 
					atendenteTelefone1: $("input#atendente-telefone1").val(), 
					atendenteInicio: inicio.getFullYear()+"-"+(1+inicio.getMonth())+"-"+inicio.getDate(), 
					atendenteTermino: termino.getFullYear()+"-"+(1+termino.getMonth())+"-"+termino.getDate(), 
					lotacao: $("input#atendente-lotacao").val(), 
					horarios: JSON.stringify(horarios)
				};
				$.post("/clinica/xhr_salas_associar_mensal", atendente, function(data){
					if ( data.done == true ) {
						// Salvo com sucesso
						$("a.botao.ativo").each(function(index, element){
							$(this).addClass("ocupado");
							//$(this).removeClass("ativo");
							$(this).attr("data-ocupado", 1);
							$(this).attr("data-atendente", data.atendente);
						});
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
							$("#atendente-id").append(option);
						}
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

	// Exibir informações de atendente antigo 
	// ou formulário de novo atendente
	$("select#atendente-id").on("change", function(e){
		var atendenteId = $(this).val();
		var username = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-username");
		var email = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-email");
		var elementarId = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-elementar-id");
		var atendenteNome = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-nome");
		var atendenteTelefone1 = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-telefone1");
		var atendenteLotacao = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-lotacao");
		var atendenteInicio = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-inicio");
		var atendenteTermino = $(this).children("option[data-atendente=\""+atendenteId+"\"]").attr("data-termino");
		var inicio = new Date(atendenteInicio);
		inicio.setDate(inicio.getDate() + 1);
		inicio.setMonth(inicio.getMonth() + 1);
		var termino = new Date(atendenteTermino);
		termino.setDate(termino.getDate() + 1);
		termino.setMonth(termino.getMonth() + 1);
		$( "input#atendente-inicio" ).datepicker( "setDate", inicio.getDate()+"/"+inicio.getMonth()+"/"+inicio.getFullYear() );
		$( "input#atendente-termino" ).datepicker( "setDate", termino.getDate()+"/"+termino.getMonth()+"/"+termino.getFullYear() );
		$("input#atendente-elementar-id").val(elementarId);
		$("input#atendente-username").val(username);
		$("input#atendente-email").val(email);
		$("input#atendente-id").val(atendenteId);
		$("input#atendente-nome").val(atendenteNome);
		$("input#atendente-telefone1").val(atendenteTelefone1);
		$("input#atendente-lotacao").val(atendenteLotacao);
		// Redefinir todos os horários
		$("a.botao").each(function(index, element){
			$(this).removeClass("ativo");
			$(this).attr("data-estado", 0);
			if ( $(this).attr("data-ocupado") == 1 ) $(element).addClass("ocupado");
		});
		if ( atendenteId != 0 ){
			$("a.botao.ocupado[data-atendente=\""+atendenteId+"\"]").each(function(index, element){
				$(this).attr("data-estado", 1);
				$(this).addClass("ativo");
				$(this).removeClass("ocupado");
			});
		}
	});
	// Atendente inicial sempre em branco 
	$("select#atendente-id").val(0);
	$("form#atendente-form")[0].reset();
	// Máscara telefone
	$("form#atendente-form").find(".campo-telefone").mask("(00) 00009-0000");

	// Campo de data
	$( "#atendente-inicio" ).datepicker({
		inline: true
	});
	$( "#atendente-termino" ).datepicker({
		inline: true
	});
};
</script>
</head>
<body>

<div id="atendente">
<h2>Salas</h2>

<div id="salas-dias">

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

</div> <!-- salas-dias -->

<div id="salas-atendente">

<form id="atendente-antigo" action="/clinica/salas-atendente">
<select id="atendente-id" name="atendente-id">
<?php foreach($atendentes as $atendente): ?>
<option data-inicio="<?php echo $atendente['inicio']; ?>" data-termino="<?php echo $atendente['termino']; ?>" data-lotacao="<?php echo $atendente['lotacao']; ?>" data-email="<?php echo $atendente['email']; ?>" data-username="<?php echo $atendente['username']; ?>" data-elementar-id="<?php echo $atendente['elementar_id']; ?>" data-atendente="<?php echo $atendente['id']; ?>" data-nome="<?php echo $atendente['nome']; ?>" data-telefone1="<?php echo $atendente['telefone1']; ?>" value="<?php echo $atendente['id']; ?>"><?php echo $atendente['nome']; ?></option>
<?php endforeach; ?> <!-- atendentes -->
<option value="0" selected>Novo...</option>
</select>
</form>

<script>

</script>
<form autocomplete="off" id="atendente-form" action="/clinica/salas-alugar">
	<input type="hidden" name="atendente-elementar-id" id="atendente-elementar-id" value="0">
	<input type="hidden" name="atendente-id" id="atendente-id" value="0">
	<p><label for="atendente-username">Nome de usuário:</label><br><input type="text" name="atendente-username" id="atendente-username" value=""></p>
	<p><label for="atendente-email">Email:</label><br><input type="text" name="atendente-email" id="atendente-email" value=""></p>
	<p><label for="atendente-password">Senha:</label><br><input type="password" name="atendente-password" id="atendente-password" value=""></p>
	<p><label for="atendente-nome">Nome:</label><br><input type="text" name="atendente-nome" id="atendente-nome" value=""></p>
	<p><label for="atendente-telefone1">Telefone 1:</label><br><input type="text" name="atendente-telefone1" id="atendente-telefone1" value="" class="campo-telefone"></p>
	<p><label for="atendente-lotacao">Lotação/hora:</label><br><input type="text" name="atendente-lotacao" id="atendente-lotacao" value=""></p>
	<p><label for="atendente-inicio">Inicio:</label><br><input type="text" name="atendente-inicio" id="atendente-inicio" value=""></p>
	<p><label for="atendente-termino">Término:</label><br><input type="text" name="atendente-termino" id="atendente-termino" value=""></p>
	<p><input type="submit" value="Salvar"></p>
</form>
</div> <!-- atendente -->

</div> <!-- salas-atendente -->

<?php foreach($salas as $sala): ?>
<h2><?php echo $sala['tipo']; ?> &ndash; <?php echo $sala['nome']; ?></h2>
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
<li><a class="botao <?php echo ($hora['ocupado']) ? "ocupado" : ""; ?>" href="<?php echo $hora['horario']; ?>" data-estado="0" data-ocupado="<?php echo ($hora['ocupado']) ? 1 : 0; ?>" data-sala="<?php echo $sala['id']; ?>" data-dia="<?php echo $dia; ?>" data-periodo="<?php echo $periodo; ?>" data-horario="<?php echo $hora['horario']; ?>" data-atendente="<?php echo $hora['atendente']; ?>"><?php echo $hora['horario']; ?></a></li>
<?php endforeach; ?> <!-- periodos -->
<hr class="salas-clear">
</ul>
<?php endforeach; ?> <!-- periodos -->
</div> <!-- bot-dia -->
<?php endforeach; ?> <!-- dias -->
</div> <!-- bot-painel -->
<hr class="salas-clear">
<?php endforeach; ?> <!-- salas -->

<div id="loading"></div>

</body>
</html>
