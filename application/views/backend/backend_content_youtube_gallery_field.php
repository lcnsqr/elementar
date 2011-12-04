<div class="youtube_parent youtube_parent_template">
	<div class="youtube_item youtube_item_template">
		<div class="youtube_item_inputs">
			<div class="youtube_item_url">
			<label for=""><?php echo $elementar_youtube_url; ?></label><br />
			<input type="text" name="url" value="" />
			</div> <!-- youtube_item_url -->
			<div class="youtube_item_description">
			<label for=""><?php echo $elementar_youtube_description; ?></label><br />
			<input type="text" name="description" value="" />
			</div> <!-- youtube_item_description -->
		</div> <!-- youtube_item_inputs -->
		<div class="youtube_item_menu">
			<ul>
				<li><a class="youtube_up" href="youtube_up">&uArr; <?php echo $elementar_youtube_move_up; ?></a></li>
				<li><a class="youtube_down" href="youtube_down">&dArr; <?php echo $elementar_youtube_move_down; ?></a></li>
				<li><a class="youtube_delete" href="youtube_delete">&empty; <?php echo $elementar_youtube_delete; ?></a></li>
				<li><a class="youtube_add_up" href="youtube_add_up">&uarr; <?php echo $elementar_youtube_new_above; ?></a></li>
				<li><a class="youtube_add_down" href="youtube_add_down">&darr; <?php echo $elementar_youtube_new_below; ?></a></li>
			</ul>
		</div>
		<div style="width: 100%; clear: both;"></div>
	</div> <!-- youtube_item -->
</div> <!-- youtube_parent -->

<p class="youtube_parent_add"><a class="youtube_add" href="youtube_add">&rarr; <?php echo $elementar_youtube_add; ?></a></p>

<div class="youtube_parent">
<?php if ( is_array($videos) ): ?>
<?php foreach ( $videos as $key => $item ) : ?>
	<div class="youtube_item">

		<div class="youtube_item_inputs">
			<div class="youtube_item_url">
			<label for=""><?php echo $elementar_youtube_url; ?></label><br />
			<?php echo form_input(array('name' => 'url', 'value' => $item['url'])); ?>
			</div> <!-- youtube_item_url -->
			<div class="youtube_item_description">
			<label for=""><?php echo $elementar_youtube_description; ?></label><br />
			<?php echo form_input(array('name' => 'description', 'value' => $item['description'])); ?>
			</div> <!-- youtube_item_description -->
		</div> <!-- youtube_item_inputs -->

		<div class="youtube_item_menu">
			<ul>
				<li><a class="youtube_up" href="youtube_up">&uArr; <?php echo $elementar_youtube_move_up; ?></a></li>
				<li><a class="youtube_down" href="youtube_down">&dArr; <?php echo $elementar_youtube_move_down; ?></a></li>
				<li><a class="youtube_delete" href="youtube_delete">&empty; <?php echo $elementar_youtube_delete; ?></a></li>
				<li><a class="youtube_add_up" href="youtube_add_up">&uarr; <?php echo $elementar_youtube_new_above; ?></a></li>
				<li><a class="youtube_add_down" href="youtube_add_down">&darr; <?php echo $elementar_youtube_new_below; ?></a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
		
	</div> <!-- youtube_item -->
<?php endforeach; ?>

<?php endif; ?>
</div> <!-- youtube_parent -->
