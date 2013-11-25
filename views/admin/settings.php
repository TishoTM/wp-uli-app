<div class="wrap">

	<?php screen_icon(); ?>
	<h2>ULI Ecommerce App</h2>


	<form method="post" action="options.php">


		<?php 
		
		settings_fields( 'signup_topic_group' );

		do_settings_sections( 'uli-app-settings' );
		
		?>


	<?php submit_button(); ?>

	</form>
</div>

<script>
	

	jQuery(function(){

		jQuery("[name='uli_app[topic_key]']").on('change', function(){

			$val = jQuery(":selected", this).text();

			if($val)
			{
				jQuery("[name='uli_app[topic_title]']").val($val);
			}

		});

	});

</script>