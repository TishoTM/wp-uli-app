<?php
/*
Plugin Name: ULI Login & Signup Form
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Sign In and Sign Up the members to the Application and Mail Newsletter
Version: 1.1
Author: Tihomir Mihaylov
Author URI: http://www.uli.org
License: ?
*/

require_once("ULIAppApi.php");
require_once("ULIAppLoginWidget.php");
require_once("ULIAppSignupWidget.php");

require_once('config.php');


class ULIEcommerceApp {


	// hold an instance of the API class
	protected 	$api;
	
	// user information
	private 	$user;

	// the main domain for the cookie
	public 		$cookie_main_domain;

	// the plugin option from the Admin section
	public 		$topic;

	// if the user is logged in
	private 	$logged_in = false;

	// if the user is with member status
	private 	$is_member = false;

	// the current url
	public 		$current_url;

	// test || production
	public 		$status;


	public 		$logout_url;
	public 		$profile_url;


	function __construct()
	{
		global $uli_app_config;
		
		// set the config params
		$this->cookie_main_domain = $uli_app_config['cookie_main_domain'];
		$this->logout_url = $uli_app_config['logout_url'];
		$this->profile_url = $uli_app_config['profile_url'];
		$this->password_reset_url = $uli_app_config['password_reset_url'];


		$this->current_url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

		$this->api = new ULIAppApi();


		$option = get_option( 'uli_app' );
		
		$this->topic = array(
			'key' => $option['topic_key'],
			'title' => $option['topic_title'],
		);

		
		$this->status = $option['status'];

		

		// check if the user is logged in
		if( $user = $this->api->getUser() )
		{

			$this->user = $user;
			$this->logged_in = true;

			if(isset($user['member']) && $user['member'])
			{
				$this->is_member = true;
			}


			$this->checkPreference($this->topic['key']);

		}
		elseif( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['uli_app_login_btn']) )
		{

			$this->login();

		}

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

		// THE AJAX ADD ACTIONS
		add_action( 'wp_ajax_uli_app_login', array($this, 'login') );
		add_action( 'wp_ajax_nopriv_uli_app_login', array($this, 'login') );
		
		add_action( 'wp_ajax_signup_form', array($this, 'signUp') );
		add_action( 'wp_ajax_nopriv_signup_form', array($this, 'signUp') );


		add_action( 'wp_ajax_uli_login_form', array($this, 'signUpLogin') );
		add_action( 'wp_ajax_nopriv_uli_login_form', array($this, 'signUpLogin') );
		
		add_action( 'wp_ajax_uli_create_account_form', array($this, 'signUpCreateAccount') );
		add_action( 'wp_ajax_nopriv_uli_create_account_form', array($this, 'signUpCreateAccount') );

		add_action( 'wp_ajax_uli_get_preferences', array($this, 'getPreferences') );
		add_action( 'wp_ajax_nopriv_uli_get_preferences', array($this, 'getPreferences') );
		
		add_action( 'wp_ajax_uli_save_preferences', array($this, 'updatePreferences') );
		add_action( 'wp_ajax_nopriv_uli_save_preferences', array($this, 'updatePreferences') );



	}


	public function login()
	{

			$login_data = array(
				'username' => $_POST['uli_app_email'],
				'password' => $_POST['uli_app_password'],
			);

			if(isset( $_POST['uli_app_remember'] ))
			{
				$login_data['remember'] = $_POST['uli_app_remember'];
			}


			if($result = $this->api->loginUser($login_data))
			{
			
				setcookie($result['session']['key'], $result['session']['value'], 0, '/', $this->cookie_main_domain);



				// create a remember cookie
				if( isset($login_data['remember']) )
				{
					if( $result['remember']['key'] && $result['remember']['value'] )
					{
						setcookie($result['remember']['key'], $result['remember']['value'], 0, '/', $this->cookie_main_domain);	
					}
				}

				$this->user = $result['account'];
				$this->logged_in = true;




				$return = array(
					'status' => true,
				);

			}
			else
			{
				$return = array(
					'status' => false,
					'message' => $this->api->message,
				);
			}


			if ( defined('DOING_AJAX') && DOING_AJAX )
			{
				// response output
			    header( "Content-Type: application/json" );
			    
			    echo json_encode($return);

			    exit;
			}


			header("Location: ".$this->current_url);		
	}


	/**
	 * SignUp Process
	 * Action when the user wants to sign up
	 * 
	 * SignUp process begins
	 */
	public function signUp()
	{

	    $topic = $_POST['uli_app_topic'];

	    
	    if($this->logged_in) 							// check if logged in
	    {

	    	if($topic)
	    	{

	    		// check if the user is already signed up
	    		if($this->checkPreference($topic))
	    		{
	    			$response = array(
	    				'action' => 'exists',
	    			);
	    		}
	    		else
	    		{

			    	$result = $this->api->saveUserPreferences($this->user['id'], array($topic), array($topic));
			    	
			    	if($result)
					{
						$response = array(
			    			'action' => 'success',
			    		);

					}
					else
					{
						$response = array(
			    			'action' => 'error',
			    		);
					}

				}

			}
			else
			{
				$response = array(
		    		'action' => 'options',
		    	);
			}
	    	

	    }
	    else  											// if not logged in
	    {

	    	$email = $_POST['uli_app_email'];

	    	if( $this->api->checkEmail($email) )			// check if there is a record with that email
	    	{

	    		// prompt the user to log in, enter the password
	    		$response = array(
	    			'action' => 'login',
	    		);

	    	}
	    	else
	    	{

	    												// prompt the user to create an account
	    		$response = array(
	    			'action' => 'create-account',
	    		);
	    		
	    	}


	    }


	    // response output
	    header( "Content-Type: application/json" );
	    
	    echo json_encode($response);

	    exit;
	}


	/**
	 * SignUp process
	 * When the user is not logged in
	 * The User is prompted for a password to login
	 * Handles the LOGIN process and then the signup
	 *
	 * @return json with the result for further action
	 */
	public function signUpLogin()
	{
		$topic = $_POST['uli_app_topic'];

		$login_data = array(
			'username' => $_POST['uli_app_email'],
			'password' => $_POST['uli_app_password'],
		);

		if(isset( $_POST['uli_app_remember'] ))
		{
			$login_data['remember'] = $_POST['uli_app_remember'];
		}

		if($login_result = $this->api->loginUser($login_data))
		{

			$this->user = $login_result['account'];
			$this->logged_in = true;

			setcookie($login_result['session']['key'], $login_result['session']['value'], 0, '/', $this->cookie_main_domain);
			
			// create a remember cookie
			if( isset($login_data['remember']) )
			{
				if( $login_result['remember']['key'] && $login_result['remember']['value'] )
				{
					setcookie($login_result['remember']['key'], $login_result['remember']['value'], 0, '/', $this->cookie_main_domain);	
				}
			}
			
			// proceed with the sign up

			if($topic)
			{

				// check if the user is already signed up
	    		if($this->checkPreference($topic))
	    		{
	    			$response = array(
	    				'action' => 'exists',
	    				'user' => $login_result['account'],
	    			);
	    		}
	    		else
	    		{

					$result = $this->api->saveUserPreferences($login_result['account']['id'], array($topic), array($topic));

					if($result)
					{
						$response = array(
			    			'action' => 'success',
			    			'user' => $login_result['account'],
			    		);
					}
					else
					{
						$response = array(
			    			'action' => 'error',
			    			'message' => $this->api->message,
			    			'code' => $this->api->code,
			    		);

					}
				}
			}
			else
			{
				$response = array(
		    		'action' => 'options',
		    		'user' => $login_result['account'],
		    	);
			}

		}
		else
		{
			$response = array(
    			'action' => 'error',
    			'message' => $this->api->message,
    			'code' => $this->api->code,
    		);
		}



		 // response output
	    header( "Content-Type: application/json" );
	    
	    echo json_encode($response);

	    exit;

	}


	/**
	 * SignUp process
	 * When the user is not logged in and the email is not found in the system
	 * The User is prompted to create an account
	 * Handles the CREATE ACCOUNT process and then signup
	 *
	 * @return json with the result for further action
	 */
	public function signUpCreateAccount()
	{

		$topic = $_POST['uli_app_topic'];

		
		$post_data = array(
			'email' => $_POST['uli_app_email'],
			'fname' => $_POST['uli_app_fname'],
			'lname' => $_POST['uli_app_lname'],
			'password' => $_POST['uli_app_password'],
		);

		if($this->api->registerUser($post_data))
		{

			// login the user
			$login_data = array(
				'username' => $_POST['uli_app_email'],
				'password' => $_POST['uli_app_password'],
			);

			if($login_result = $this->api->loginUser($login_data))
			{
				setcookie($login_result['session']['key'], $login_result['session']['value'], 0, '/', $this->cookie_main_domain);
			}


			// proceed with the sign up

			$lookup_preferences = $this->api->getAllPreferences();
			$update_preferences = array();
			foreach ($lookup_preferences as $value) {
				$update_preferences[] = $value['value'];
			} 

			if($topic)
			{				

				$result = $this->api->saveUserPreferences($login_result['account']['id'], $update_preferences, array($topic));

				if($result)
				{
					$response = array(
		    			'action' => 'success',
		    			'user' => $login_result['account'],
		    		);
				}
				else
				{
					$response = array(
		    			'action' => 'error',
		    			'message' => $this->api->message,
		    			'code' => $this->api->code,
		    		);

				}
				
			}
			else
			{
				// remove all the preferences assigned by NF by default
				$this->api->saveUserPreferences($login_result['account']['id'], $update_preferences, array());

				$response = array(
	    			'action' => 'options',
	    			'user' => $login_result['account'],
	    		);
			}

		}
		else
		{
			$response = array(
	    		'action' => 'error',
	    		'message' => $this->api->message,
	    		'code' => $this->api->code,
	    	);
		}



		// response output
	    header( "Content-Type: application/json" );
	    
	    echo json_encode($response);

	    exit;

	}

	/**
	* @return json all the preferences
	* via AJAX call
	*/
	public function getPreferences()
	{	
		$preferences = $this->api->getAllPreferences();

		$user_preferences = $this->api->getUserPreferences($this->user['id']);

		// prepare the options with boolean checked
		foreach($preferences as &$preference)
		{

			$preference['checked'] = false;
			
			if( $user_preferences[$preference['value']]['emo_optout_flag'] == 0 )
			{
				$preference['checked'] = true;
			}
		}


		$response = array(
			'preferences' => $preferences,
			// 'selected' => $user_preferences,
		);


		// response output
	    header( "Content-Type: application/json" );
	    
	    echo json_encode($response);

	    exit;
	}

	/**
	 * POST update the user preferences
	 */
	public function updatePreferences()
	{
		$lookup_preferences = $this->api->getAllPreferences();

		$update_preferences = array();
		foreach ($lookup_preferences as $value) {
			$update_preferences[] = $value['value'];
		}
		
		$selected_preferences = $_POST['uli_selected_preferences'];

		$result = $this->api->saveUserPreferences($this->user['id'], $update_preferences, $selected_preferences);


		$response = array(
			'result' => $result,
		);


		// response output
	    header( "Content-Type: application/json" );
	    
	    echo json_encode($response);

	    exit;
	}

	/**
	 * @return array all the preferences
	 */
	public function get_preferences()
	{
		return $this->api->getAllPreferences();
	}


	/**
	 * @return array the user info
	*/
	public function user()
	{
		return $this->user;
		// return $this->api->getUser();
	}


	/**
	 * @return boolean if the user is logged in
	 */
	public function is_logged()
	{
		return $this->logged_in;
	}

	/**
	 * @return boolean if the user is a member
	 */
	public function is_member()
	{
		return $this->is_member;
	}

	/**
	 * @return boolean if the user is signed up
	 */
	public function checkPreference($key)
	{
		if( ! $this->logged_in ) return false;

		return $this->api->checkPreference($this->user['id'], $key);
	}


	public function enqueue_scripts()
	{
		// enqueue and localise scripts
		wp_enqueue_script( 'uli-app-ajax', plugin_dir_url( __FILE__ ) . 'js/functions.js', array( 'jquery' ) );
		
		wp_enqueue_script( 'handlebars', plugin_dir_url( __FILE__ ) . 'js/handlebars.js', array( 'jquery' ) );
		
		wp_enqueue_script( 'jquery_ui', plugin_dir_url( __FILE__ ) . 'js/jquery-ui-1.10.3.custom.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'jquery_validate', plugin_dir_url( __FILE__ ) . 'js/jquery.validate.min.js', array( 'jquery' ) );

		wp_enqueue_style( 'jquery_ui_css', plugin_dir_url( __FILE__ ) . 'css/south-street/jquery-ui-1.10.3.custom.css' );
		wp_enqueue_style( 'uli_app_styles', plugin_dir_url( __FILE__ ) . 'css/uli_app_styles.css' );
		
		wp_localize_script( 'uli-app-ajax', 'uli_app_ajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
				'logged_in' => $this->logged_in
			)
		);

	}

	// ------------------------------------------------------------------------------------------------------------

	/**
	 * Display the Login Widget
	 */
	public function login_widget()
	{
		ob_start();

		include( plugin_dir_path( __FILE__ ) . 'views/widgets/login_widget.php');

		$content = ob_get_contents();
		
		ob_end_clean();

		if($uli_app->status == 'production' || current_user_can('install_plugins'))
		{

			return $content;
			
		}

		return "";
	}

	/**
	 * Display the Quick Sign Up Widget
	 */
	public function signup_widget($topic_key=null, $topic_title=null, $widget_title="")
	{
		$current_topic = array();

		if($topic_key)
		{
			$current_topic['key'] = $topic_key;

			if($topic_title)
			{
				$current_topic['title'] = $topic_title;
			}
			else
			{
				$preferences = $this->api->getAllPreferences();

				foreach ($preferences as $item)
				{
					if($item['value'] == $topic_key)
					{
						$current_topic['title'] = $item['option'];
						break;
					}
				}
			}
		}

		ob_start();

		include( plugin_dir_path( __FILE__ ) . 'views/widgets/signup_widget_view.php');

		$content = ob_get_contents();
		
		ob_end_clean();

		if($uli_app->status == 'production' || current_user_can('install_plugins'))
		{

			return $content;
			
		}

		return "";
	}
}


$uli_app = new ULIEcommerceApp();

if ( is_admin() )	// admin actions
{
	require_once("ULIAppAdmin.php");
	$uli_admin = new ULIAppAdmin();

	$uli_admin->preferences = $uli_app->get_preferences();
}

add_action( 'widgets_init', function()
{
	register_widget( 'ULIAppLoginWidget' );
	register_widget( 'ULIAppSignupWidget' );
});



function show_uli_app_login()
{
	global $uli_app;

	echo $uli_app->login_widget();
}

function show_uli_app_signup($topic_key=null, $topic_title=null, $widget_title="Quick SignUp")
{
	global $uli_app;

	echo $uli_app->signup_widget($topic_key, $topic_title, $widget_title);
}

function all_uli_app_preferences()
{
	global $uli_app;

	return $uli_app->get_preferences();
}

?>