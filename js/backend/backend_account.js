$(function(){$("#login_form").submit(function(a){a.preventDefault();$("#blocker").fadeIn("fast");var b=$(this).attr("action")+document.location.hash;$.post("/user/login",$(this).serialize(),function(d){try{if(d.done==true){if(b!=""){location.reload()}}else{showClientWarning(d.msg);$("#blocker").fadeOut("fast")}}catch(c){showClientWarning("Erro de comunicação com o servidor")}},"json")});$(".logout").click(function(b){b.preventDefault();var a=$(this).attr("href");$.post("/user/logout",function(c){if(c.done==true){window.location.replace(a)}else{alert(c.msg)}},"json")});$("#user_add").live("click",function(a){a.preventDefault();$("#user_add_form").show("slow","easeInSine")});$("#form_user_add").live("submit",function(a){a.preventDefault();$.post("/backend/account/xhr_write_user",$(this).serialize(),function(c){try{if(c.done==true){$(".user_info").first().before(c.html);$("#user_add").hide("slow","easeOutSine")}else{$.each(c,function(d,e){})}}catch(b){}},"json")});$(".user_del").live("click",function(b){b.preventDefault();var a=$(this).parents(".user_info").first();$.post("/backend/account/xhr_erase_user",{id:id},function(d){try{if(d.done==true){$(a).hide("slow","easeOutSine",function(){$(a).remove()})}else{$.each(d,function(e,f){})}}catch(c){}},"json")})});var idle=true;window.onbeforeunload=confirmExit;function confirmExit(){if(idle!=true){return"Há uma operação em andamento. Tem certeza que deseja sair da página?"}};