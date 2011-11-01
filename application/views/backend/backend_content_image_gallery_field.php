<div class="image_parent image_parent_template">
	<div class="image_item image_item_template">
		<input name="image_item_field" id="image_item_field" type="hidden" value='' class="image_item_field" />
		<div class="image_item_inputs">
			<div class="image_item_description">
			<label>Descrição da imagem</label><br />
			<input type="text" name="image_item_description_field" value="" class="image_item_description_field" />
			</div> <!-- image_item_description -->
			<div class="image_item_thumbnail image_item_thumbnail_missing">
			</div> <!-- image_item_thumbnail -->
		</div> <!-- image_item_inputs -->

		<div class="image_item_menu">
			<ul>
				<li><a class="browse_file" href="browse_file">&alpha; Arquivo</a></li>
				<li><a class="image_erase" href="image_erase">&empty; Limpar</a></li>
				<li><a class="image_up" href="image_up">&uArr; Mover para cima</a></li>
				<li><a class="image_down" href="image_down">&dArr; Mover para baixo</a></li>
				<li><a class="image_delete" href="image_delete">&empty; Remover</a></li>
				<li><a class="image_add_up" href="image_add_up">&uarr; Nova imagem acima</a></li>
				<li><a class="image_add_down" href="image_add_down">&darr; Nova imagem abaixo</a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- image_item -->
</div> <!-- image_parent -->

<p class="image_parent_add"><a class="image_add" href="image_add">&rarr; Incluir imagem</a></p>
<div class="image_parent">
<?php foreach ( $gallery as $gallery_item ) : ?>
	<?php $image = json_decode($gallery_item, TRUE); $item_id = uniqid(); ?>
	<div class="image_item">
		<input class="image_item_field" name="image_item_field_<?php echo $item_id; ?>" id="image_item_field_<?php echo $item_id; ?>" type="hidden" value='<?php echo $gallery_item; ?>' />
		<div class="image_item_inputs">
			<div class="image_item_description">
			<label>Descrição da imagem</label><br />
			<input class="image_item_description_field" type="text" name="image_item_field_<?php echo $item_id; ?>_description" id="image_item_field_<?php echo $item_id; ?>_description" value="<?php echo $image['title']; ?>" />
			</div> <!-- image_item_description -->
<?php if ( (bool) $image['thumbnail'] ) : ?>
			<div id="image_item_thumbnail_<?php echo $item_id; ?>" class="image_item_thumbnail" style="background-image: url('<?php echo $image['thumbnail']; ?>');">
<?php else: ?>
			<div id="image_item_thumbnail_<?php echo $item_id; ?>" class="image_item_thumbnail image_item_thumbnail_missing">
<?php endif; ?>
			</div> <!-- image_item_thumbnail -->
		</div> <!-- image_item_inputs -->

		<div class="image_item_menu">
			<ul>
				<li><a class="browse_file" href="browse_file">&alpha; Arquivo</a></li>
				<li><a class="image_erase" href="image_erase">&empty; Limpar</a></li>
				<li><a class="image_up" href="image_up">&uArr; Mover para cima</a></li>
				<li><a class="image_down" href="image_down">&dArr; Mover para baixo</a></li>
				<li><a class="image_delete" href="image_delete">&empty; Remover</a></li>
				<li><a class="image_add_up" href="image_add_up">&uarr; Nova imagem acima</a></li>
				<li><a class="image_add_down" href="image_add_down">&darr; Nova imagem abaixo</a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- image_item -->
<?php endforeach; ?>
</div> <!-- image_parent -->
