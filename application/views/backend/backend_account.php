<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">

	<div id="account_tree" class="pool_board">
	
		<div id="account_tree_loading"></div>
	
		<div class="border top_side"></div>
		<div class="border right_side"></div>
		<div class="border bottom_side"></div>
		<div class="border left_side"></div>
		<div class="corner top_left"></div>
		<div class="corner top_right"></div>
		<div class="corner bottom_right"></div>
		<div class="corner bottom_left"></div>
		<div class="foreground"></div>
		<div class="face_light"></div>
	
		<div id="tree_parent_1" class="tree_parent">
			
			<div class="tree_listing_row undropable">
				<div class="tree_listing_icon">
					<img style="margin: 0;" src="/css/backend/icon_groups.png" alt="<?php echo $parent; ?>" />
				</div>
				<div class="tree_listing_header">
					<p class="label content"><a href="<?php echo $parent_id; ?>" title="<?php echo $parent; ?>"><?php echo $parent; ?></a></p>
				</div>
				<div class="tree_listing_menu white_board">
					<div class="border top_side"></div>
					<div class="border right_side"></div>
					<div class="border bottom_side"></div>
					<div class="border left_side"></div>
					<div class="corner top_left"></div>
					<div class="corner top_right"></div>
					<div class="corner bottom_right"></div>
					<div class="corner bottom_left"></div>
					<div class="foreground"></div>
					<ul>
						<li><span class="title"><?php echo $parent; ?></span></li>
						<li><a class="new group" href="<?php echo $parent_id; ?>"><?php echo $elementar_new_group; ?></a></li>
					</ul>
				</div>
			</div> <!-- .tree_listing_row -->
			
			<div id="tree_listing_1" class="tree_listing">
			
			<?php /* include("backend_content_tree.php"); */ ?>
			<?php if ( is_array($account_hierarchy_group) ) : ?>
				<?php foreach($account_hierarchy_group as $group): ?>
				<div class="tree_parent">
			
					<div class="tree_listing_row dropable">
						<div class="tree_listing_bullet">
							<?php if ( $group['children'] === TRUE ): ?>  		
							<a href="<?php echo $group['id']; ?>" class="<?php echo ( (bool) $group_listing && $group_listing_id == $group['id'] ) ? "unfold" : "fold"; ?> folder_switch group"></a>
							<?php else: ?>
							<span class="bullet_placeholder">&nbsp;</span>
							<?php endif; ?>
						</div>
						<div class="tree_listing_icon">
							<img src="/css/backend/icon_group.png" alt="<?php echo $group['description']; ?>" />
						</div>
						<div class="tree_listing_text">
							<p class="label group"><a href="<?php echo $group['id']; ?>" title="<?php echo $group['description']; ?>"><?php echo $group['name']; ?></a></p>
						</div>
						<div class="tree_listing_menu white_board">
							<div class="border top_side"></div>
							<div class="border right_side"></div>
							<div class="border bottom_side"></div>
							<div class="border left_side"></div>
							<div class="corner top_left"></div>
							<div class="corner top_right"></div>
							<div class="corner bottom_right"></div>
							<div class="corner bottom_left"></div>
							<div class="foreground"></div>
							<ul>
								<li><span class="title"><?php echo $group['name']; ?></span></li>
								<li><hr /></li>
								<li><a class="edit group" href="<?php echo $group['id']; ?>"><?php echo $elementar_edit_group; ?></a></li>
								<li><hr /></li>
								<li><a class="remove content" href="<?php echo $group['id']; ?>" title="<?php echo $elementar_delete; ?> “<?php echo $group['name']; ?>”"><?php echo $elementar_delete; ?></a></li>
								<li><hr /></li>
								<li><a class="new content" href="<?php echo $group['id']; ?>"><?php echo $elementar_new_group; ?></a></li>
						</ul>
						</div>
					</div>
					<!-- Combine parent ID and content ID to avoid duplicated IDs -->
					<?php if ( (bool) $group_listing && $group_listing_id == $group['id'] ) : ?>
						<div style="display: block;" id="tree_listing_account_<?php echo $parent_id; ?>_<?php echo $group['id']; ?>" class="tree_listing">
							<?php echo $group_listing; ?>
						</div>
					<?php else: ?>
						<div id="tree_listing_content_<?php echo $parent_id; ?>_<?php echo $group['id']; ?>" class="tree_listing"></div>
					<?php endif; ?>
			
				</div> <!-- .tree_parent -->
				<?php endforeach; ?>
			<?php endif; ?>
			
			</div> <!-- #tree_listing_1 -->
		
		<div id="tree_drag_container"></div>
		
		</div> <!-- #tree_parent_1 -->

		<div class="shade_top"></div>
	</div> <!-- #content_tree -->
	
	<div id="account_editor_board" class="pool_board">
	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>
	
	<div id="account_window" style="display: none;">
	</div> <!-- #content_editor_window -->
	
	<div class="shade_top"></div>
	<div class="shade_bottom"></div>	
	</div> <!-- #content_editor_board -->


</div> <!-- #main -->

<?php include("global/footer.php"); ?>

<div id="client_warning"><span id="client_msg"></span></div>

<div id="blocker"></div> <!-- #blocker -->
</body>

</html>
