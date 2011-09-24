<p class="image_parent_add"><a class="image_add" href="image_add">&rarr; Incluir imagem</a></p>
<div class="image_parent">
<?php if ( count($gallery) > 0 ) : ?>
<?php foreach ( $gallery as $item ) : ?>
<?php echo $item['item_form']; ?>
<?php endforeach; ?>
<?php endif; ?>
</div> <!-- image_parent -->
