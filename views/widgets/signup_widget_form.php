<?php

if ( isset( $instance[ 'title' ] ) ) {
	$title = $instance[ 'title' ];
}
else {
	$title = __( 'Subscribe Form', 'text_domain' );
}
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>


<label for="<?php echo $this->get_field_id( 'topic_key' ); ?>"><?php _e( 'Topic:' ); ?></label> 
<select name="<?php echo $this->get_field_name( 'topic_key' ); ?>" id="<?php echo $this->get_field_id( 'topic_key' ); ?>">


	<option value="0">Default Topic form the settings</option>

	<?php foreach ($this->uli_preferences as $preference): ?>

	<?php

	$selected = '';
	if( isset($instance['topic_key']) && $instance['topic_key'] == $preference['value'] )
	{
		$selected = 'selected';
	}

	?>
		
	<option value="<?php echo $preference['value']; ?>" <?php echo $selected; ?>><?php echo $preference['option']; ?></option>

	<?php endforeach; ?>

</select>




<input type="hidden" name="<?php echo $this->get_field_name( 'topic_title' ); ?>" id="<?php echo $this->get_field_id( 'topic_title' ); ?>" value="<?php echo isset($instance['topic_title']) ? $instance['topic_title'] : ''; ?>" />


<script>
	

	jQuery(function(){

		jQuery("#<?php echo $this->get_field_id( 'topic_key' ); ?>").on('change', function(){

			$val = jQuery(":selected", this).text();

			if($val)
			{
				jQuery("#<?php echo $this->get_field_id( 'topic_title' ); ?>").val($val);
			}

		});

	});

</script>