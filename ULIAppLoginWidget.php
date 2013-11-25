<?php

class ULIAppLoginWidget extends WP_Widget {


	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		
		parent::__construct(
			'uli_app_login', // Base ID
			__('ULI App Login', 'text_domain'), // Name
			array( 'description' => __( 'Login form for ULI Ecommerce Application', 'text_domain' ), ) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget

		global $uli_app;

		if($uli_app->status == 'production' || current_user_can('install_plugins'))
		{

			$title = apply_filters( 'widget_title', $instance['title'] );

			echo $args['before_widget'];
			if ( ! empty( $title ) )
				echo $args['before_title'] . $title . $args['after_title'];
			

			include( plugin_dir_path( __FILE__ ) . 'views/widgets/login_widget.php');

			echo $args['after_widget'];

		}
	}


	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
 	public function form( $instance ) {
		// outputs the options form on admin
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}
}

?>