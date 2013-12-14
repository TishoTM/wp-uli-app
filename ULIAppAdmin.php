<?php


class ULIAppAdmin{

	public $options;

	public $preferences;


	function __construct()
	{
		add_action( 'admin_menu', array($this, 'plugin_admin_menu') );
	}

	public function plugin_admin_menu()
	{
		add_options_page( 'ULI Login & Signup Form', 'Login & Signup Form', 'administrator', 'uli-app-settings', array($this, 'show_admin_options') );

		add_action( 'admin_init', array($this, 'init_plugin_settings') );
	}

	public function init_plugin_settings()
	{
		register_setting( 'signup_topic_group', 'uli_app' );

		add_settings_section(
            'signup_topic_section', // ID
            'ULI SignUp Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'uli-app-settings' // Page
        );  


        add_settings_field(
            'uli_app_topic_key', // ID
            'Topic', // Title 
            array($this, 'topic_callback'),
            'uli-app-settings', // Page
            'signup_topic_section' // Section           
        );      

        add_settings_field(
            'uli_app_topic_title', 
            '', 
            array($this, 'topic_title_callback'),
            'uli-app-settings', 
            'signup_topic_section'
        );



        // --------------------------------------

		add_settings_section(
            'signup_status_section', // ID
            'Login & Signup Form Status', // Title
            array( $this, 'print_status_section_info' ), // Callback
            'uli-app-settings' // Page
        );  


        add_settings_field(
            'uli_app_status', // ID
            'Status', // Title 
            array($this, 'status_callback'),
            'uli-app-settings', // Page
            'signup_status_section' // Section           
        );      


	}

	public function print_section_info()
	{
		print 'Choose SignUp Topic:';	
	}

	public function print_status_section_info()
	{
		echo "<b>Test</b> = admin only visible and uses my-test.uli.org | api-test.uli.org <br />";	
		echo "<b>Production</b> = uses my.uli.org | api.uli.org <br />";	
	}

	/** 
     * Get the settings option array and print one of its values
     */
    public function topic_callback()
    {
      
	    $html = '<select id="signup_topic" name="uli_app[topic_key]">';  
	        
	        $html .= '<option value="0">Select a topic...</option>';  
	        
	        foreach ($this->preferences as $preference)
	        {
	        	$key = $preference['value'];
	        	$title = $preference['option'];

	        	$selected = '';
	        	if(isset( $this->options['topic_key'] ) && $key == $this->options['topic_key']){ $selected = 'selected'; }

	        	$html .= '<option value="'.$key.'" '.$selected.'>'.$title.'</option>';
	        }

	    $html .= '</select>'; 
	      
	    echo $html;
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function topic_title_callback()
    {
    	$value = "";
    	if( isset($this->options['topic_title']) ) $value = $this->options['topic_title'];
        
        $html = '<input name="uli_app[topic_title]" type="hidden" value="'.$value.'" />';

        echo $html;
    }


    public function status_callback()
    {
    	$value = "";    	

    	if( isset($this->options['status']) ) $value = $this->options['status'];

    	$selected = '';

    	// print_r($value); exit;

    	$html = '<select name="uli_app[status]" id="uli_app_status">';    	
    	
    	$selected = $value=="test" ? "selected" : '';
    	$html .= '<option value="test" '.$selected.'>Test</option>';


    	$selected = $value=="production" ? "selected" : '';
    	$html .= '<option value="production" '.$selected.'>Production</option>';

    	$html .= '<select>';

    	echo $html;
    }

	public function show_admin_options()
	{
		$this->options = get_option( 'uli_app' );

		include( plugin_dir_path( __FILE__ ) . 'views/admin/settings.php');
	}


}