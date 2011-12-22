
<div class="order_by">
<form action="/backend/content" name="<?php echo $index_sname; ?>_form">
<input type="hidden" name="content_id" value="<?php echo $content_id; ?>" />
	<p class="title">Ordenar por</p>
		<div class="filter_fields_left">
			<ul>
			<?php if ( (bool) count($order_by) ) : ?>
			<?php foreach($order_by as $field): ?>
			<li><input type="radio" name="order_by" value="<?php echo $field['sname']; ?>" <?php echo ( $field['sname'] == $order_by_checked ) ? 'checked="checked"' : ''; ?> /><label><?php echo $field['name']; ?></label></li>
			<?php endforeach; ?>
			<?php endif; ?>
			</ul>
		</div> <!-- filter_fields_left -->
		<div class="filter_fields_right">
			<?php if ( (bool) count($index_filter) ) : ?>
			<ul>
			<li><input type="radio" name="direction" value="asc" <?php echo ( $index_filter['direction'] == 'asc' ) ? 'checked="checked"' : ''; ?> /><label>Asc</label></li>
			<li><input type="radio" name="direction" value="desc" <?php echo ( $index_filter['direction'] == 'desc' ) ? 'checked="checked"' : ''; ?> /><label>Desc</label></li>
			<li><hr /></li>
			<li>Limite:<br /><input type="text" name="limit" value="<?php echo $index_filter['limit']; ?>" /></li>
			<li><hr /></li>
			<li>Níveis:<br /><input type="text" name="depth" value="<?php echo $index_filter['depth']; ?>" /></li>
			</ul>
			<?php endif; ?>
		</div> <!-- filter_fields_right -->
		<hr class="hr_clear" />
	</form>
</div> <!-- order_by -->
<hr class="hr_clear" />

