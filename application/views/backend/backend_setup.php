<?php include("global/head.php"); ?>

<body>

<?php include("global/header.php"); ?>

<div id="main">

	<div id="setup">

<?php if ( ! $pending ): ?>
		<form name="setup_form" id="setup_form" action="/setup">
			
			<p class="setup_field">
			<label for="username"><?php echo $elementar_setup_username; ?></label><br />
			<input name="username" id="username" type="text" />
			</p>
			
			<p class="setup_field">
			<label for="email"><?php echo $elementar_setup_email; ?></label><br />
			<input name="email" id="email" type="text" />
			</p>
			
			<p class="setup_field">
			<label for="password"><?php echo $elementar_setup_password; ?></label><br />
			<input name="password" id="password" type="password" />
			</p>
			
			<p class="setup_field">
			<input type="submit" value="<?php echo $elementar_setup_submit; ?>" />
			</p>

		</form>
<?php else: ?>
	<?php echo $pending_message; ?>
<?php endif; ?>
		<div id="blocker"></div>
	
	</div> <!-- #setup -->

</div> <!-- #main -->

<?php include("global/footer.php"); ?>

<div id="client_warning"><span id="client_msg"></span></div>
<div id="blocker"></div>
</body>

</html>
