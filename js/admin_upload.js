//<![CDATA[

$(function() {

/*
 * Envio automático ao escolher imagem
 */
$("input[name='upload_image_field']").live("change", function() {
	$(this).parents("form.upload_image").first().submit();
});

/*
 * Image upload
 */
$(".upload_image").live("submit", function(event) {

	var form = $(this);
	
	if ( $(form).find("input[name='upload_image_field']").val() == "" ) {
		return false;
	}
	
	var form_upload_session = $(form).find("input[name='form_upload_session']").val();
	var upload_image_form = $("#upload_image_"+form_upload_session).find(".upload_image_form");
	var upload_image_loading = $("#upload_image_"+form_upload_session).find(".upload_image_loading");
	var upload_image_display = $("#upload_image_"+form_upload_session).find(".upload_image_display");
	
	$(upload_image_form).hide("slow", "easeOutSine", function() {
		$(upload_image_loading).show("fast", "easeOutSine");
	});

	// Verificar sessão de upload 
	$(form).everyTime("3s", 'SessionStatus', function() {
		$.post("/admin/upload/xhr_read_image_status", { form_upload_session : form_upload_session }, function(data){
			try {
				if ( data.done == true ) {
					// interromperloop
					$(form).stopTime('SessionStatus');
					// escrever id da imagem enviada
					var field_sname = $(form).find("input[name='field_sname']").first().val();
					$("#content_editor_window").find("input[name='"+field_sname+"']").val(data.image_id);
					$("#upload_image_"+form_upload_session).find(".upload_image_display_thumb").attr("alt", data.name);
					$("#upload_image_"+form_upload_session).find(".upload_image_display_thumb").attr("src", data.thumb_uri);
					$(upload_image_loading).hide("slow", "easeOutSine", function() {
						$(upload_image_display).show("fast", "easeOutSine");
					});
				}
			}
			catch (err) {
				$(form).stopTime('SessionStatus');
				//console.log("Erro de comunicação com o servidor");
			}
		}, "json");
	});
});

$(".upload_image_cancel").live("click", function(event) {
	event.preventDefault();
	var container = $(this).parents(".upload_image_container");
	// interromper loop
	$(container).find("form.upload_image").stopTime('SessionStatus');
	// interromper envio
	$(container).find("iframe").attr('src', '/admin/upload/empty_iframe');

	$(container).find("input[name='upload_image_field']").val("");

	$(container).find(".upload_image_loading").hide("slow", "easeOutSine");
	$(container).find(".upload_image_form").show("fast", "easeInSine");
});
	
$(".upload_image_change").live("click", function(event) {
	event.preventDefault();
	var container = $(this).parents(".upload_image_container");
	
	field_sname = $(container).find("input[name='field_sname']").val();
	$(".form_cont").find("input[name='"+field_sname+"']").val("");

	$(container).find("input[name='upload_image_field']").val("");

	$(container).find(".upload_image_display").hide("slow", "easeOutSine");
	$(container).find(".upload_image_form").show("fast", "easeInSine");

});


});

//]]>

