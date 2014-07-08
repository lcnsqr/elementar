<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<title><?php echo $title; ?> &ndash; Agenda</title>
<link rel="stylesheet" href="/css/clinica/style.css">
<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>
<script>
window.onload=function(){
	// Registro da agenda exibida
	var agora = new Date();
	var sessao = (localStorage.getItem("agenda")) ? JSON.parse(localStorage.getItem("agenda")) : {
		atendenteId: -1,
		ano: agora.getFullYear(), 
		mes: agora.getMonth(), 
		dia: -1, 
		wdia: -1, 
		horarioId: -1, 
		hora: -1, 
		lotacao: -1,
		min: 0
	};

	// Redesenhar calendário
	var calMonth = function(){
		var dia = new Date(sessao.ano, sessao.mes);
		var divMonthName = document.getElementById("cal-month-name");
		switch ( sessao.mes ){
			case 0: divMonthName.innerHTML = "Janeiro &ndash; " + sessao.ano; break;
			case 1: divMonthName.innerHTML = "Fevereiro &ndash; " + sessao.ano; break;
			case 2: divMonthName.innerHTML = "Março &ndash; " + sessao.ano; break;
			case 3: divMonthName.innerHTML = "Abril &ndash; " + sessao.ano; break;
			case 4: divMonthName.innerHTML = "Maio &ndash; " + sessao.ano; break;
			case 5: divMonthName.innerHTML = "Junho &ndash; " + sessao.ano; break;
			case 6: divMonthName.innerHTML = "Julho &ndash; " + sessao.ano; break;
			case 7: divMonthName.innerHTML = "Agosto &ndash; " + sessao.ano; break;
			case 8: divMonthName.innerHTML = "Setembro &ndash; " + sessao.ano; break;
			case 9: divMonthName.innerHTML = "Outubro &ndash; " + sessao.ano; break;
			case 10: divMonthName.innerHTML = "Novembro &ndash; " + sessao.ano; break;
			case 11: divMonthName.innerHTML = "Dezembro &ndash; " + sessao.ano; break;
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
		while ( dia.getMonth() == sessao.mes ){
			var div = document.createElement("div");
			if ( dia.getDay() == 0 ) var col = "inative";
			else var col = ( dia.getDay() % 2 == 0 ) ? "even" : "odd";
			div.setAttribute("class", "cal-day " + col);
			div.setAttribute("data-day", dia.getDate());
			div.setAttribute("data-wday", dia.getDay());
			var p = document.createElement("p");
			p.innerHTML = dia.getDate();
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
	}

	// (re)desenhar calendário
	calMonth();

	$("#cal-month-back > p > a, #cal-month-forward > p > a").on("click", function(e){
		e.preventDefault();
		var year = sessao.ano;
		var month = sessao.mes;
		if ( $(this).parents("div").first().attr("id") == "cal-month-back" ) month--;
		else month++
		var mes = new Date(year, month);
		// Alterar mês/ano
		sessao.ano = mes.getFullYear();
		sessao.mes = mes.getMonth();
		calMonth();
		// Carregar agenda
		carregarAgenda(exibirAgenda);
		// Salvar estado da agenda
		localStorage.setItem("agenda", JSON.stringify(sessao));
	});

	// Carregar agenda do atendente no ano e mês
	var carregarAgenda = function(callback){
		$("div#loading").fadeIn("fast");
		// Mês no javascript começa em zero, incrementar
		var mes = sessao.mes + 1;
		$.post("/clinica/xhr_agenda_atendente", {atendenteId: sessao.atendenteId, ano: sessao.ano, mes: mes}, function(data){
			if ( data.done == true ) {
				var atendente = document.getElementById("atendente-id");
				document.getElementById("nome-atendente").innerHTML = atendente.options[atendente.selectedIndex].text;
				callback(data.agenda);
			}
			$("div#loading").fadeOut("fast");
		}, "json");
	}

	var exibirAgenda = function(agenda){
		// Redesenhar calendário
		calMonth();
		// Carregar agenda no calendário
		for(var a=0;a<agenda.length;a++){
			// Percorrer dias do mês na agenda
			for(var d in agenda[a]){
				// Identificar lotação do dia
				var diaLotado = true;
				for ( var h = 0; h < agenda[a][d].length; h++ ){
					if ( agenda[a][d][h]["atendidos"].length < parseInt(agenda[a][d][h]["lotacao"]) ){
						diaLotado = false;
					}
				}
				dSemZero = parseInt(d).toString();
				var anchor = document.createElement("a");
				// Incluir horários como atributo no elemento
				anchor.setAttribute("data-horarios", JSON.stringify(agenda[a][d]));
				anchor.innerHTML = dSemZero;
				anchor.setAttribute("href", dSemZero);
				anchor.setAttribute("data-dia", dSemZero);
				anchor.setAttribute("data-wdia", $("#cal-month-table > .cal-day[data-day=\"" + dSemZero + "\"]").data("wday"));
				anchor.onclick = function(){
					// Definir dia
					sessao.dia = $(this).attr("data-dia");
					sessao.wdia = $(this).attr("data-wdia");
					// Grifo
					$(".cal-day.selected").removeClass("selected");
					$(this).parents(".cal-day").first().addClass("selected");
					$(this).blur();
					// Carregar agenda do dia
					var horas = $(this).data("horarios");
					// Exibir horários nos períodos
					exibirHorarios(horas);
					// Salvar estado da agenda
					localStorage.setItem("agenda", JSON.stringify(sessao));
					return false;
				};
				// Inserir
				$("#cal-month-table > .cal-day[data-day=\"" + dSemZero + "\"] > p").empty();
				$("#cal-month-table > .cal-day[data-day=\"" + dSemZero + "\"] > p").append(anchor);
				// Lotação
				if ( diaLotado ) $("#cal-month-table > .cal-day[data-day=\"" + dSemZero + "\"]").addClass("ocupado");
			}
		}
		// Recuperar (se houver) dia
		$("#cal-month-table").find("a[data-dia=\""+sessao.dia+"\"]").click();
	}

	// Escolher atendente
	$("select#atendente-id").on("change", function(e){
		// Atendente
		sessao.atendenteId = $(this).val();
		// Limpar horários nos períodos
		document.getElementById("agenda-periodos-manha").innerHTML = "";
		document.getElementById("agenda-periodos-tarde").innerHTML = "";
		document.getElementById("agenda-periodos-noite").innerHTML = "";
		// Remover fichas
		$("ul.fic-abas > li").remove();
		$("div.fichas > div").remove();
		// Buscar agenda do atendente no servidor
		carregarAgenda(exibirAgenda);
		// Salvar estado da agenda
		localStorage.setItem("agenda", JSON.stringify(sessao));
	});

	// Resumo da agenda
	$("#resumo_agenda").on("click", function(event){
		event.preventDefault();
		var uri = $(this).attr("href");
		window.open(uri + sessao.atendenteId, "resumo_agenda", "location=0,menubar=0,width=320,height=480");
	});

	// Recuperar estado salvo
	$("select#atendente-id").val(sessao.atendenteId);
	carregarAgenda(exibirAgenda);

	var exibirHorarios = function(horas){
		// Limpar horários nos períodos
		var manha = document.getElementById("agenda-periodos-manha");
		manha.innerHTML = "";
		var tarde = document.getElementById("agenda-periodos-tarde");
		tarde.innerHTML = "";
		var noite = document.getElementById("agenda-periodos-noite");
		noite.innerHTML = "";
		// Cada hora do dia
		for(var h=0;h<horas.length;h++){
			var periodo = document.getElementById("agenda-periodos-" + horas[h]["periodo"]);
			// Inserir nova hora
			var a = document.createElement("a");
			a.setAttribute("href", horas[h]["hora"]);
			a.setAttribute("data-horario-id", horas[h]["id"]);
			a.setAttribute("data-hora", horas[h]["hora"]);
			a.setAttribute("data-lotacao", horas[h]["lotacao"]);
			a.setAttribute("data-atendidos", JSON.stringify(horas[h]["atendidos"]));
			a.innerHTML = twoDigit(horas[h]["hora"]) + ":00";
			a.onclick = function(){
				sessao.hora = $(this).data("hora");
				sessao.horarioId = $(this).data("horario-id");
				sessao.lotacao = $(this).data("lotacao");
				var atendidos = JSON.parse($(this).attr("data-atendidos"));
				exibirFichas(atendidos);
				// Grifo
				$("#agenda-periodos").find("a").removeClass("selected");
				$(this).addClass("selected");
				// Salvar estado da agenda
				localStorage.setItem("agenda", JSON.stringify(sessao));
				return false;
			}
			// Marcar se ocupado
			if ( horas[h]["atendidos"].length >= parseInt(horas[h]["lotacao"]) ){
				$(a).addClass("ocupado");
			}
			var li = document.createElement("li");
			li.appendChild(a);
			periodo.appendChild(li);
		}
		// Recuperar (se houver) hora
		$("#agenda-periodos").find("a[data-hora=\""+sessao.hora+"\"]").click();
	}
	
	var exibirFichas = function(atendidos){
		// Remover fichas
		$("ul.fic-abas > li").remove();
		$("div.fichas > div").remove();
		// Duração sessão
		var janela = Math.round(60 / sessao.lotacao);
		for ( var j = 0; j < 60; j += janela ){
			// Aba
			var a = document.createElement("a");
			a.setAttribute("href", j);
			a.setAttribute("data-id", j);
			a.setAttribute("data-ativo", 0);
			a.setAttribute("data-min", j);
			a.innerHTML = twoDigit(sessao.hora) + ":" + twoDigit(j);
			a.onclick = escolherFicha;
			if ( j == 0 ) $(a).addClass("ativo").attr("data-ativo", 1);
			var li = document.createElement("li");
			li.appendChild(a);
			$("ul.fic-abas").append(li);
			// Formulário
			var div = document.createElement("div");
			div.setAttribute("data-ativo", 0);
			div.setAttribute("data-id", j);
			div.setAttribute("class", "fic-cont");
			var form = $("form#atendido-fonte").clone(true);
			$(form).find("p#ficha-detalhes > span").html(sessao.dia+"/"+(sessao.mes+1)+"/"+sessao.ano+" às "+twoDigit(sessao.hora) + ":" + twoDigit(j));
			// Máscara telefone
			$(form).find(".campo-telefone").mask("(00) 00009-0000");
			$(form).attr("id", "");
			$(form).attr("data-min", j);
			$(form).attr("data-atendido-id", 0);
			$(form).on("submit", agendar);
			// Preencher com agendado (se houver)
			for ( var i = 0; i < atendidos.length; i++ ){
				if ( parseInt(atendidos[i].minuto) == j ){
					$(form).attr("data-atendido-id", atendidos[i].atendidos_id);
					$(form).attr("data-agendamento-id", atendidos[i].id);
					$(form).find("input[name=\"atendido-nome\"]").val(atendidos[i].nome);
					$(form).find("input[name=\"atendido-telefone1\"]").val(atendidos[i].telefone1);
					$(form).find("input[name=\"atendido-procedimento\"]").val(atendidos[i].procedimento);
					// Ocupado
					$(a).addClass("ocupado");
					break;
				}
			}
			$(div).append(form);
			if ( j == 0 ) $(div).addClass("ativo").attr("data-ativo", 1);
			$("div.fichas").append(div);
		}
		// Recuperar (se houver) minuto (agendamento)
		$("#fichas-cont").find("ul.fic-abas").find("a[data-min=\""+sessao.min+"\"]").click();
	}

	// Fichas
	var escolherFicha = function(){
		sessao.min = $(this).attr("data-min");
		// Salvar estado da agenda
		localStorage.setItem("agenda", JSON.stringify(sessao));
		$("div.fichas > ul.fic-abas > li > a").each(function(index, element){
			$(element).removeClass("ativo");
			$(element).attr("data-ativo", 0);
		});
		$(this).addClass("ativo");
		$(this).attr("data-ativo", 1);
		$(this).blur();

		$("div.fichas > div.fic-cont").each(function(index, element){
			$(element).removeClass("ativo");
			$(element).attr("data-ativo", 0);
		});
		var id = $(this).attr("data-id");
		$("div.fichas > div.fic-cont[data-id=\"" + id + "\"]").addClass("ativo");
		$("div.fichas > div.fic-cont[data-id=\"" + id + "\"]").attr("data-ativo", 1);
		if ( ! $(this).hasClass("ocupado") ){
			$("div.fichas > div.fic-cont[data-id=\"" + id + "\"]").find("input[name=atendido-nome]").focus();
		}
		return false;
	};

	var twoDigit = function(n){
		if ( n.toString().length < 2 ) return "0" + n.toString();
		return n;
	}

	$("a.atendido-acao.agendar").on("click", function(e){
		e.preventDefault();
		$(this).parents("form").first().submit();
	});

	$("a.atendido-acao.desmarcar").on("click", function(e){
		e.preventDefault();
		$("div#loading").fadeIn("fast");
		var form = $(this).parents("form").first();
		var agendamento_id = $(form).attr("data-agendamento-id");
		$.post("/clinica/xhr_agendamento_cancelar", {atendenteId: sessao.atendenteId, ano: sessao.ano, mes: sessao.mes + 1, agendamento_id: agendamento_id}, function(data){
			if ( data.done == true ) {
				exibirAgenda(data.agenda);
			}
			else {
				alert(data.message);
			}
			$("div#loading").fadeOut("fast");
		}, "json");
	});

	var agendar = function(){
		$("div#loading").fadeIn("fast");
		var form = this;
		// Dados do atendido
		var atendidoId = $(form).attr("data-atendido-id");
		var nome = $(this).find("input[name=\"atendido-nome\"]").val();
		var telefone1 = $(this).find("input[name=\"atendido-telefone1\"]").val();
		var procedimento = $(this).find("input[name=\"atendido-procedimento\"]").val();

		$.post("/clinica/xhr_agendamento", {atendenteId: sessao.atendenteId, ano: sessao.ano, mes: sessao.mes + 1, dia: sessao.dia, wdia: sessao.wdia, horarioId: sessao.horarioId, hora: sessao.hora, min: sessao.min, atendidoId: atendidoId, atendidoNome: nome, atendidoTelefone1: telefone1, atendidoProcedimento: procedimento}, function(data){
			if ( data.done == true ) {
				exibirAgenda(data.agenda);
				// atendidos datalist
				$("#atendidos-list").empty();
				for(var a = 0; a < data.atendidos.length; a++){
					var option = document.createElement("option");
					option.setAttribute("value", data.atendidos[a].nome+", "+data.atendidos[a].telefone1);
					option.setAttribute("data-atendido", JSON.stringify(data.atendidos[a]));
					$("#atendidos-list").append(option);
				}
			}
			else {
				alert(data.message);
			}
			$("div#loading").fadeOut("fast");
		}, "json");
		return false;
	}

	// Auto completar dados do atendido
	$("input[list=atendidos-list]").on("input", function(e){
		var option = $("#atendidos-list > option[value=\""+$(this).val()+"\"]");
		if ( option.length > 0 ){
			var form = $(this).parents("form").first();
			var atendido = $(option).data("atendido");
			$(form).attr("data-atendido-id", atendido.id);
			for ( var prop in atendido ){
				$(form).find("input[name=atendido-"+prop+"]").val(atendido[prop]);
			}
		}
	});

};
</script>
</head>
<body>

<div id="agenda-atendente-header">
<form id="atendente-form" action="/clinica/agenda">
<p>Agenda de 
<select id="atendente-id" name="atendente-id">
<?php foreach($atendentes as $atendente): ?>
<option data-atendente="<?php echo $atendente['id']; ?>" data-nome="<?php echo $atendente['nome']; ?>" data-telefone1="<?php echo $atendente['telefone1']; ?>" value="<?php echo $atendente['id']; ?>"><?php echo $atendente['nome']; ?></option>
<?php endforeach; ?> <!-- atendentes -->
</select>
<a id="resumo_agenda" href="/clinica/agenda_resumo/">Resumo da agenda</a>
</p>
</form>
</div> <!-- atendente -->

<div id="cal-agenda">

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
</div>

<div id="agenda-periodos">
<div class="agenda-periodo">
<p>Manhã</p>
<ul id="agenda-periodos-manha">
</ul> <!-- manha -->
</div> <!-- periodo -->
<div class="agenda-periodo">
<p>Tarde</p>
<ul id="agenda-periodos-tarde">
</ul> <!-- tarde -->
</div> <!-- periodo -->
<div class="agenda-periodo">
<p>Noite</p>
<ul id="agenda-periodos-noite">
</ul> <!-- noite -->
</div> <!-- periodo -->
<hr>
</div> <!-- agenda-periodos -->

</div> <!-- cal-agenda -->

<div id="fichas-cont">
<p id="nome-atendente"></p>
<div class="fichas">
	<ul class="fic-abas">
	</ul>
	<hr>
</div> <!-- fichas -->
<datalist id="atendidos-list">
<?php foreach($atendidos as $atendido): ?>
<option 
	data-atendido='<?php echo json_encode($atendido); ?>' 
	value="<?php echo $atendido->nome; ?>, <?php echo $atendido->telefone1; ?>">
<?php endforeach; ?>
</datalist>
</div> <!-- fichas-cont -->

<form autocomplete="off" id="atendido-fonte" class="atendido-form">
<p id="ficha-detalhes">Consulta em <span></span></p>
<p><label>Nome:</label><br><input list="atendidos-list" type="text" name="atendido-nome"></p>
<p><label>Celular:</label><br><input class="campo-telefone" type="text" name="atendido-telefone1"></p>
<p><label>Procedimento:</label><br><input class="campo-procedimento" type="text" name="atendido-procedimento"></p>
<p><a href="agendar" class="atendido-acao agendar">Agendar</a></p>
<p><a href="desmarcar" class="atendido-acao desmarcar">Desmarcar</a></p>
</form>

<div id="loading"></div>
</body>
</html>
