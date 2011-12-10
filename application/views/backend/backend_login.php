<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">

	<div id="authenticate">
	
		<h1><?php echo $elementar_authentication_title; ?></h1>
		
		<form name="login" id="login_form" action="<?php echo (isset($action)) ? $action : "/admin"; ?>">
			
			<p class="login_field">
			<label for="user"><?php echo $elementar_authentication_account; ?></label><br />
			<input name="user" id="user" type="text" />
			</p>
			
			<p class="login_field">
			<label for="password"><?php echo $elementar_authentication_password; ?></label><br />
			<input name="password" id="password" type="password" />
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
