<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title><?php echo $title; ?> &laquo; <?php echo $site; ?></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<?php foreach ($metafields as $metafield) : ?>
<?php if ( (bool) $metafield['value'] ): ?>
<meta name="<?php echo $metafield['name']; ?>" content="<?php echo $metafield['value']; ?>" /> 
<?php endif; ?>
<?php endforeach; ?>

<?php echo $extra_head; ?>

<link rel="stylesheet" href="/main/css/<?php echo $content_id; ?>" type="text/css" />
<script type="text/javascript" src="/main/javascript/<?php echo $content_id; ?>"></script>

</head>

<body>
<?php echo $content; ?>
</body>
</html>
