<div class="pseudo_variables_menu">
<h1>Campos de conteúdo e elementos</h1>
<div class="pseudo_variables_accordion">

<!-- Content data -->
<div class="pseudo_variables_column">
	<p><strong><?php echo $content_singles_title; ?></strong></p>
	<ul>
<?php foreach ( $content_singles as $content_single ) : ?>
		<li><a class="add_variable_single" href="<?php echo $content_single['sname']; ?>"><?php echo $content_single['name']; ?></a></li>
<?php endforeach; ?>
	</ul>
</div> <!-- pseudo_variables_column -->

<!-- Elementos -->
<div class="pseudo_variables_column">
	<p><strong><?php echo $element_singles_title; ?></strong></p>
	<ul>
<?php foreach ( $element_singles as $element_single_type => $element_single_list ) : ?>
		<li><a class="add_variable_pair" href="<?php echo $element_single_list['pair']; ?>"><strong><?php echo $element_single_type; ?></strong></a>
		<ul class="pseudo_variables_element_list">
<?php foreach ( $element_single_list['elements'] as $element_single ) : ?>
		<li><a class="add_variable_single" href="<?php echo $element_single['sname']; ?>"><?php echo $element_single['name']; ?></a></li>
<?php endforeach; ?>
		</ul>
		</li>
<?php endforeach; ?>
	</ul>
</div> <!-- pseudo_variables_column -->

<hr style="clear: both; border: 0; height: 0;" />

</div> <!-- pseudo_variables_accordion -->
<div class="pseudo_variables_menu_switcher_footer"><a href="pseudo_variables_menu_switcher" class="pseudo_variables_menu_switcher collapsed"></a></div>
</div> <!-- pseudo_variables_menu -->
