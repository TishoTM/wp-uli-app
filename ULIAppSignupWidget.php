<?php

class ULIAppSignupWidget extends WP_Widget {


	private $uli_preferences;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		
		parent::__construct(
			'uli_app_signup', // Base ID
			__('ULI App Sign Up', 'text_domain'), // Name
			array( 'description' => __( 'Quick Sign Up form for ULI Ecommerce Application', 'text_domain' ), ) // Args
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


			$current_topic = array();

			$current_topic['key'] = $instance['topic_key'] ? $instance['topic_key'] : $uli_app->topic['key'];
			$current_topic['title'] = $instance['topic_title'] ? $instance['topic_title'] : $uli_app->topic['title'];


			echo $args['before_widget'];
			if ( ! empty( $title ) )
				echo $args['before_title'] . $title . $args['after_title'];

			include( plugin_dir_path( __FILE__ ) . 'views/widgets/signup_widget_view.php');

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


 		global $uli_app;

 		$this->uli_preferences = $uli_app->get_preferences();

 		include( plugin_dir_path( __FILE__ ) . 'views/widgets/signup_widget_form.php');
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
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		$instance['topic_key'] = ( ! empty( $new_instance['topic_key'] ) ) ? strip_tags( $new_instance['topic_key'] ) : '';
		
		if( $instance['topic_key'] )
		{
			$instance['topic_title'] = ( ! empty( $new_instance['topic_title'] ) ) ? strip_tags( $new_instance['topic_title'] ) : '';	
		}
		else
		{
			$instance['topic_title'] = "";
		}

		

		return $instance;
	}
}

?>