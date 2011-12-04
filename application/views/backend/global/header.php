<div id="header">

<?php if ( $is_logged ): ?>

<div id="resource_menu">
<?php echo $resource_menu; ?>
</div> <!-- #user_menu -->

<div id="user_menu">
<ul>
	<li><strong><?php echo $username; ?></strong></li>
	<li><span class="top_menu_sep">&bull;</span></li>
	<li><a href="/backend/content" title="<?php echo $elementar_exit; ?>" class="logout"><?php echo $elementar_exit; ?></a></li>
</ul>
</div> <!-- #user_menu -->

<?php endif; ?>

<div style="width: 100%; clear: both;"></div>

</div> <!-- #header -->
<div id="header_shadow"></div>

<div id="banner"><div class="logo"></div></div>
