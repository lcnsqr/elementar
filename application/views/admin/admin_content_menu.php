<p class="page_title">Menus</p>

<div id="menu_editor">

<p class="page_subtitle">Edição dos menus</p>

<div id="menu_navigation_tab">
	<ul>
		<?php while($menu = current($menus)): ?>
		<li><a class="tab_anchor <?php echo ( key($menus) == 0 ) ? "current" : ""; ?>" href="<?php echo $menu['id']; ?>"><?php echo $menu['name']; ?></a></li>
		<?php next($menus); ?>
		<?php endwhile; ?>
		<li><a class="tab_anchor_add" href="add">+</a></li>
	</ul>
<hr style="clear: both; border: 0; height: 0;" />
</div> <!-- #menu_navigation_tab -->
<div id="menu_window">
	<?php reset($menus); ?>
	<?php while($menu = current($menus)): ?>
	<div <?php echo ( key($menus) == 0 ) ? "style=\"display: block;\"" : ""; ?> class="menu_window_tree" id="menu_window_<?php echo $menu['id']; ?>">

	<?php if ( is_array($menu['children']) ): ?>
	<div id="content_editor_tree">
	<div id="tree_parent_0" class="tree_parent">

		<div class="tree_listing_row">
			<div class="tree_listing_icon">
				<img src="/img/icon_home.png" alt="<?php echo $menu['name']; ?>" />
			</div>
			<div class="tree_listing_menu">
				<a href="<?php echo $menu['id']; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $menu['name']; ?>" /></a>
				<div class="tree_menu">
					<div class="dropdown tree_menu_dropdown">
					<ul>
						<li><a class="new menu" href="<?php echo $menu['id']; ?>">Criar item de menu</a></li>
					</ul>
					</div>
				</div>
			</div>
			<div class="tree_listing_header">
				<!-- <h1><?php echo $menu['name']; ?></h1> -->
				<form class="label menu" action="rename">
					<p>
						<input type="hidden" name="id" value="<?php echo $menu['id']; ?>" />
						<input type="text" name="name" value="<?php echo $menu['name']; ?>" />
					</p>
				</form>
			</div>
		</div> <!-- .tree_listing_row -->
		
		<div id="tree_listing_0" class="tree_listing">

		<?php while($child = current($menu['children'])): ?>
		<div class="tree_parent">
			<div class="tree_listing_row">
				<div class="tree_listing_bullet">
					<span class="bullet_placeholder">&nbsp;</span>
				</div>
				<div class="tree_listing_icon">
					<!-- Links to reorder menus -->
					<map class="menu_move" name="menu_move_<?php echo $child['id']; ?>" id="menu_move_<?php echo $child['id']; ?>">
					<area class="up" shape="rect" coords="0,0,12,6" alt="Subir" href="up" />
					<area class="down" shape="rect" coords="0,6,12,12" alt="Descer" href="down" />
					</map>
					<img alt="<?php echo $child['name']; ?>" src="/img/icon_menu_move.png" width="12" height="12" usemap="#menu_move_<?php echo $child['id']; ?>" />
				</div>
				<div class="tree_listing_menu">
					<a href="<?php echo $child['id']; ?>" class="tree_menu_dropdown_button"><img src="/img/icon_menu_dropdown_off.png" alt="<?php echo $child['name']; ?>" /></a>
					<div class="tree_menu">
						<div class="dropdown tree_menu_dropdown">
						<ul>
							<li><a class="edit menu" href="<?php echo $child['id']; ?>">Editar</a></li>
							<li><a class="remove menu" href="<?php echo $child['id']; ?>" title="Remover “<?php echo $child['name']; ?>”">Excluir</a></li>
						</ul>
						</div>
					</div>
				</div>
				<div class="tree_listing_text">
					<!-- <span class="label"><?php echo $child['name']; ?></span> -->
					
					<form class="label menu" action="rename">
						<p>
							<input type="hidden" name="id" value="<?php echo $child['id']; ?>" />
							<input type="text" name="name" value="<?php echo $child['name']; ?>" />
						</p>
					</form>
				</div>
			</div> <!-- .tree_listing_row -->
	
		</div> <!-- .tree_parent -->
		<?php next($menu['children']); ?>
		<?php endwhile; ?>
	</div> <!-- #tree_listing_0 -->
	</div> <!-- #tree_parent_0 -->
	</div> <!-- #content_editor_tree -->
	<?php endif; ?>

	</div> <!-- #menu_window_<?php echo $menu['id']; ?> -->
	<?php next($menus); ?>
	<?php endwhile; ?>

	<div id="content_editor_window" style="display: none;">
	
	</div> <!-- #content_editor_window -->

	<hr style="clear: both; border: 0; height: 0; " />

</div> <!-- #menu_window -->

</div> <!-- #menu_editor -->
