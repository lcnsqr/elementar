<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">

<div id="sections">

<p class="page_title">Identificação</p>
<form name="login" id="login_form" action="<?php echo (isset($action)) ? $action : "/admin"; ?>">
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

<div id="sections_blocker"></div> <!-- #sections_blocker -->

</div> <!-- #sections -->

</div> <!-- #main -->

<?php include("global/footer.php"); ?>

<div id="client_warning"><span id="client_msg"></span></div>

</body>

</html>
