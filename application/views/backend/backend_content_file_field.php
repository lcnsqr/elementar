<div class="file_parent">
	<div class="file_item">

		<div class="file_item_inputs">
			<div class="file_item_description">
			<label for="<?php echo $input_name; ?>_description">Descrição do arquivo</label><br />
			<input type="text" name="<?php echo $input_name; ?>_description" id="<?php echo $input_name; ?>_description" value="<?php echo $file_description; ?>" class="file_description" id="<?php echo $input_name; ?>_description" />
			</div> <!-- file_item_description -->
<?php if ( (bool) $thumbnail ) : ?>
			<div id="file_item_thumbnail_<?php echo $input_name; ?>" class="file_item_thumbnail" style="background-image: url('<?php echo $thumbnail; ?>');">
<?php else: ?>
			<div id="file_item_thumbnail_<?php echo $input_name; ?>" class="file_item_thumbnail file_item_thumbnail_missing">
<?php endif; ?>
			</div> <!-- file_item_thumbnail -->

			<!-- file details -->
			<ul id='file_details_<?php echo $input_name; ?>' class='file_details <?php echo ( $file_uri == '' ) ? 'hidden' : ''; ?>'>
				<li><strong>URI</strong>: <span class="uri"><?php echo $file_uri; ?></span></li>
				<li><strong>Tipo</strong>: <span class="mime"><?php echo $mime; ?></span></li>
				<li><strong>Tamanho</strong>: <span class="size"><?php echo $size; ?></span></li>
			</ul>

		</div> <!-- file_item_inputs -->

		<div class="file_item_menu">
			<ul>
				<li><a class="browse_file" href="<?php echo $input_name; ?>">&alpha; Arquivo</a></li>
				<li><a class="file_erase" href="<?php echo $input_name; ?>">&empty; Limpar</a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- file_item -->
</div> <!-- file_parent -->
