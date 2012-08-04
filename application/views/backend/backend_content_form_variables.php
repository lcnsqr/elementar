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
		<li><a class="add_relative_variable_pair" data-variable="<?php echo $relative_content_variables_type_list['pair']; ?>" href="<?php echo $relative_content_variable_type; ?>"><strong><?php echo $relative_content_variable_type; ?></strong></a>
		</li>
<?php endforeach; ?>
	</ul>
</td> <!-- pseudo_variables_column -->

<!-- Elementos -->
<td class="pseudo_variables_column">
	<p><strong><?php echo $element_variables_title; ?></strong></p>
	<ul>
<?php foreach ( $element_variables as $element_variable_type => $element_variable_type_list ) : ?>
		<li><a class="variable_pair_menu" href="<?php echo $element_variable_type_list['sname']; ?>"><strong><?php echo $element_variable_type; ?></strong></a>
		<!-- filters menu -->
		<div class="element_filter_menu dark_board">
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
			<p class="title"><?php echo $filter_of; ?> <?php echo $element_variable_type; ?> <?php echo $filter_at; ?> <?php echo $type_name; ?></p>
			<a href="close" class="close_menu"></a>
			<div class="filter_forms">
				<div class="order_by">
				<form action="/backend/content/xhr_write_template_filter" name="<?php echo $element_variable_type; ?>_form">
					<p class="title"><?php echo $filter_order_by; ?></p>
					<input type="hidden" name="template_id" value="<?php echo $template_id; ?>" />
					<input type="hidden" name="element_type" value="<?php echo $element_variable_type_list['sname']; ?>" />
						<div class="filter_fields_left">
							<ul>
							<?php foreach($element_variable_type_list['filter_form']['order_by'] as $sname => $order_by ): ?>
							<li><input type="radio" name="order_by" value="<?php echo $sname; ?>" <?php echo ( (bool) $order_by['selected'] ) ? 'checked="checked"' : ''; ?> /><label><?php echo $order_by['name']; ?></label></li>
							<?php endforeach; ?>
							</ul>
						</div> <!-- filter_fields_left -->
						<div class="filter_fields_right">
							<ul>
							<li><input type="radio" name="direction" value="asc" <?php echo ( $element_variable_type_list['filter_form']['direction'] == 'asc' ) ? 'checked="checked"' : ''; ?> /><label>Asc</label></li>
							<li><input type="radio" name="direction" value="desc" <?php echo ( $element_variable_type_list['filter_form']['direction'] == 'desc' ) ? 'checked="checked"' : ''; ?> /><label>Desc</label></li>
							<li><hr /></li>
							<li><?php echo $filter_limit; ?>:<br /><input type="text" name="limit" value="<?php echo $element_variable_type_list['filter_form']['limit']; ?>" /></li>
							</ul>
						</div> <!-- filter_fields_right -->
						<hr class="hr_clear" />
					</form>
				</div> <!-- order_by -->
				<div class="insertion">
				<form action="/backend/content" name="<?php echo $element_variable_type; ?>_insert">
					<p class="title"><?php echo $filter_select; ?></p>
					<input type="hidden" name="template_id" value="<?php echo $template_id; ?>" />
					<ul>
						<?php foreach($element_variable_type_list['filter_form']['insert'] as $sname => $insert ): ?>
						<li><input type="checkbox" name="variable[]" value="<?php echo $sname; ?>" /><label><?php echo $insert['name']; ?></label></li>
						<?php endforeach; ?>
					</ul>
				</form>
				</div> <!-- insertion -->
				<hr class="hr_clear" />
				<div class="order_by">
					<p class="form_action"><a class="save_filter" href="filter"><?php echo $filter_save; ?></a></p>
				</div>
				<div class="insertion" style="border: 0;">
					<p class="form_action"><a class="add_variable_pair" href="<?php echo $element_variable_type_list['sname']; ?>"><?php echo $filter_insert; ?></a></p>
				</div>
				<hr class="hr_clear" />
			</div> <!-- filter_forms -->

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
