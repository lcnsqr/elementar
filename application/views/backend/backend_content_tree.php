
<?php if ( is_array($content_hierarchy_element) ) : ?>
	<?php foreach($content_hierarchy_element as $element): ?>
	<div class="tree_parent">

		<div class="tree_listing_row undroppable">
			<div class="tree_listing_bullet">
				<span class="bullet_placeholder">&nbsp;</span>
			</div>
			<div class="tree_listing_icon">
				<img src="/css/backend/icon_element.png" alt="<?php echo $element['name']; ?>" />
			</div>
			<div class="tree_listing_text">
				<p class="label element"><a href="<?php echo $element['id']; ?>"><?php echo $element['name']; ?></a></p>
			</div>
			<div class="tree_listing_menu dark_board">
				<div class="border top_side"></div>
				<div class="border right_side"></div>
				<div class="border bottom_side"></div>
				<div class="border left_side"></div>
				<div class="corner top_left"></div>
				<div class="corner top_right"></div>
				<div class="corner bottom_right"></div>
				<div class="corner bottom_left"></div>
				<div class="foreground"></div>
				<div class="menu_indicator"></div>
				<ul>
					<li><span class="title"><?php echo $element['name']; ?></span></li>
					<li><hr /></li>
					<li><a class="edit element" href="<?php echo $element['id']; ?>"><?php echo $elementar_edit; ?></a></li>
					<li><hr /></li>
					<li><a class="remove element" href="<?php echo $element['id']; ?>" title="<?php echo $elementar_delete; ?> “<?php echo $element['name']; ?>”"><?php echo $elementar_delete; ?></a></li>
				</ul>
			</div>
		</div> <!-- .tree_listing_row -->

	</div> <!-- .tree_parent -->
<?php endforeach; ?>
<?php endif; ?>

<?php if ( is_array($content_hierarchy_content) ) : ?>
	<?php foreach($content_hierarchy_content as $content): ?>
	<div class="tree_parent">

		<div class="tree_listing_row droppable">
			<div class="tree_listing_bullet">
				<?php if ( $content['children'] === TRUE ): ?>  		
				<a href="<?php echo $content['id']; ?>" class="<?php echo ( (bool) $content_listing && $content_listing_id == $content['id'] ) ? "unfold" : "fold"; ?> folder_switch content"></a>
				<?php else: ?>
				<span class="bullet_placeholder">&nbsp;</span>
				<?php endif; ?>
			</div>
			<div class="tree_listing_icon">
				<img src="/css/backend/icon_content.png" alt="<?php echo current(json_decode($content['name'], TRUE)); ?>" />
			</div>
			<div class="tree_listing_text">
				<p class="label content"><a href="<?php echo $content['id']; ?>"><?php echo current(json_decode($content['name'], TRUE)); ?></a></p>
			</div>
			<div class="tree_listing_menu dark_board">
				<div class="border top_side"></div>
				<div class="border right_side"></div>
				<div class="border bottom_side"></div>
				<div class="border left_side"></div>
				<div class="corner top_left"></div>
				<div class="corner top_right"></div>
				<div class="corner bottom_right"></div>
				<div class="corner bottom_left"></div>
				<div class="foreground"></div>
				<div class="menu_indicator"></div>
				<ul>
					<li><span class="title"><?php echo current(json_decode($content['name'], TRUE)); ?></span></li>
					<li><hr /></li>
					<li><a class="edit content" href="<?php echo $content['id']; ?>"><?php echo $elementar_edit_content; ?></a></li>
					<li><a class="edit template" href="<?php echo $content['id']; ?>"><?php echo $elementar_edit_template; ?></a></li>
					<li><a class="edit meta" href="<?php echo $content['id']; ?>"><?php echo $elementar_edit_meta; ?></a></li>
					<li><hr /></li>
					<li><a class="remove content" href="<?php echo $content['id']; ?>" title="<?php echo $elementar_delete; ?> “<?php echo current(json_decode($content['name'], TRUE)); ?>” <?php echo $elementar_and_associated; ?>"><?php echo $elementar_delete; ?></a></li>
					<li><hr /></li>
					<li><a class="new content" href="<?php echo $content['id']; ?>"><?php echo $elementar_new_content; ?></a></li>
					<li><a class="new element" href="<?php echo $content['id']; ?>"><?php echo $elementar_new_element; ?></a></li>
				</ul>
			</div>
		</div>
		<!-- Combine parent ID and content ID to avoid duplicated IDs -->
		<?php if ( (bool) $content_listing && $content_listing_id == $content['id'] ) : ?>
			<div style="display: block;" id="tree_listing_content_<?php echo $parent_id; ?>_<?php echo $content['id']; ?>" class="tree_listing">
				<?php echo $content_listing; ?>
			</div>
		<?php else: ?>
			<div id="tree_listing_content_<?php echo $parent_id; ?>_<?php echo $content['id']; ?>" class="tree_listing"></div>
		<?php endif; ?>

	</div> <!-- .tree_parent -->
	<?php endforeach; ?>
<?php endif; ?>
