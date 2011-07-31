<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $title; ?> &laquo; <?php echo $site_name; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php foreach ($metafields as $metafield) : ?>
	<meta name="<?php echo $metafield['name']; ?>" content="<?php echo $metafield['content']; ?>" /> 
<?php endforeach; ?>

<?php if ( array_key_exists('javascript', $elements) ) : ?>
	<?php foreach ( $elements['javascript'] as $javascript ) : ?>
		<?php if ( (bool) $javascript['url'] ) : ?>
			<script type="text/javascript" src="<?php echo $javascript['url']; ?>"></script>
		<?php endif; ?>
		<?php if ( (bool) $javascript['code'] ) : ?>
			<script type="text/javascript">
			//<![CDATA[
			<?php echo $javascript['code']; ?>
			//]]>
			</script>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
	
	<link rel="stylesheet" href="/css/reset.css" type="text/css" />
</head>
