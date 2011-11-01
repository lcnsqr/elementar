<div class="image_parent">
	<div class="image_item">

		<div class="image_item_inputs">
			<div class="image_item_description">
			<label for="<?php echo $input_name; ?>_description">Descrição da imagem</label><br />
			<input type="text" name="<?php echo $input_name; ?>_description" id="<?php echo $input_name; ?>_description" value="<?php echo $image_description; ?>" class="image_description" id="<?php echo $input_name; ?>_description" />
			</div> <!-- image_item_description -->
<?php if ( (bool) $thumbnail ) : ?>
			<div id="image_item_thumbnail_<?php echo $input_name; ?>" class="image_item_thumbnail" style="background-image: url('<?php echo $thumbnail; ?>');">
<?php else: ?>
			<div id="image_item_thumbnail_<?php echo $input_name; ?>" class="image_item_thumbnail image_item_thumbnail_missing">
<?php endif; ?>
			</div> <!-- image_item_thumbnail -->
		</div> <!-- image_item_inputs -->

		<div class="image_item_menu">
			<ul>
				<li><a class="browse_file" href="<?php echo $input_name; ?>">&alpha; Arquivo</a></li>
				<li><a class="image_erase" href="<?php echo $input_name; ?>">&empty; Limpar</a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- image_item -->
</div> <!-- image_parent -->
