<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>File Manager</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/css/backend/backend_file.css" type="text/css" />
	<link rel="stylesheet" href="/css/backend/reset.css" type="text/css" />

<?php foreach ( $js as $uri ): ?>
	<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>

</head>

<body>

<div id="file_manager">

	<div id="file_manager_tree_board" class="pool_board">

	<div id="tree_loading"></div>

	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>

	<div id="tree_parent_1" class="tree_parent">
		<div id="tree_listing_1" class="tree_listing">
		
			<div class="tree_parent">
		
				<div class="tree_listing_row">
					<div class="tree_listing_bullet">
						<?php if ( $folder['children'] === TRUE ): ?>  		
						<a href="<?php echo $folder['path']; ?>" class="<?php echo ( (bool) $folders ) ? "unfold" : "fold"; ?> folder_switch folder"></a>
						<?php else: ?>
						<span class="bullet_placeholder">&nbsp;</span>
						<?php endif; ?>
					</div>
					<div class="tree_listing_icon">
						<img src="/css/backend/icon_folder.png" alt="<?php echo $folder['name']; ?>" />
					</div>
					<div class="tree_listing_text">
						<p class="label folder"><a class="<?php echo ( $current == $folder['path'] ) ? "current" : ""; ?>" href="<?php echo $folder['path']; ?>" title="<?php echo $folder['name']; ?>"><?php echo $folder['name']; ?></a></p>
					</div>
				</div> <!-- .tree_listing_row -->
		
				<div <?php if ( (bool) $folders ) : ?> style="display: block;" <?php endif; ?> id="tree_listing_content_root" class="tree_listing">
		
<?php echo $folders; ?>
				
				</div> <!-- tree_listing -->
				
			</div> <!-- .tree_parent -->

		</div> <!-- #tree_listing_1 -->
	</div> <!-- #tree_parent_1 -->
	
	<div class="shade_top"></div>
	</div> <!-- #file_manager_tree_board -->


	<div id="file_manager_listing_board" class="pool_board">
	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>
	
	<div id="file_manager_listing">
<?php echo $listing; ?>
	</div> <!-- #file_manager_listing -->
	<div class="loading" style="top: 10px; right: 14px; bottom: 10px; left: 14px;"></div>

	<div class="shade_top"></div>
	<div class="shade_bottom"></div>
	</div> <!-- #file_manager_listing_board -->


	<div id="file_manager_action_board" class="pool_board">
	<div class="border top_side"></div>
	<div class="border right_side"></div>
	<div class="border bottom_side"></div>
	<div class="border left_side"></div>
	<div class="corner top_left"></div>
	<div class="corner top_right"></div>
	<div class="corner bottom_right"></div>
	<div class="corner bottom_left"></div>
	<div class="foreground"></div>
	
	<div id="file_manager_action">
		<div id="current_folder_details">
			<div id="current_folder_icon"></div>
			<p id="current_folder_title">Raiz</p>
			<ul>
				<li><a href="<?php echo $folder['path']; ?>" id="current_folder_mkdir">Nova Pasta</a></li>
				<li><a href="<?php echo $folder['path']; ?>" id="current_folder_upload">Enviar Arquivo</a></li>
			</ul>
		</div>
		<div id="current_file_details">
		</div>	
	</div> <!-- #file_manager_action -->

	<div class="shade_top"></div>
	</div> <!-- #file_manager_action_board -->

</div> <!-- file_manager -->

</body>

</html>
