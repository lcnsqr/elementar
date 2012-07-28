<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?> &laquo; <?php echo $site; ?></title>
<?php echo $extra_head; ?>
	<link rel="icon" href="<?php echo $favicon; ?>" />
<?php foreach ($metafields as $metafield) : ?>
<?php if ( (bool) $metafield['value'] ): ?>
	<meta name="<?php echo $metafield['name']; ?>" content="<?php echo $metafield['value']; ?>" /> 
<?php endif; ?>
<?php endforeach; ?>
	<link rel="stylesheet" href="/main/css/<?php echo $content_id; ?>" />
	<script src="/main/javascript/<?php echo $content_id; ?>"></script>
</head>
<body>
<?php echo $content; ?>
</body>
</html>
