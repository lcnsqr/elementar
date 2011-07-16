<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $title; ?> &laquo; <?php echo $site_name; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php foreach ($metafields as $metafield) : ?>
	<meta name="<?php echo $metafield['name']; ?>" content="<?php echo $metafield['content']; ?>" /> 
<?php endforeach; ?>

	<script type="text/javascript" src="/js/jquery-1.5.min.js"></script>
	
	<link rel="stylesheet" href="/css/reset.css" type="text/css" />
</head>

<body>

<div id="top_menu">
<ul>
<?php while ($attrs = current($principal)) : ?>
	<li>
		<a href="<?php echo $attrs['target']; ?>"><?php echo key($principal); ?></a>

		<?php if ( is_array($attrs['menu']) ) : ?>
		<div class="dropdown">
			<ul>
			<?php while ($sub_attrs = current($attrs['menu']) ) : ?>
				<li><a href="<?php echo $sub_attrs['target']; ?>"><?php echo key($attrs['menu']); ?></a></li>
				<?php next($attrs['menu']); ?>
			<?php endwhile; ?>
			</ul>
		</div> <!-- .dropdown -->
		<?php endif; ?>

	</li>

	<?php if ( next($principal) ): ?>
	<li>|</li>
	<?php endif; ?>
<?php endwhile; ?>
</ul>

</div> <!-- #top_menu -->

<p><?php echo $breadcrumb; ?></p>

<p><?php echo $title; ?></p>

</body>

</html>
