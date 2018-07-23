<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta name="viewport" content="width=device-width, user-scalable=no">
<title><?php echo $title; ?></title>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php foreach ( $js as $uri ): ?>
<script type="text/javascript" src="<?php echo $uri; ?>"></script>
<?php endforeach; ?>

<?php foreach ( $css as $uri ): ?>
<link rel="stylesheet" href="<?php echo $uri; ?>" type="text/css" />
<?php endforeach; ?>

</head>

<body>

<div id="authenticate">
<?php if ( ! $is_logged ): ?>
<h1>Identificação</h1>
<form name="login" id="login_form" action="<?php echo $action; ?>">
<p class="login_field">
<label for="username">Nome de usuário:</label><br />
<input name="username" id="username" type="text" />
</p>
<p class="login_field">
<label for="password">Senha:</label><br />
<input name="password" id="password" type="password" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>

<form name="forgot_password" id="forgot_password" action="/">
<h1>Esqueci a senha</h1>
<p class="login_field">
<label for="email">Email cadastrado:</label><br />
<input name="email" id="email" type="text" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>

<?php else: ?>

<p><?php echo $username; ?></p>
<p><a href="/account" class="logout">Sair</a></p>
<?php endif; ?>

</div><!-- authenticate -->

</body>

</html>
