<div class="file_parent file_parent_template">
	<div class="file_item file_item_template">
		<input name="file_item_field" id="file_item_field" type="hidden" value='' class="file_item_field" />
		<div class="file_item_inputs">
			<div class="file_item_description">
			<label><?php echo $elementar_file_description; ?></label><br />
			<input type="text" name="file_item_description_field" value="" class="file_item_description_field" />
			</div> <!-- file_item_description -->
			<div class="file_item_thumbnail file_item_thumbnail_missing">
			</div> <!-- file_item_thumbnail -->
			<!-- file details -->
			<ul class='file_details hidden'>
				<li><strong><?php echo $elementar_file_uri; ?></strong>: <span class="uri"></span></li>
				<li><strong><?php echo $elementar_file_type; ?></strong>: <span class="mime"></span></li>
				<li><strong><?php echo $elementar_file_size; ?></strong>: <span class="size"></span></li>
			</ul>
		</div> <!-- file_item_inputs -->

		<div class="file_item_menu">
			<ul>
				<li><a class="browse_file" href="browse_file">&alpha; <?php echo $elementar_file_browse; ?></a></li>
				<li><a class="file_erase" href="file_erase">&empty; <?php echo $elementar_file_erase; ?></a></li>
				<li><a class="file_up" href="file_up">&uArr; <?php echo $elementar_file_move_up; ?></a></li>
				<li><a class="file_down" href="file_down">&dArr; <?php echo $elementar_file_move_down; ?></a></li>
				<li><a class="file_delete" href="file_delete">&empty; <?php echo $elementar_file_delete; ?></a></li>
				<li><a class="file_add_up" href="file_add_up">&uarr; <?php echo $elementar_file_new_above; ?></a></li>
				<li><a class="file_add_down" href="file_add_down">&darr; <?php echo $elementar_file_new_below; ?></a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- file_item -->
</div> <!-- file_parent -->

<p class="file_parent_add"><a class="file_add" href="file_add">&rarr; <?php echo $elementar_file_add; ?></a></p>
<div class="file_parent">
<?php foreach ( $gallery as $gallery_item ) : ?>
	<?php $file = json_decode($gallery_item, TRUE); $item_id = uniqid(); ?>
	<div class="file_item">
		<input class="file_item_field" name="file_item_field_<?php echo $item_id; ?>" id="file_item_field_<?php echo $item_id; ?>" type="hidden" value='<?php echo $gallery_item; ?>' />
		<div class="file_item_inputs">
			<div class="file_item_description">
			<label><?php echo $elementar_file_description; ?></label><br />
			<input class="file_item_description_field" type="text" name="file_item_field_<?php echo $item_id; ?>_description" id="file_item_field_<?php echo $item_id; ?>_description" value="<?php echo $file['title']; ?>" />
			</div> <!-- file_item_description -->
<?php if ( (bool) $file['thumbnail'] ) : ?>
			<div id="file_item_thumbnail_<?php echo $item_id; ?>" class="file_item_thumbnail" style="background-image: url('<?php echo $file['thumbnail']; ?>');">
<?php else: ?>
			<div id="file_item_thumbnail_<?php echo $item_id; ?>" class="file_item_thumbnail file_item_thumbnail_missing">
<?php endif; ?>
			</div> <!-- file_item_thumbnail -->

			<!-- file details -->
			<ul id='file_details_<?php echo $item_id; ?>' class='file_details <?php echo ( $file['uri'] == '' ) ? 'hidden' : ''; ?>'>
				<li><strong><?php echo $elementar_file_uri; ?></strong>: <span class="uri"><?php echo $file['uri']; ?></span></li>
				<li><strong><?php echo $elementar_file_type; ?></strong>: <span class="mime"><?php echo $file['mime']; ?></span></li>
				<li><strong><?php echo $elementar_file_size; ?></strong>: <span class="size"><?php echo $file['size']; ?></span></li>
			</ul>

		</div> <!-- file_item_inputs -->

		<div class="file_item_menu">
			<ul>
				<li><a class="browse_file" href="browse_file">&alpha; <?php echo $elementar_file_browse; ?></a></li>
				<li><a class="file_erase" href="file_erase">&empty; <?php echo $elementar_file_erase; ?></a></li>
				<li><a class="file_up" href="file_up">&uArr; <?php echo $elementar_file_move_up; ?></a></li>
				<li><a class="file_down" href="file_down">&dArr; <?php echo $elementar_file_move_down; ?></a></li>
				<li><a class="file_delete" href="file_delete">&empty; <?php echo $elementar_file_delete; ?></a></li>
				<li><a class="file_add_up" href="file_add_up">&uarr; <?php echo $elementar_file_new_above; ?></a></li>
				<li><a class="file_add_down" href="file_add_down">&darr; <?php echo $elementar_file_new_below; ?></a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
	</div> <!-- file_item -->
<?php endforeach; ?>
</div> <!-- file_parent -->
