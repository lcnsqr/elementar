$(function(){$("body").click(function(){$("body > .tree_listing_menu").fadeOut("fast",function(){$(this).remove()});$(".label > a.current").removeClass("current")});$(".label > a").live("click",function(b){b.preventDefault();$("body > .tree_listing_menu").fadeOut("fast",function(){$(this).remove()});$(".label > a.current").removeClass("current");$(this).addClass("current");var a=$(this).parents(".tree_listing_row").first().find(".tree_listing_menu");var c=$(a).clone();$(c).css({left:b.pageX+"px",top:b.pageY+"px"});$("body").append(c);$(c).fadeIn("fast")});$(".label > a").live("mousedown",function(a){a.preventDefault()});$("a.fold.folder_switch").live("click",function(b){b.preventDefault();$("#account_tree_loading").fadeIn("fast");var d=$(this).attr("href");var c=$(this).parents(".tree_parent").first().find(".tree_listing").first();var a=$(this);$.post("/backend/account/xhr_render_tree_listing",{id:d},function(e){if(e.done==true){$(c).html(e.html);$(c).slideDown("fast","easeInSine");$(a).addClass("unfold");$(a).removeClass("fold")}$("#account_tree_loading").fadeOut("fast")},"json")});$("a.unfold.folder_switch").live("click",function(a){a.preventDefault();var b=$(this).parents(".tree_parent").first().find(".tree_listing").first();$(b).slideUp("fast","easeOutSine");$(this).addClass("fold");$(this).removeClass("unfold")});$(window).mousedown(function(a){mouseButton=1});$(window).mouseup(function(c){mouseButton=0;if($("#tree_drag_container").children().length>0){var g=$("#tree_drag_container").find("p.label").first();var f=$(g).children("a").attr("href");if($(g).hasClass("element")){var b="element"}else{if($(g).hasClass("content")){var b="content"}}$("#tree_drag_container").fadeOut("fast",function(){$("#tree_drag_container").html("");$("#tree_drag_container").hide()});var a=$(".tree_listing_row.hover").find("p.label").first();var e=$(a).children("a").attr("href");$(".tree_listing_row").not(".undroppable").not(".droppable").addClass("droppable");$(".tree_listing_row.hover").removeClass("hover");if(b=="content"){var d="/backend/content/xhr_write_content_parent"}else{if(b=="element"){var d="/backend/content/xhr_write_element_parent"}}if(!e||!f){return null}$("#account_tree_loading").fadeIn("fast");$.post(d,{parent_id:e,child_id:f},function(h){if(h.done==true){$.post("/backend/content/xhr_render_tree_unfold",{request:b,id:f},function(i){$("#tree_listing_1").html(i.html);$("#account_tree_loading").fadeOut("fast")},"json")}else{$("#account_tree_loading").fadeOut("fast");showClientWarning(h.message)}},"json")}});$(window).mousemove(function(b){if(mouseButton==1&&$("#tree_drag_container").children().length>0){$("#tree_drag_container:hidden").fadeIn("fast");$("#tree_drag_container").css("top",(b.pageY-offsetY)+"px");$("#tree_drag_container").css("left",(b.pageX-offsetX)+"px");var a=b.pageY;var c=b.pageX;$(".tree_listing_row.droppable").not(".dragging").each(function(){var e=$(this).offset().top;var f=$(this).offset().left+$(this).outerWidth();var d=$(this).offset().top+$(this).outerHeight();var g=$(this).offset().left;if(a>e&&c<f&&a<d&&a>g){$(this).addClass("hover")}else{$(this).removeClass("hover")}})}});$(".tree_listing_icon.draggable").live("mousedown",function(b){b.preventDefault();var d=$(this).parent(".tree_listing_row");if($(d).parent("#tree_parent_1").length>0){return null}var c=$(d).offset();offsetY=b.pageY-c.top;offsetX=b.pageX-c.left;$(d).removeClass("droppable");var a=$(d).clone();$(a).addClass("dragging");$(a).children().addClass("dragging");$("#tree_drag_container").html(a)})});var offsetY=0;var offsetX=0;var mouseButton=0;