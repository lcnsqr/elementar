
<?php if ( is_array($folders) ) : ?>
	<?php foreach($folders as $folder): ?>
	<div class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_bullet">
				<?php if ( $folder['children'] === TRUE ): ?>
				<a href="<?php echo $folder['path']; ?>" class="<?php echo ( (bool) $tree && $tree_dir == $folder['path'] ) ? "unfold" : "fold"; ?> folder_switch folder"></a>
				<?php else: ?>
				<span class="bullet_placeholder">&nbsp;</span>
				<?php endif; ?>
			</div>
			<div class="tree_listing_icon">
				<p><img src="/css/admin/icon_folder.png" alt="<?php echo $folder['name']; ?>" /></p>
			</div>
			<div class="tree_listing_text">
				<p class="label folder"><a class="<?php echo ( $current == $folder['path'] ) ? "current" : ""; ?>" href="<?php echo $folder['path']; ?>" title="<?php echo $folder['name']; ?>"><?php echo $folder['name']; ?></a></p>
			</div>
		</div> <!-- .tree_listing_row -->

		<?php if ( (bool) $tree && $tree_dir == $folder['path'] ) : ?>
			<div style="display: block;" id="tree_listing_content_<?php echo $tree_dir; ?>_<?php echo $folder['path']; ?>" class="tree_listing">
				<?php echo $tree; ?>
			</div>
		<?php else: ?>
			<div id="tree_listing_content_<?php echo $tree_dir; ?>_<?php echo $folder['path']; ?>" class="tree_listing"></div>
		<?php endif; ?>

	</div> <!-- .tree_parent -->
<?php endforeach; ?>
<?php endif; ?>
