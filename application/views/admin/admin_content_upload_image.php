<div class="image_parent">
	<div class="image_item">

		<div class="image_item_inputs">
			<div class="image_item_description">
			<?php echo $image_description; ?>
			</div> <!-- image_item_description -->
<?php if ( (bool) $thumbnail ) : ?>
			<div class="image_item_thumbnail" style="background-image: url('<?php echo $thumbnail; ?>');">
<?php else: ?>
			<div class="image_item_thumbnail image_item_thumbnail_missing">
<?php endif; ?>
			<div class="loading"></div>
			</div> <!-- image_item_thumbnail -->
		</div> <!-- image_item_inputs -->

		<div class="menu_item_menu">
			<div class="upload_form"><?php echo $upload_form; ?></div>
			<ul>
				<li class="image_cancel_item" style="display: none;"><a class="image_cancel" href="<?php echo $upload_session_id; ?>">&notin; Cancelar Envio</a></li>
				<!--
				<li><a class="image_up" href="image_up">&uArr; Mover para cima</a></li>
				<li><a class="image_down" href="image_down">&dArr; Mover para baixo</a></li>
				-->
				<li><a class="image_erase" href="image_erase">&empty; Limpar</a></li>
				<!--
				<li><a class="image_add_up" href="image_add_up">&uarr; Novo image acima</a></li>
				<li><a class="image_add_down" href="image_add_down">&darr; Novo image abaixo</a></li>
				-->
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
		<iframe style="display: none;" id="iframeUpload_<?php echo $upload_session_id; ?>" name="iframeUpload_<?php echo $upload_session_id; ?>" scrolling="no" frameborder="0"></iframe>
	</div> <!-- image_item -->
</div> <!-- image_parent -->
