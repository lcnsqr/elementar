<p class="page_subtitle">Conteúdo</p>

<?php if ( (bool) $category_id !== FALSE ): ?>
<p><?php echo $breadcrumb; ?></p>
<?php endif; ?>

<?php if ( (bool) $content_id === FALSE ): ?>
<div>
<span>Criar conteúdo do tipo </span><?php echo $content_types_dropdown; ?><span> <a id="choose_content_type" href="<?php echo $category_id; ?>" title="Criar conteúdo">Criar</a></span>
</div>
<?php endif; ?>

<div style="display: none;" id="type_define_new_container"></div>

<hr />

<div style="display: none;" id="content_editor_form"></div>
