<div class="pseudo_variables_menu">
<h1><?php echo $elementar_template_variables_title; ?></h1>
<div class="pseudo_variables_accordion">

<table>
<tr>

<!-- Content data -->
<td class="pseudo_variables_column">
	<p><strong><?php echo $content_variables_title; ?></strong></p>
	<ul>
<?php foreach ( $content_variables as $content_variable ) : ?>
		<li><a class="add_variable_single" href="<?php echo $content_variable['sname']; ?>"><?php echo $content_variable['name']; ?></a></li>
<?php endforeach; ?>
	</ul>
</td> <!-- pseudo_variables_column -->

<!-- Contents -->
<td class="pseudo_variables_column">
	<p><strong><?php echo $relative_content_variables_title; ?></strong></p>
	<ul>
<?php foreach ( $relative_content_variables as $relative_content_variable_type => $relative_content_variables_type_list ) : ?>
		<li><a class="add_variable_pair" href="<?php echo $relative_content_variables_type_list['pair']; ?>"><strong><?php echo $relative_content_variable_type; ?></strong></a>
		</li>
<?php endforeach; ?>
	</ul>
</td> <!-- pseudo_variables_column -->

<!-- Elementos -->
<td class="pseudo_variables_column">
	<p><strong><?php echo $element_variables_title; ?></strong></p>
	<ul>
<?php foreach ( $element_variables as $element_variable_type => $element_variable_type_list ) : ?>
		<li><a class="variable_pair_menu" href="<?php echo $element_variable_type_list['pair']; ?>"><strong><?php echo $element_variable_type; ?></strong></a>
		<div class="element_filter_menu white_board">
			<div class="border top_side"></div>
			<div class="border right_side"></div>
			<div class="border bottom_side"></div>
			<div class="border left_side"></div>
			<div class="corner top_left"></div>
			<div class="corner top_right"></div>
			<div class="corner bottom_right"></div>
			<div class="corner bottom_left"></div>
			<div class="foreground"></div>
			<div class="menu_indicator"></div>
			<ul>
				<li><span class="title"><?php echo $element_variables_title; ?></span></li>
				<li><hr /></li>
			</ul>
		</div>
		<ul class="pseudo_variables_element_list">
<?php foreach ( $element_variable_type_list['elements'] as $element_single ) : ?>
		<li><a class="add_variable_single" href="<?php echo $element_single['sname']; ?>"><?php echo $element_single['name']; ?></a></li>
<?php endforeach; ?>
		</ul>
		</li>
<?php endforeach; ?>
	</ul>
</td> <!-- pseudo_variables_column -->

</tr>
</table>

</div> <!-- pseudo_variables_accordion -->
<div class="pseudo_variables_menu_switcher_footer"><a href="pseudo_variables_menu_switcher" class="pseudo_variables_menu_switcher collapsed"></a></div>
</div> <!-- pseudo_variables_menu -->
