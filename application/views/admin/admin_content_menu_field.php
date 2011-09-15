<div class="menu_parent" id="menu_parent_template">
	<div class="menu_item" id="menu_item_template">
		<div class="menu_item_inputs">
			<div class="menu_item_name">
			<label for="">Nome</label><br />
			<input type="text" name="name" value="" />
			</div> <!-- menu_item_name -->
			<div class="menu_item_target">
			<label for="">Destino</label><br />
			<input type="text" name="target" value="" />
			<?php echo $targets; ?>
			</div> <!-- menu_item_target -->
		</div> <!-- menu_item_inputs -->
		<div class="menu_item_menu">
			<ul>
				<li><a class="menu_up" href="menu_up">&uArr; Mover para cima</a></li>
				<li><a class="menu_down" href="menu_down">&dArr; Mover para baixo</a></li>
				<li><a class="menu_delete" href="menu_delete">&empty; Remover</a></li>
				<li><a class="menu_add_up" href="menu_add_up">&uarr; Novo menu acima</a></li>
				<li><a class="menu_add_down" href="menu_add_down">&darr; Novo menu abaixo</a></li>
				<li><a class="menu_add_submenu" href="menu_add_submenu">&rarr; Novo submenu</a></li>
			</ul>
		</div>
		<div style="width: 100%; clear: both;"></div>
	</div> <!-- menu_item -->
</div> <!-- menu_parent -->

<p class="menu_parent_add"><a class="menu_add" href="menu_add">&rarr; Novo menu</a></p>

<?php if ( is_array($menu) ) _render_menu_field($menu, $targets); ?>

<?php function _render_menu_field($menu, $targets) { ?>
<div class="menu_parent">
<?php foreach ( $menu as $key => $item ) : ?>
	<div class="menu_item">

		<div class="menu_item_inputs">
			<div class="menu_item_name">
			<label for="">Nome</label><br />
			<?php echo form_input(array('name' => 'name', 'value' => $item['name'])); ?>
			</div> <!-- menu_item_name -->
			<div class="menu_item_target">
			<label for="">Destino</label><br />
			<?php echo form_input(array('name' => 'target', 'value' => $item['target'])); ?>
			<?php echo $targets; ?>
			</div> <!-- menu_item_target -->
		</div> <!-- menu_item_inputs -->

		<div class="menu_item_menu">
			<ul>
				<li><a class="menu_up" href="menu_up">&uArr; Mover para cima</a></li>
				<li><a class="menu_down" href="menu_down">&dArr; Mover para baixo</a></li>
				<li><a class="menu_delete" href="menu_delete">&empty; Remover</a></li>
				<li><a class="menu_add_up" href="menu_add_up">&uarr; Novo menu acima</a></li>
				<li><a class="menu_add_down" href="menu_add_down">&darr; Novo menu abaixo</a></li>
				<li><a class="menu_add_submenu" href="menu_add_submenu">&rarr; Novo submenu</a></li>
			</ul>
		</div>

		<div style="width: 100%; clear: both;"></div>
		
<?php if ( is_array($item['menu']) ) : ?>
<?php _render_menu_field($item['menu'], $targets); ?>
<?php endif; ?>
	</div> <!-- menu_item -->
<?php endforeach; ?>

</div> <!-- menu_parent -->

<?php } /* _render_menu_field() */ ?>
