<?php if ( is_array($listing) ) : ?>
<ul>
<?php foreach($listing as $item): ?>
<li>
	<a style="background-image: url('<?php echo $item['icon']; ?>');" class="item block <?php echo $item['class']; ?>" href="<?php echo $item['path']; ?>" title="<?php echo $item['name']; ?>">
	<p class="label"><?php echo $item['label']; ?></p>
	</a>
	<div class="item_details">
		<hr />
		<div style="background-image: url('<?php echo $item['icon']; ?>');" class="current_file_icon"></div>
		<p class="current_file_title"><?php echo $item['name']; ?></p>
		<ul>
			<?php if ( $item['class'] != 'directory' ) : ?>
			<li><strong>Tipo</strong>: <?php echo $item['mime']; ?></li>
			<li><strong>Tamanho</strong>: <?php echo $item['size']; ?></li>
			<?php
			switch ( $item['mime'] )
			{
				case 'image/png' :
				case 'image/jpeg' :
				case 'image/gif' :
				echo '<li><strong>Dimensões</strong>: ' . $item['width'] . '&times;' . $item['height'] . '</li>';
				break;
			}
			?>
			<?php endif; ?>
			<li><a href="<?php echo $item['path']; ?>">Renomear</a></li>
			<li><a href="<?php echo $item['path']; ?>" class="current_item_erase">Apagar</a></li>
		</ul>
		<?php if ( $item['class'] != 'directory' ) : ?>
		<hr />
		<ul>
			<li><a href="<?php echo $item['path']; ?>" class="insert">Inserir endereço</a><span class="action_insert"><?php echo $item['path']; ?></span></li>
			<?php
			switch ( $item['mime'] )
			{
				case 'image/png' :
				case 'image/jpeg' :
				case 'image/gif' :
				echo '<li><a href="' . $item['path'] . '" class="insert">Inserir como imagem</a><span class="action_insert">' . $item['img']. '</span></li>';
				break;
			}
			?>
		</ul>
		<?php endif; ?>
	</div>
</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
