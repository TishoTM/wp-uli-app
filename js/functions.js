jQuery(function($){


	// The process starts
	$(".uli-signup-form").on('submit', function(e){

		e.preventDefault();
		$form = $(this);

		$widget = $(this).parent();

		var email = $("[name='uli_app_email']", this).val();

		$.ajax({

			url: uli_app_ajax.url,
			type: "POST",
			data: $(this).serialize(),
			success: function(data){

				var tpl;
				
				if(data.action == 'options')
				{
					tpl = ajax_get_preferences_view();
				}
				else
				{

					var source   = $("#uli-signup-"+data.action+"-template", $widget).html();
					var template = Handlebars.compile(source);

					var tpl = template({email:email});
				}
			

				$dialog = $widget.find('.uli_app_signup_dialog');

				
					$dialog.html(tpl).dialog({

						autoOpen: false,
						modal: true,
						width: '400px',
						close: function(event, ui){
							uli_app_ajax.widget_id = '';
							$(this).dialog("destroy"); 
						}
					});
				
				// add the id of the wrapper
				uli_app_ajax.widget_id = $widget.attr('id');
				
				
				$dialog.dialog('open');
				
			},	
			complete: function(data, request){


			}	

			

		});

	});


	// User is not logged in
	// The email is found in NF
	// The LOGIN form is shown
	$(".uli_app_signup_dialog").on('submit', "#uli-signup-login-form, #uli-create-account-form", function(e){

		e.preventDefault();

		var $form = $(e.currentTarget);

		if( ! $form.valid() ) return false;

		var email = $("[name='uli_app_email']", this).val();

		$('.uli_app_result_message', $form).text('');

		$.ajax({
			url: uli_app_ajax.url,
			type: 'POST',
			data: $(this).serialize(),
			beforeSend: function(){
				$("button.uli_app_button[type=submit]", $form).addClass('loading');
			},
			success: function(data){

				var tpl;

				if(data.action == 'options')
				{
					tpl = ajax_get_preferences_view();
				}
				else if(data.action == 'success' || data.action == 'exists')
				{

					var source   = $("#uli-signup-"+data.action+"-template", "#"+uli_app_ajax.widget_id).html();
					var template = Handlebars.compile(source);

					tpl = template({email:email});

				}
				else{

					$('.uli_app_result_message', $form).addClass('error').hide().text(data.message).slideDown();

					if(data.code == 401)
					{
						$('.login_link_wrapper').slideDown();
					}

					return false;
				}


				if(data.action != 'error' && data.user.name)
				{
					// Show the logged-in user in the header
					source = $("#uli-app-login-success-template").html();
					template = Handlebars.compile(source);

					var login_tpl = template({name:data.user.name});
					$(".uli_app_login_wrapper").html(login_tpl);
				}
				

				$(".uli_app_signup_dialog").html(tpl);

			},
			complete: function(){
				$("button.uli_app_button[type=submit]", $form).removeClass('loading');
			}
		});


	});

	// When Creating Account returns email exists
	// handle the process from the login step forward
	$(".uli_app_signup_dialog").on('click', '.uli_app_signup_login_button', function(e){

		e.preventDefault();

		$dialog = $(e.delegateTarget);

		$widget = $dialog.parent();

		var entered_email = $('#uli_app_email', '#uli-create-account-form').val();

		$("input[name=uli_app_email]", "#"+uli_app_ajax.widget_id+" form.uli-signup-form").val(entered_email);


		// submit the form
		$("form.uli-signup-form", "#"+uli_app_ajax.widget_id).submit();

		// close the dialog
		$dialog.dialog('close');
	});



	$(".uli_app_signup_dialog").on('click', ".dialog_ok_btn", function(e){

		e.preventDefault();

		$dialog = $(e.delegateTarget);

		$dialog.dialog("close");

	});


	/**
	 * Show all the preferences in a list of checkboxes
	 */
	$(".uli_app_signup_dialog").on('click', '.uli_signup_all_preferences', function(e){


		e.preventDefault();


		$dialog = $(e.delegateTarget);

		var options_tpl = ajax_get_preferences_view();

		$dialog.children().slideUp().detach().delay(1000);

		$dialog.html(options_tpl).children().not(":hidden").hide().slideDown();

	});

	$(".uli_app_signup_dialog").on('submit', '#uli_preferences_form', function(e){

		e.preventDefault();

		$dialog = $(e.delegateTarget);

		$form = $(this);

		$.ajax({
			url: uli_app_ajax.url,
			type: "POST",
			data: $form.serialize(),
			beforeSend: function(){
				$("button.uli_app_button[type=submit]", $form).addClass('loading');
			},
			success: function(data){

				// console.log(data); return false;

				$(".uli_status_label").hide();

				if(data.result)
				{
					$(".uli_status_label.update_success").slideDown();
				}
				else{
					$(".uli_status_label.update_error").slideDown();	
				}

			},
			complete: function(){
				$("button.uli_app_button[type=submit]", $form).removeClass('loading');
			}
		});

	});

	var ajax_get_preferences_view = function(){

		var options_tpl;

		$.ajax({
			url: uli_app_ajax.url,
			type: 'POST',
			async: false,
			data: {action: 'uli_get_preferences'},
			success: function(data){

				var source   = $("#uli-signup-options-template").html();
				var template = Handlebars.compile(source);

				options_tpl = template({options:data.preferences});
			}	
		});

		return options_tpl;

	};


	// $.extend(jQuery.validator.messages, {
	//     equalTo:"The Repeat Password and Password must match"
	// });

	$.validator.setDefaults({
		errorElement: 'div',
		messages: {
			uli_app_password2: {
				equalTo:"The Repeat Password and Password must match"
			}
		}
	});




}, 'jQuery');