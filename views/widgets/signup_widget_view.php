<?php global $uli_app; ?>


<?php if( ! isset($instance) && $widget_title): ?>

	<h3><?php echo $widget_title; ?></h3>

<?php endif; ?>


<?php

// set the topic parameters for the view file
// ------------------------------------------
if(isset($current_topic) && ! empty($current_topic))
{
	$topic = $current_topic;
}
else
{
	$topic = $uli_app->topic;
}

$signed_up = $uli_app->checkPreference($topic['key']);

?>


<div id="uli-signup-wrapper-<?php echo rand(); ?>" class="uli-signup-wrapper">

	<form action="#" class="uli-signup-form" method="post">


		<?php $user_email = $uli_app->is_logged() ? $uli_app->user()['email'] : ''; ?>

		<input type="text" name="uli_app_email" placeholder="E-mail" value="<?php echo $user_email; ?>" />

		<input type="hidden" name="uli_app_topic" value="<?php echo $topic['key']; ?>" />
		<input type="hidden" name="action" value="signup_form" />

		<button type="submit" class="uli_app_button" name="signup_submit">Sign up</button>

	</form>

	<div class="uli_app_signup_dialog" title="Quick SignUp" style="display:none; padding:50px;"></div>






<script id="uli-signup-login-template" type="text/x-handlebars-template">

	<h4>Do We Know You Already?</h4>

	<p>
	Your email address, {{email}}, already exists in our records. Please sign in so we can complete your request.
	</p>

	<br />
	<form id="uli-signup-login-form" action="#" method="post" class="validate_form">
		
		<label for="uli_app_username">Email</label><br />

		<input type="email" name="uli_app_email" id="uli_app_email" value="{{email}}" required />

		<br />

		<label for="uli_app_password">Password</label><br />
		<input type="password" name="uli_app_password" id="uli_app_password" required />

		<input type="hidden" name="action" value="uli_login_form" />

		<input type="hidden" name="uli_app_topic" value="<?php echo $topic['key']; ?>" />

		<br />

		<a href="<?php echo  $uli_app->password_reset_url; ?>" target="_blank">Can't access your account?</a>

		<br /><br />

		<input type="checkbox" id="uli_app_signup_remember" name="uli_app_remember" value="1" />
		<label for="uli_app_signup_remember">Stay logged in</label>

		<br /><br />

		<button type="submit" class="uli_app_button" name="uli_app_login_submit" id="uli_app_login_submit">Continue</button>


		<div class="uli_app_result_message"></div>
	</form>

</script>

<script id="uli-signup-success-template" type="text/x-handlebars-template">

	<h4>Thank you for your interest in ULI</h4>

	You are now subscribed to receive <b><?php echo $topic['title']; ?></b> at {{email}}.

	
	<br /><br />

	<a href="#" class="uli_signup_all_preferences">Manage all my alerts</a>

	<br />

	<button class="dialog_ok_btn">OK</button>
	
</script>

<script id="uli-signup-exists-template" type="text/x-handlebars-template">

	<h4>Thank you for your interest in ULI</h4>

	{{email}} is already subscribed to receive <b><?php echo $topic['title']; ?></b>.

	<br /><br />
	
	If you are not receiving these updates, please contact ULI Customer Service at <br /><br />

	customerservice@uli.org <br /><br />

	United States: 800-321-5011 <br />
	Europe: +44 207 487 9577 <br />
	Everywhere else: +1 410 626 7500 <br />


	<br />

	<a href="#" class="uli_signup_all_preferences">Manage all my alerts</a>

	<br />
	<button class="dialog_ok_btn">OK</button>
	
</script>


<script id="uli-signup-error-template" type="text/x-handlebars-template">

	<h4>Error</h4>

	There is an error with the sign up

	<br />
	<button class="dialog_ok_btn">Close</button>
	
</script>



<script id="uli-signup-create-account-template" type="text/x-handlebars-template">

	<h4>Thank you for your interest in ULI</h4>

	<p>
	Before we subscribe you<?php echo $topic['key'] ? ' to receive <b>'.$topic['title'].'</b>' : '' ;?>, please provide some more information to help us connect you with the right ULI people, content and programs: 
	</p>

	<br />
	<form id="uli-create-account-form" action="#" method="post" class="validate_form">
		
		<label for="uli_app_email">E-mail:</label><br />
		<input type="email" name="uli_app_email" id="uli_app_email" value="{{email}}" required />

		<br />

		<label for="uli_app_fname">First Name:</label><br />
		<input type="text" name="uli_app_fname" id="uli_app_fname" required />

		<br />

		<label for="uli_app_lname">Last Name:</label><br />
		<input type="text" name="uli_app_lname" id="uli_app_lname" required />

		<br />

		<label for="new_uli_app_password">Password:</label><br />
		<input type="password" name="uli_app_password" id="new_uli_app_password" required />

		<br />

		<label for="uli_app_password2">Repeat Password:</label><br />
		<input type="password" name="uli_app_password2" id="uli_app_password2" required equalTo="#new_uli_app_password" />

		<br />

		<input type="hidden" name="action" value="uli_create_account_form" />
		<input type="hidden" name="uli_app_topic" value="<?php echo $topic['key']; ?>" />

		<br />

		<button type="submit" class="uli_app_button">Create Account</button>


		<div class="uli_app_result_message"></div>

		<div class="login_link_wrapper hidden">
			<a href="#" class="uli_app_signup_login_button" title="Login">Login with your existing account</a>
		</div>
	</form>




</script>

<script id="uli-signup-options-template" type="text/x-handlebars-template">

	<h4>Thank you for your interest in ULI</h4>

	<p>
	Select the kinds of information you’d like to receive from ULI:
	</p>

	<form action="#" id="uli_preferences_form" method="post">

		<ul class="uli_preferences_list">

			{{#each options}}
			
			<li>
				<label for="uli_preference_check_{{value}}">
					<input
						value="{{value}}"
						name="uli_selected_preferences[]"
						type="checkbox"
						id="uli_preference_check_{{value}}"
						{{#if checked}} checked {{/if}}
						class="uli_preference_check" />
					{{option}}
				</label>
			</li>

			{{/each}}

		</ul>
		<input type="hidden" name="action" value="uli_save_preferences" />

		
		<button type="submit" class="uli_app_button">Save</button>
		<button class="dialog_ok_btn">Close</button>

	</form>

	<p class="update_success uli_status_label" style="color:#78a22f; display:none;">Success. The information has been updated.</p>
	<p class="update_error uli_status_label" style="color:#ff0000; display:none;">Error. The information could not be updated.</p>

</script>


</div>