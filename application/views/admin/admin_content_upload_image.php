<div id="upload_image_<?php echo $form_upload_session; ?>" class="upload_image_container">

<div class="upload_image_form" <?php echo ( $img_url == "" ) ? "style=\"display: block;\"" : ""; ?> >
<?php echo $form_upload; ?>
</div> <!-- .upload_image_form -->

<div class="upload_image_loading">
<p><img src="/img/ajax-loader.gif" alt="Carregando..." /> Carregando imagem...</p>
<p><a href="upload_image_cancel" class="upload_image_cancel">Cancelar</a></p>
</div> <!-- .upload_image_loading -->

<div class="upload_image_display" <?php echo ( $img_url != "" ) ? "style=\"display: block;\"" : ""; ?> >
<p><img src="<?php echo $img_url; ?>" alt="" class="upload_image_display_thumb" /></p>
<p><a href="upload_image_change" class="upload_image_change" >Alterar</a></p>

</div> <!-- .upload_image_display -->

<iframe style="display: none;" class="iframeUpload_<?php echo $form_upload_session; ?>" name="iframeUpload_<?php echo $form_upload_session; ?>" scrolling="no" frameborder="0"></iframe>
		
</div> <!-- .upload_image_container -->
