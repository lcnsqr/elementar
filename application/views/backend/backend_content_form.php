<div id="editors_container">
	<div id="editors_header">
<?php if ( $show_tabs ) : ?>
		<div id="editors_menu">
			<ul>
				<li><a class="editors_menu_item<?php echo ( $editor == 'content' ) ? ' current' : ''; ?>" href="content_editor_form"><?php echo $elementar_editor_content; ?></a></li>
				<li><a class="editors_menu_item<?php echo ( $editor == 'template' ) ? ' current' : ''; ?>" href="template_editor_form"><?php echo $elementar_editor_template; ?></a></li>
				<li><a class="editors_menu_item<?php echo ( $editor == 'meta' ) ? ' current' : ''; ?>" href="meta_editor_form"><?php echo $elementar_editor_meta; ?></a></li>
			</ul>
		</div>
<?php endif; ?>
		<p><?php echo $breadcrumb; ?></p>
	</div>

<?php if ( $show_tabs ) : ?>
	<div style="display: <?php echo ( $editor == 'meta' ) ? 'block' : 'none'; ?>;" id="meta_editor_form" class="editor_form">
	<?php echo $meta_form; ?>
	</div>

	<div style="display: <?php echo ( $editor == 'template' ) ? 'block' : 'none'; ?>;" id="template_editor_form" class="editor_form">
	<?php echo $template_form; ?>
	</div>
<?php endif; ?>
	
	<div style="display: <?php echo ( $editor == 'content' ) ? 'block' : 'none'; ?>;" id="content_editor_form" class="editor_form">
	<?php echo $content_form; ?>
	</div>

</div>
