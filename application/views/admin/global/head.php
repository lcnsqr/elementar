<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<title><?php echo $title; ?></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>

<link rel="stylesheet" href="/css/reset.css" type="text/css" />
<link rel="stylesheet" href="/css/admin.css" type="text/css" />
<link rel="stylesheet" href="/css/admin/admin_tree.css" type="text/css" />
<link rel="stylesheet" href="/css/admin_window.css" type="text/css" />
<link rel="stylesheet" href="/css/admin_menu.css" type="text/css" />

</head>
