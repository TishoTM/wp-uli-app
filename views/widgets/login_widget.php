<div class="uli_app_login_wrapper">

<?php

global $uli_app;

if( $uli_app->is_logged() && $user = $uli_app->user() ): ?>
	
	<a href="<?php echo $uli_app->logout_url; ?>?redirect_url=<?php echo urlencode($uli_app->current_url);?>" title="Logout">Logout</a> | <span style="padding:0 10px;">Welcome <?php echo $user['name']; ?></span> | <a href="<?php echo $uli_app->profile_url; ?>" title="Manage Your Profile" target="_blank">Manage Your Profile</a>


<?php else: ?>

	<a href="#" class="uli_app_login_link" title="Login">Login</a>

	|

	<a href="http:///uli.org/membership/join" title="Join ULI">Join</a>

<?php endif; ?>

</div>

<div id="uli_app_dialog" title="Login" style="padding:38px 60px; display:none;">

	<h3>Login to ULI</h3>

	<form action="<?php echo  $uli_app->current_url; ?>" id="uli_app_login_form" class="validate_form" method="post" enctype="multipart/form-data">

		<label for="uli_app_email">Email</label>
		<input type="email" name="uli_app_email" id="uli_app_email" class="mp_textbox" required />

		<label for="uli_app_password">Password</label>
		<input type="password" name="uli_app_password" id="uli_app_password" class="mp_textbox" required />

		<input type="hidden" value="uli_app_login" name="action" />

		<a id="cant-access" href="<?php echo  $uli_app->password_reset_url; ?>" target="_blank">Can't access your account?</a>

		<input type="checkbox" id="uli_app_remember" name="uli_app_remember" value="1" />
		<label for="uli_app_remember">Stay logged in</label>

		<button id="uli_app_login_btn" type="submit" class="uli_app_button" name="uli_app_login_btn">login</button>

		<div class="uli_app_result_message"></div>

	</form>

</div>


<script id="uli-app-login-success-template" type="text/x-handlebars-template">	

	
	<a href="<?php echo $uli_app->logout_url; ?>?redirect_url=<?php echo urlencode($uli_app->current_url);?>">Logout</a>

	|

    Welcome {{name}}

    |

    <a href="<?php echo $uli_app->profile_url; ?>" target="_blank">Manage Your Profile</a>

	
</script>

<script>
	
	jQuery(function($){

		$('.uli_app_login_link').on('click', function(e){

			e.preventDefault();

			$('#uli_app_dialog').dialog({

				autoOpen: false,
				modal: true,
				width: '400px'

			});

			$('#uli_app_dialog').dialog("open");

		});


		$('#uli_app_dialog').on('submit', '#uli_app_login_form', function(e){

			e.preventDefault();

			var $form = $(e.currentTarget);

			if( ! $form.valid() ) return false;

			$(".uli_app_result_message", $form).text('');

			$.ajax({
				
				url: uli_app_ajax.url,
				type: 'POST',
				data: $form.serialize(),
				beforeSend: function(){
					$("button.uli_app_button[type=submit]", $form).addClass('loading');

				},
				success: function(data){

					if( ! data.status )
					{
						$(".uli_app_result_message", $form).addClass('error').hide().text(data.message).slideDown();
					}
					else
					{
						window.location.href = $form.attr('action');
					}

				},
				complete: function(){
					$("button.uli_app_button[type=submit]", $form).removeClass('loading');
				}

			});


		});




	}, 'jQuery');

</script>
