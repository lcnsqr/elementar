<div class="menu_parent menu_parent_template">
	<div class="menu_item menu_item_template">
		<div class="menu_item_inputs">
			<div class="menu_item_name">
			<label for=""><?php echo $elementar_menu_name; ?></label><br />
			<input type="text" name="name" value="" />
			</div> <!-- menu_item_name -->
			<div class="menu_item_target">
			<label for=""><?php echo $elementar_menu_target; ?></label><br />
			<input type="text" name="target" value="" />
			<?php echo $targets; ?>
			</div> <!-- menu_item_target -->
		</div> <!-- menu_item_inputs -->
		<div class="menu_item_menu">
			<ul>
				<li><a class="menu_up" href="menu_up">&uArr; <?php echo $elementar_menu_move_up; ?></a></li>
				<li><a class="menu_down" href="menu_down">&dArr; <?php echo $elementar_menu_move_down; ?></a></li>
				<li><a class="menu_delete" href="menu_delete">&empty; <?php echo $elementar_menu_delete; ?></a></li>
				<li><a class="menu_add_up" href="menu_add_up">&uarr; <?php echo $elementar_menu_new_above; ?></a></li>
				<li><a class="menu_add_down" href="menu_add_down">&darr; <?php echo $elementar_menu_new_below; ?></a></li>
				<li><a class="menu_add_submenu" href="menu_add_submenu">&rarr; <?php echo $elementar_menu_new_submenu; ?></a></li>
			</ul>
		</div>
		<div style="width: 100%; clear: both;"></div>
	</div> <!-- menu_item -->
</div> <!-- menu_parent -->

<div class='menu_field'>
<p class="menu_parent_add"><a class="menu_add" href="menu_add">&rarr; <?php echo $elementar_menu_add; ?></a></p>

<?php echo $html; ?>

</div>
