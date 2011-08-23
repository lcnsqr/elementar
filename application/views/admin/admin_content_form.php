<div id="content_editors_container">
	<div id="content_editors_header">
		<div id="content_editors_menu">
			<ul>
				<li><a class="content_editors_menu_item current" href="content_editor_form">Conteúdo</a></li>
				<li><a class="content_editors_menu_item" href="template_editor_form">Template</a></li>
			</ul>
		</div>
		<p><?php echo $breadcrumb; ?></p>
	</div>

	<div style="display: none;" id="template_editor_form" class="editor_form">
	<?php echo $template_form; ?>
	</div>
	
	<div id="content_editor_form" class="editor_form">
	<?php echo $content_form; ?>
	</div>
</div>
