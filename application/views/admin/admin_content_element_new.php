<p class="page_subtitle">Elemento</p>

<?php if ( (bool) $parent_id !== FALSE ): ?>
<p><?php echo $breadcrumb; ?></p>
<?php endif; ?>

<?php if ( (bool) $element_id === FALSE ): ?>
<div>
<span>Criar elemento do tipo </span><?php echo $element_types_dropdown; ?><span> <a id="choose_<?php echo $parent; ?>_element_type" href="<?php echo $parent_id; ?>" title="Criar elemento">Criar</a></span>
</div>
<?php endif; ?>
<hr />
<div style="display: none;" id="element_editor_form">

</div> <!-- #element_editor_form -->
