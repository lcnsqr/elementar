<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<title><?php echo $title; ?> &ndash; <?php echo $nome; ?></title>
<link rel="stylesheet" href="/css/clinica/style.css">
<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>
<script>
window.onload=function(){
	// Registro da agenda exibida
	var agora = new Date();
	var sessao = {
		atendenteId: -1,
		ano: agora.getFullYear(), 
		mes: agora.getMonth(), 
		dia: agora.getDate(), 
		wdia: agora.getDay(), 
		horarioId: -1, 
		hora: -1, 
		lotacao: -1,
		min: 0
	};
	document.getElementById("data-ativa").innerHTML = sessao.dia + "/" + (sessao.mes+1) + "/" + sessao.ano;

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
				callback(data.agenda);
			}
			else {
				alert(data.message);
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
					document.getElementById("data-ativa").innerHTML = sessao.dia + "/" + (sessao.mes+1) + "/" + sessao.ano;
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

	var exibirHorarios = function(horas){
		// Limpar horários nos períodos
		var manha = document.getElementById("atendente-periodos-manha");
		manha.innerHTML = "";
		var tarde = document.getElementById("atendente-periodos-tarde");
		tarde.innerHTML = "";
		var noite = document.getElementById("atendente-periodos-noite");
		noite.innerHTML = "";
		// Cada hora do dia
		for(var h=0;h<horas.length;h++){
			var periodo = document.getElementById("atendente-periodos-" + horas[h]["periodo"]);
			// Inserir atendimento
			var hora = document.createElement("span");
			hora.setAttribute("data-horario-id", horas[h]["id"]);
			hora.setAttribute("data-hora", horas[h]["hora"]);
			hora.setAttribute("data-lotacao", horas[h]["lotacao"]);
			hora.innerHTML = twoDigit(horas[h]["hora"]) + ":00";
			var atendimentos = document.createElement("ul");
			var atendidos = horas[h]["atendidos"];
			// Preencher com agendado (se houver)
			for ( var i = 0; i < atendidos.length; i++ ){
				var atendimento = document.createElement("li");
				//atendimento.innerHTML = "<span>" + twoDigit(horas[h]["hora"]) + ":" + atendidos[i].minuto + "</span> " + atendidos[i].nome;
				atendimento.innerHTML = "<span>" + twoDigit(horas[h]["hora"]) + ":" + atendidos[i].minuto + "</span> ";
				atendimentos.appendChild(atendimento);
				var atendido = document.createElement("li");
				atendido.innerHTML =  atendidos[i].nome;
				atendimentos.appendChild(atendido);
				if ( atendidos[i].procedimento != "" ){
					// Exibir procedimento
					var atendimento = document.createElement("li");
					atendimento.setAttribute("class", "procedimento");
					atendimento.innerHTML =  atendidos[i].procedimento;
					atendimentos.appendChild(atendimento);
				}
			}
			// Marcar se ocupado
			/*
			if ( horas[h]["atendidos"].length >= parseInt(horas[h]["lotacao"]) ){
				$(a).addClass("ocupado");
			}
			*/
			var li = document.createElement("li");
			li.appendChild(hora);
			li.appendChild(atendimentos);
			periodo.appendChild(li);
		}
		// Recuperar (se houver) hora
		$("#atendente-periodos").find("a[data-hora=\""+sessao.hora+"\"]").click();
	}
	
	// Definir atendente
	sessao.atendenteId = $("#cal-agenda").data("atendente");
	carregarAgenda(exibirAgenda);

	var twoDigit = function(n){
		if ( n.toString().length < 2 ) return "0" + n.toString();
		return n;
	}

};
</script>
</head>
<body>

<div id="pagina-atendente">
<h2><?php echo $nome; ?></h2>

<h3 id="data-ativa"></h3>

<div data-atendente="<?php echo $atendente_id; ?>" id="cal-agenda">

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

</div> <!-- cal-agenda -->

<div id="atendente-periodos">
<div class="atendente-periodo">
<!-- <p>Manhã</p> -->
<ul id="atendente-periodos-manha">
</ul> <!-- manha -->
</div> <!-- periodo -->
<div class="atendente-periodo">
<!-- <p>Tarde</p> -->
<ul id="atendente-periodos-tarde">
</ul> <!-- tarde -->
</div> <!-- periodo -->
<div class="atendente-periodo">
<!-- <p>Noite</p> -->
<ul id="atendente-periodos-noite">
</ul> <!-- noite -->
</div> <!-- periodo -->
</div> <!-- atendente-periodos -->

</div> <!-- pagina-atendente -->

<div id="loading" class="cover"></div>

</body>
</html>
