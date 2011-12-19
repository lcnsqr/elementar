<?php foreach($group['accounts'] as $account): ?>
	<div class="tree_parent">

		<div class="tree_listing_row undroppable">
			<div class="tree_listing_bullet">
				<span class="bullet_placeholder">&nbsp;</span>
			</div>
			<div class="tree_listing_icon draggable">
				<img src="/css/backend/icon_account.png" alt="<?php echo $account['user']; ?>" />
			</div>
			<div class="tree_listing_text">
				<p class="label account"><a href="<?php echo $account['id']; ?>"><?php echo $account['user']; ?></a></p>
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
					<li><span class="title"><?php echo $account['user']; ?></span></li>
					<li><hr /></li>
					<li><a class="edit account" href="<?php echo $account['id']; ?>"><?php echo $elementar_edit_account; ?></a></li>
					<li><hr /></li>
					<li><a class="remove account" href="<?php echo $account['id']; ?>" title="<?php echo $elementar_delete; ?> “<?php echo $account['user']; ?>”"><?php echo $elementar_delete; ?></a></li>
				</ul>
			</div>
		</div> <!-- .tree_listing_row -->

	</div> <!-- .tree_parent -->
<?php endforeach; ?>
