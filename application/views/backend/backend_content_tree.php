
<?php if ( is_array($content_hierarchy_element) ) : ?>
	<?php foreach($content_hierarchy_element as $element): ?>
	<div class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_bullet">
				<span class="bullet_placeholder">&nbsp;</span>
			</div>
			<div class="tree_listing_icon">
				<img src="/css/backend/icon_element.png" alt="<?php echo current(json_decode($element['name'], TRUE)); ?>" />
			</div>
			<div class="tree_listing_text">
				<p class="label element"><a href="<?php echo $element['id']; ?>" title="<?php echo current(json_decode($element['name'], TRUE)); ?>"><?php echo current(json_decode($element['name'], TRUE)); ?></a></p>
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
					<li><span class="title"><?php echo current(json_decode($element['name'], TRUE)); ?></span></li>
					<li><hr /></li>
					<li><a class="edit element" href="<?php echo $element['id']; ?>">Editar</a></li>
					<li><hr /></li>
					<li><a class="remove element" href="<?php echo $element['id']; ?>" title="Remover “<?php echo current(json_decode($element['name'], TRUE)); ?>”">Excluir</a></li>
				</ul>
			</div>
		</div> <!-- .tree_listing_row -->

	</div> <!-- .tree_parent -->
<?php endforeach; ?>
<?php endif; ?>

<?php if ( is_array($content_hierarchy_content) ) : ?>
	<?php foreach($content_hierarchy_content as $content): ?>
	<div class="tree_parent">

		<div class="tree_listing_row dropable">
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
				<p class="label content"><a href="<?php echo $content['id']; ?>" title="<?php echo current(json_decode($content['name'], TRUE)); ?>"><?php echo current(json_decode($content['name'], TRUE)); ?></a></p>
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
					<li><span class="title"><?php echo current(json_decode($content['name'], TRUE)); ?></span></li>
					<li><hr /></li>
					<li><a class="edit content" href="<?php echo $content['id']; ?>">Editar Conteúdo</a></li>
					<li><a class="edit template" href="<?php echo $content['id']; ?>">Editar Template</a></li>
					<li><a class="edit meta" href="<?php echo $content['id']; ?>">Meta Fields</a></li>
					<li><hr /></li>
					<li><a class="remove content" href="<?php echo $content['id']; ?>" title="Remover “<?php echo current(json_decode($content['name'], TRUE)); ?>” e elementos associados">Excluir</a></li>
					<li><hr /></li>
					<li><a class="new content" href="<?php echo $content['id']; ?>">Criar conteúdo aqui</a></li>
					<li><a class="new element" href="<?php echo $content['id']; ?>">Criar elemento aqui</a></li>
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
