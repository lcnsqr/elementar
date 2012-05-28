<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">
	
<div class="pool_board" id="main_tree">
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
				<img src="/css/backend/icon_settings.png" alt="<?php echo $elementar_main; ?>" />
			</div>
			<div class="tree_listing_header">
				<p class="label settings"><a href="main"><?php echo $elementar_main; ?></a></p>
			</div>
		</div>
		
		<div class="tree_listing_row">
			<div class="tree_listing_icon">
				<img src="/css/backend/icon_settings.png" alt="<?php echo $elementar_languages; ?>" />
			</div>
			<div class="tree_listing_header">
				<p class="label settings"><a href="languages"><?php echo $elementar_languages; ?></a></p>
			</div>
		</div>

		<div class="tree_listing_row">
			<div class="tree_listing_icon">
				<img src="/css/backend/icon_settings.png" alt="<?php echo $elementar_email; ?>" />
			</div>
			<div class="tree_listing_header">
				<p class="label settings"><a href="email"><?php echo $elementar_email; ?></a></p>
			</div>
		</div>
	</div>
</div>

<div class="pool_board" id="main_window">
	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>
	<div id="content_window">
	</div> <!-- #content_window -->
	
	<div class="shade_top"></div>
	<div class="shade_bottom"></div>	
</div>

<!-- vertical resizer (tree width -->
<div id="vertical_resizer"></div>

</div> <!-- #main -->

<?php include("global/footer.php"); ?>

<div id="client_warning"><span id="client_msg"></span></div>

<div id="blocker"></div> <!-- #blocker -->
</body>

</html>
