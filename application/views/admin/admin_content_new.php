<p class="page_subtitle">Conteúdo</p>

<?php if ( (bool) $category_id !== FALSE ): ?>
<p><?php echo $breadcrumb; ?></p>
<?php endif; ?>

<?php if ( (bool) $content_id === FALSE ): ?>
<p>Criar conteúdo do tipo <?php echo $content_types_dropdown; ?> <a id="choose_content_type" href="<?php echo $category_id; ?>" title="Criar conteúdo">Criar</a></p>
<?php endif; ?>
<hr />
<div style="display: none;" id="content_editor_form">

</div> <!-- #content_editor_form -->
