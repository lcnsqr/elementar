<?php if ( is_array($groups) ) : ?>
	<?php foreach($groups as $group): ?>
	<div class="tree_parent">

		<div class="tree_listing_row droppable">
			<div class="tree_listing_bullet">
				<?php if ( $group['children'] === TRUE ): ?>  		
				<a href="<?php echo $group['id']; ?>" class="<?php echo ( (bool) $group['display_accounts'] ) ? "unfold" : "fold"; ?> folder_switch group"></a>
				<?php else: ?>
				<span class="bullet_placeholder">&nbsp;</span>
				<?php endif; ?>
			</div>
			<div class="tree_listing_icon" style="cursor: auto !important;">
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
				<div class="menu_indicator"></div>
				<ul>
					<li><span class="title"><?php echo $group['name']; ?></span></li>
					<li><hr /></li>
					<li><a class="edit group" href="<?php echo $group['id']; ?>"><?php echo $elementar_edit_group; ?></a></li>
					<li><hr /></li>
					<li><a class="remove group" href="<?php echo $group['id']; ?>" title="<?php echo $elementar_delete; ?> “<?php echo $group['name']; ?>”"><?php echo $elementar_delete; ?></a></li>
					<li><hr /></li>
					<li><a class="new account" href="<?php echo $group['id']; ?>"><?php echo $elementar_new_account; ?></a></li>
			</ul>
			</div>
		</div>

		<?php if ( (bool) $group['display_accounts'] ) : ?>
			<div style="display: block;" class="tree_listing">
			<?php include('backend_account_tree_group.php'); ?>
			</div> <!-- tree_listing -->
		<?php else: ?>
			<div class="tree_listing">
			</div> <!-- tree_listing -->
		<?php endif; ?>

	</div> <!-- .tree_parent -->
	<?php endforeach; ?>
<?php endif; ?>
