<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Account</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="<?php echo JQUERY; ?>"></script>
	<script type="text/javascript" src="<?php echo JS_ACCOUNT; ?>"></script>
</head>

<body>

<?php if ( ! $is_logged ): ?>
<p class="page_title">Identificação</p>
<form name="login" id="login_form" action="/account">
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
<p class="page_title">Esqueci a senha</p>
<p class="login_field">
<label for="email">Email cadastrado:</label><br />
<input name="email" id="email" type="text" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>

<p class="page_title">Cadastro</p>
<form name="login" id="register_form" action="/account">
<p class="login_field">
<label for="user">Nome de usuário:</label><br />
<input name="user" id="user" type="text" />
</p>
<p class="login_field">
<label for="email">Email:</label><br />
<input name="email" id="email" type="text" />
</p>
<p class="login_field">
<label for="password">Senha:</label><br />
<input name="password" id="password" type="password" />
</p>
<p class="login_field"><input type="submit" value="Entrar" /></p>
</form>


<?php else: ?>

<p><?php echo $username; ?></p>
<p><a href="/account" class="logout">Sair</a></p>
<?php endif; ?>

</body>

</html>
