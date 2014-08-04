<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $title; ?> &ndash; Cobranças</title>
<link rel="stylesheet" href="/css/clinica/style.css">
<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>
<script>
window.onload=function(){
	// Carregar contas de atendentes
	var cobrancas = function(year, month){
		var divMonth = document.getElementById("cal-month");
		month++;
		$.post("/clinica/xhr_cobrancas", {ano: year, mes: month}, function(data){
			if ( data.done == true ) {
				for (var c = 0; c < data.conta.length; c++){
					var dia = $(divMonth).find("div.cal-day[data-day=" + data.conta[c].dia_vencimento + "]");
					// Dias com cobranças
					var cobrancas = $(dia).data("cobrancas");
					if ( cobrancas.length == 0 ){
						var p = document.createElement("p");
						var anchor = document.createElement("a");
						anchor.innerHTML = data.conta[c].dia_vencimento;
						anchor.setAttribute("href", data.conta[c].dia_vencimento);
						anchor.onclick = function(){
							$("#cobrancas-cont").html("");
							// Grifo
							$(".cal-day.selected").removeClass("selected");
							$(this).parents(".cal-day").first().addClass("selected");
							$(this).blur();
							var cobrancas = $(this).parents(".cal-day").data("cobrancas");
							for ( var c = 0; c < cobrancas.length; c++ ){
								var p = document.createElement("p");
								p.innerHTML = cobrancas[c].atendente_nome + ": R$ " + cobrancas[c].valor;
								$("#cobrancas-cont").append(p);
							}
							return false;
						}
						p.appendChild(anchor);
						$(dia).children().remove();
						$(dia).append(p);
					}
					// Cobranças
					cobrancas.push(data.conta[c]);
					$(dia).attr("data-cobrancas", JSON.stringify(cobrancas));
				}
			}
			else {
				alert(data.message);
			}
		}, "json");
	}

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
			var col = ( dia.getDay() % 2 == 0 ) ? "even" : "odd";
			div.setAttribute("class", "cal-day " + col);
			div.setAttribute("data-day", dia.getDate());
			div.setAttribute("data-wday", dia.getDay());
			div.setAttribute("data-cobrancas", "[]");
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

		var divMonth = document.getElementById("cal-month");
		divMonth.setAttribute("data-year", year);
		divMonth.setAttribute("data-month", month);

		// Carregar dias com cobranças
		cobrancas(year, month);
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

};
</script>
</head>
<body>

<div id="atendente">
<h2>Cobranças</h2>

<div id="cobrancas-dias">

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

</div> <!-- cobrancas-dias -->

<div id="cobrancas-cont">

</div> <!-- cobrancas-cont -->

</body>
</html>
