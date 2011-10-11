<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">
	
	<div id="content_tree" class="pool_board">
	
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
	
			<div class="tree_listing_row">
				<div class="tree_listing_icon">
					<img style="margin: 0;" src="/img/icon_home.png" alt="<?php echo $parent; ?>" />
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
						<li><hr /></li>
						<li><a class="edit content" href="<?php echo $parent_id; ?>">Editar Conteúdo</a></li>
						<li><a class="edit template" href="<?php echo $parent_id; ?>">Editar Template</a></li>
						<li><a class="edit meta" href="<?php echo $parent_id; ?>">Meta Fields</a></li>
						<li><hr /></li>
						<li><a class="new content" href="<?php echo $parent_id; ?>">Criar conteúdo aqui</a></li>
						<li><a class="new element" href="<?php echo $parent_id; ?>">Criar elemento aqui</a></li>
					</ul>
				</div>
			</div> <!-- .tree_listing_row -->
			
			<div id="tree_listing_1" class="tree_listing">
			
			<?php include("admin_content_tree.php"); ?>
			
			</div> <!-- #tree_listing_1 -->
		
		</div> <!-- #tree_parent_1 -->

		<div class="shade_top"></div>
		<div class="shade_bottom"></div>	
	</div> <!-- #content_tree -->
	
	<div id="content_editor_board" class="pool_board">
	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>
	
	<div id="content_window" style="display: none;">
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
