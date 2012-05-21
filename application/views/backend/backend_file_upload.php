<div class="upload_form_container" id="upload_form_container_<?php echo $upload_session_id; ?>">
<hr />

<div class="fake_upload_link_container">
<a href="upload_file" class="fake_upload_link"><?php echo $fm_choose_file; ?></a>
</div>
<?php echo $upload_form; ?>
<iframe style="display: none;" id="iframeUpload_<?php echo $upload_session_id; ?>" name="iframeUpload_<?php echo $upload_session_id; ?>" scrolling="no" frameborder="0"></iframe>

<div class="loading"></div>
<a href="discard_upload" class="close_upload"></a>
</div> <!-- upload_form_container -->
