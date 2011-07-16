
<p class="page_title">Usuários</p>

<p class="page_subtitle">Administração de usuários</p>

<p><a href="user_add" id="user_add">Incluir usuário</a></p>

<div id="user_add_form" style="display: none;">
<form action="/admin/account/useradd" id="form_user_add" name="form_user_add">
<p>
<label for="user_login">Login</label><br />
<input name="user_login" id="user_login" type="text" />
</p>
<p>
<label for="user_password">Senha</label><br />
<input name="user_password" id="user_password" type="password" />
</p>
<p>
<label for="user_email">Email</label><br />
<input name="user_email" id="user_email" type="text" />
</p>
<p>
<label for="user_name">Nome completo</label><br />
<input name="user_name" id="user_name" type="text" />
</p>
<p><input type="submit" value="Criar" /></p>
</form> <!-- #form_user_add -->
</div> <!-- #user_add -->

<?php echo $users; ?>
