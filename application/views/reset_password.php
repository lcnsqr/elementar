<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $title; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="/js/jquery-1.5.min.js"></script>
	<script type="text/javascript" src="/js/session.js"></script>
	<script type="text/javascript" src="/js/account.js"></script>
	<link rel="stylesheet" type="text/css" href="/css/reset.css" />
</head>

<body>

<?php if ( $verified === TRUE): ?>
<form name="change_password" id="change_password" action="/">
<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
<p class="login_field">
<label for="nova_senha">Nova Senha:</label><br />
<input name="nova_senha" id="nova_senha" type="password" />
</p>
<p class="login_field"><input type="submit" value="Alterar" /></p>
</form>

<?php else: ?>

<p><?php echo $verified; ?></p>

<?php endif; ?>

</body>

</html>
