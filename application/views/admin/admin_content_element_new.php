<p class="page_subtitle">Elemento</p>

<?php if ( (bool) $parent_id !== FALSE ): ?>
<p><?php echo $breadcrumb; ?></p>
<?php endif; ?>

<?php if ( (bool) $element_id === FALSE ): ?>
<div>
<span>Criar elemento do tipo </span><?php echo $element_types_dropdown; ?><span> <a id="choose_element_type_for_parent_id" href="<?php echo $parent_id; ?>" title="Criar elemento">Criar</a></span>
</div>
<?php endif; ?>

<div style="display: none;" id="type_define_new_container"></div>

<hr />
<div style="display: none;" id="element_editor_form"></div>
