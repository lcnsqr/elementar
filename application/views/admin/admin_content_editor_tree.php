
<?php if ( is_array($content_hierarchy_element) ) : ?>
	<?php foreach($content_hierarchy_element as $element): ?>
	<div class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_bullet">
				<span class="bullet_placeholder">&nbsp;</span>
			</div>
			<div class="tree_listing_icon">
				<img src="/img/icon_element.png" alt="<?php echo $element['name']; ?>" />
			</div>
			<div class="tree_listing_menu">
				<a href="<?php echo $element['id']; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $element['name']; ?>" /></a>
				<div class="tree_menu">
					<div class="dropdown tree_menu_dropdown">
					<ul>
						<li><a class="edit element" href="<?php echo $element['id']; ?>">Editar</a></li>
						<li><a class="remove element" href="<?php echo $element['id']; ?>" title="Remover “<?php echo $element['name']; ?>”">Excluir</a></li>
					</ul>
					</div>
				</div>
			</div>
			<div class="tree_listing_text">
				<!-- <span class="label"><?php echo $element['name']; ?></span> -->
				
				<form class="label element" action="rename">
					<p>
						<input type="hidden" name="id" value="<?php echo $element['id']; ?>" />
						<input type="text" name="name" value="<?php echo $element['name']; ?>" />
					</p>
				</form>
			</div>
		</div> <!-- .tree_listing_row -->

	</div> <!-- .tree_parent -->
<?php endforeach; ?>
<?php endif; ?>

<?php if ( is_array($content_hierarchy_category) ) : ?>
	<?php foreach($content_hierarchy_category as $category): ?>
	<div class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_bullet">
				<?php if ( $category['children'] === TRUE ): ?>
				<a href="<?php echo $category['id']; ?>" class="<?php echo ( (bool) $category_listing && $category_listing_id == $category['id'] ) ? "unfold" : "fold"; ?> folder_switch category"></a>
				<?php else: ?>
				<span class="bullet_placeholder">&nbsp;</span>
				<?php endif; ?>
			</div>
			<div class="tree_listing_icon">
				<img src="/img/icon_category.png" alt="<?php echo $category['name']; ?>" />
			</div>
			<div class="tree_listing_menu">
				<a href="<?php echo $category['id']; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $category['name']; ?>" /></a>
				<div class="tree_menu">
					<div class="dropdown tree_menu_dropdown">
					<ul>
						<li><a class="meta category" href="<?php echo $category['id']; ?>">Meta fields</a></li>
						<li><a class="remove category" href="<?php echo $category['id']; ?>" title="Remover “<?php echo $category['name']; ?>” e todo seu conteúdo">Excluir</a></li>
						<li><hr /></li>
						<li><a class="new category" href="<?php echo $category['id']; ?>">Criar categoria aqui</a></li>
						<li><a class="new content" href="<?php echo $category['id']; ?>">Criar conteúdo aqui</a></li>
						<li><a class="new category_element" href="<?php echo $category['id']; ?>">Criar elemento aqui</a></li>
					</ul>
					</div>
				</div>
			</div>
			<div class="tree_listing_text">
				<!-- <span class="label"><?php echo $category['name']; ?></span> -->
				
				<form class="label category" action="rename">
					<p>
						<input type="hidden" name="id" value="<?php echo $category['id']; ?>" />
						<input type="text" name="name" value="<?php echo $category['name']; ?>" />
					</p>
				</form>
			</div>
		</div> <!-- .tree_listing_row -->

		<?php if ( (bool) $category_listing && $category_listing_id == $category['id'] ) : ?>
			<div style="display: block;" id="tree_listing_category_<?php echo $parent_id; ?>_<?php echo $category['id']; ?>" class="tree_listing">
				<?php echo $category_listing; ?>
			</div>
		<?php else: ?>
			<div id="tree_listing_category_<?php echo $parent_id; ?>_<?php echo $category['id']; ?>" class="tree_listing"></div>
		<?php endif; ?>
	</div> <!-- .tree_parent -->
<?php endforeach; ?>
<?php endif; ?>

<?php if ( is_array($content_hierarchy_content) ) : ?>
	<?php foreach($content_hierarchy_content as $content): ?>
	<div class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_bullet">
				<?php if ( $content['children'] === TRUE ): ?>  		
				<a href="<?php echo $content['id']; ?>" class="<?php echo ( (bool) $content_listing && $content_listing_id == $content['id'] ) ? "unfold" : "fold"; ?> folder_switch content"></a>
				<?php else: ?>
				<span class="bullet_placeholder">&nbsp;</span>
				<?php endif; ?>
			</div>
			<div class="tree_listing_icon">
				<img src="/img/icon_content.png" alt="<?php echo $content['name']; ?>" />
			</div>
			<div class="tree_listing_menu">
				<a href="<?php echo $content['id']; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $content['name']; ?>" /></a>
				<div class="tree_menu">
					<div class="dropdown tree_menu_dropdown">
					<ul>
						<li><a class="meta content" href="<?php echo $content['id']; ?>">Meta fields</a></li>
						<li><a class="edit content" href="<?php echo $content['id']; ?>">Editar</a></li>
						<li><a class="remove content" href="<?php echo $content['id']; ?>" title="Remover “<?php echo $content['name']; ?>” e elementos associados">Excluir</a></li>
						<li><hr /></li>
						<li><a class="new content_element" href="<?php echo $content['id']; ?>">Criar elemento aqui</a></li>
					</ul>
					</div>
				</div>
			</div>
			<div class="tree_listing_text">
				<!-- <span class="label"><?php echo $content['name']; ?></span> -->
				<form class="label content" action="rename">
					<p>
						<input type="hidden" name="id" value="<?php echo $content['id']; ?>" />
						<input type="text" name="name" value="<?php echo $content['name']; ?>" />
					</p>
				</form>
			</div>
		</div>
		<!-- Combine category ID and content ID to avoid duplicated IDs -->
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
