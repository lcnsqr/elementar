<p class="page_title">Conteúdo</p>

<div id="content_editor">

<p class="page_subtitle">Edição dos conteúdos</p>

<div id="content_editor_tree" class="aluminium_board">

	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="face_light"></div>

	<div id="tree_parent_0" class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_icon">
				<img src="/img/icon_home.png" alt="<?php echo $parent; ?>" />
			</div>
			<div class="tree_listing_menu">
				<a href="<?php echo $parent_id; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $parent; ?>" /></a>
				<div class="tree_menu">
					<div class="dropdown tree_menu_dropdown">
					<ul>
						<li><a class="meta" href="<?php echo $parent_id; ?>">Meta fields</a></li>
						<li><hr /></li>
						<li><a class="new content" href="<?php echo $parent_id; ?>">Criar conteúdo aqui</a></li>
						<li><a class="new element" href="<?php echo $parent_id; ?>">Criar elemento aqui</a></li>
					</ul>
					</div>
				</div>
			</div>
			<div class="tree_listing_header">
				<h1><?php echo $parent; ?></h1>
			</div>
		</div> <!-- .tree_listing_row -->
		
		<div id="tree_listing_0" class="tree_listing">
		
		<?php include("admin_content_editor_tree.php"); ?>
		
		</div> <!-- #tree_listing_0 -->
	
	</div> <!-- #tree_parent_0 -->

</div> <!-- #content_editor_tree -->

<div id="content_editor_board" class="white_board">
<div class="border top_side"></div>
<div class="border right_side"></div>
<div class="border bottom_side"></div>
<div class="border left_side"></div>
<div class="corner top_left"></div>
<div class="corner top_right"></div>
<div class="corner bottom_right"></div>
<div class="corner bottom_left"></div>

<div id="content_editor_window" style="display: none;">
</div> <!-- #content_editor_window -->

</div> <!-- #content_editor_board -->

<hr style="clear: both; border: 0; height: 0;" />

</div> <!-- #cont_editor -->

