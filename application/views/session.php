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

<?php if ( ! $is_logged ): ?>
<p class="page_title">Identificação</p>
<form name="login" id="login_form" action="<?php echo (isset($action)) ? $action : "/"; ?>">
<p class="login_field">
<label for="login_usuario">Nome de usuário:</label><br />
<input name="login_usuario" id="login_usuario" type="text" />
</p>
<p class="login_field">
<label for="login_senha">Senha:</label><br />
<input name="login_senha" id="login_senha" type="password" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>

<form name="reset_password" id="reset_password" action="/">
<p class="page_title">Esqueci a senha</p>
<p class="login_field">
<label for="user_email">Email cadastrado:</label><br />
<input name="user_email" id="user_email" type="text" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>


<?php else: ?>

<p><?php echo $username; ?></p>
<p><a href="/user/logout">Sair</a></p>
<?php endif; ?>

</body>

</html>
