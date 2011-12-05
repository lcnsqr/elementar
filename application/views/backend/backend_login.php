<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">

	<div id="authenticate">
	
		<h1><?php echo $elementar_authentication_title; ?></h1>
		
		<form name="login" id="login_form" action="<?php echo (isset($action)) ? $action : "/admin"; ?>">
			
			<p class="login_field">
			<label for="login_usuario"><?php echo $elementar_authentication_account; ?></label><br />
			<input name="login_usuario" id="login_usuario" type="text" />
			</p>
			
			<p class="login_field">
			<label for="login_senha"><?php echo $elementar_authentication_password; ?></label><br />
			<input name="login_senha" id="login_senha" type="password" />
			</p>
			
			<p class="login_field">
			<input type="submit" value="<?php echo $elementar_authentication_login; ?>" />
			</p>

		</form>
		
		<div id="blocker"></div> <!-- #blocker -->
	
	</div> <!-- #authenticate -->

</div> <!-- #main -->

<?php include("global/footer.php"); ?>

<div id="client_warning"><span id="client_msg"></span></div>
<div id="blocker"></div>
</body>

</html>
