<?php

class ULIAppApi
{
	// URL of the Application (used for the login)
	protected $app_url;

	// Url of the API
	protected $api_url;

	// the session ID returned by the Application
	// encrypted with MCrypt
	protected $session_key;

	protected $remember_cookie;

	public $message;
	public $code;

	function __construct()
	{
		global $uli_app_config;

		$this->app_url = $uli_app_config['app_url'];
		
		$this->api_url = $uli_app_config['api_url'];

		if (isset($_COOKIE['uli_ecommerce_app'])) {

			$this->session_key = $_COOKIE['uli_ecommerce_app'];
		}
		// check for remember cookie
		else {
			foreach ($_COOKIE as $name => $value) {

				if (strpos($name, 'remember_') !== false) {
					$this->remember_cookie = array(
						'name' => $name,
						'value' => $value,
					);
				}
			}
		}

		
	}

	/**
	 * Set the url of the API
	 * @param string $url
	 * return void
	 */
	public function set_api_url($url)
	{
		$this->api_url = $url;
	}

	/**
	 * Set the url of the Application
	 * @param string $url
	 * return void
	 */
	public function set_app_url($url)
	{
		$this->app_url = $url;
	}


	/**
	 * Application Request
	 * Login the user to the application
	 * @param array $data
	 * @return array/boolean
	 */
	public function loginUser($data)
	{

		$remote_url = $this->app_url . "remote/login";

		// -------------------------------------------

		$result = wp_remote_post($remote_url, array(

			'sslverify' => false,
			'body' => $data,

		));

		if (! is_array($result)) return false;

		$content = json_decode($result['body'], true);

		if( $result['response']['code'] == 202 && isset($content['session']['key']) && isset($content['session']['value']) )
		{
			return $content;
		}

		$this->message = $GLOBALS['uli_app_labels']['login_error'];
		
		return false;
	}

	/**
	 * API Request
	 * Create an account
	 * @param array $data('username', 'fname', 'lname', 'password')
	 * @return User information
	 */
	public function registerUser($data)
	{

		$remote_url = $this->api_url . "users/create/";

		// -------------------------------------------

		$result = wp_remote_post($remote_url, array(

			'sslverify' => false,
			'body' => $data,

		));

		if( ! is_array($result) ) return false;

		

		// SUCCESS
		if( $result['response']['code'] == 201 )
		{
			$return = json_decode($result['body'], true);

			return $return['WEBWebUserLoginResult'];
		}
		// EMAIL ALREADY EXISTS
		else if( $result['response']['code'] == 401 )
		{
			$this->message = $GLOBALS['uli_app_labels']['email_exists'];
			$this->code = 401;
			return false;
		}
		
		// ERROR
		$this->message = $GLOBALS['uli_app_labels']['server_error'];
		$this->code = $result['response']['code'];
		return false;	
	}

	/**
	 * Application Request
	 * Check if the user is logged in the application
	 * @return array
	 */
	public function getUser()
	{
		$remote_url = $this->app_url . "remote/login";

		// -------------------------------------------

		$cookies = array();
		if($this->session_key)
		{
			$cookies[] = new WP_Http_Cookie( array( 'name' =>'uli_ecommerce_app', 'value' => $this->session_key ) );
		}
		else if($this->remember_cookie)
		{
			$cookies[] = new WP_Http_Cookie( array( 'name' =>$this->remember_cookie['name'], 'value' => $this->remember_cookie['value'] ) );
		}
		else
		{
			return false;
		}

		$result = wp_remote_get($remote_url, array(

			'sslverify' => false,
			'cookies' => $cookies,

		));

		if( ! is_array($result) ) return false;

		if( $result['response']['code'] == 202 )
		{
		
			return json_decode($result['body'], true);

		}
		
		return false;
	}

	/**
	 * API Request
	 * Check if the email is already in the system
	 * @param string $email
	 * @return boolean
	 */
	public function checkEmail($email)
	{

		$remote_url = $this->api_url . "users/search?email=".$email;

		// -------------------------------------------

		$result = wp_remote_get($remote_url, array(

			'sslverify' => false

		));

		if( ! is_array($result) ) return false;

		if($result['response']['code'] == 200 && json_decode($result['body']) !== false)
		{
			return true;
		}

		return false;
	}

	/**
	 * API Request
	 * Update the user preferences in NF
	 * @param string $topic
	 * @param string $user_id
	 * @return the user info
	 */
	public function signUpUser($topic = null, $user_id = null)
	{

		if( ! $topic ) return false;

		$remote_url = $this->api_url . "users/preference/".$user_id;

		// -------------------------------------------


		// we need to get all the preferenes linked to the user
		$user_preferences = $this->getUserPreferences($user_id);
		$post_user_preferences = array();


		foreach ($user_preferences as $key => $obj)
		{
			$post_user_preferences[] = $obj['emo_key'];
		}




		$post_data = array(

			'preferences' => $post_user_preferences,
			'selected_preferences' => array($user_preferences[$topic]['emo_key']),
		);

		$result = wp_remote_post($remote_url, array(

			'sslverify' => false,
			'body' => $post_data,

		));


		if( ! is_array($result) ) return false;


		$content = json_decode($result['body'], true);


		if( $result['response']['code'] == 202 )
		{
			
			return 'success';

		}
		
		$this->message = $GLOBALS['uli_app_labels']['server_error'];
		$this->code = $result['response']['code'];
		return false;
	}

	/**
	 * API request
	 * Check if the user has already signed up for the topic
	 * @param string @user_id
	 * @param string $key
	 * @return boolean
	 */
	public function checkPreference($user_id, $key)
	{

		$remote_url = $this->api_url . "users/preference/".$user_id;

		// -------------------------------------------

		$result = wp_remote_get($remote_url, array(

			'sslverify' => false

		));

		if( ! is_array($result) ) return false;

		if($result['response']['code'] != 200)
		{
			return false;
		}

		$content = json_decode($result['body'], true);

		foreach ($content as $preference)
		{

			if($key == $preference['emo_mtp_key'] && $preference['emo_optout_flag'] == 0)
			{
				return true;
			}
		
		}

		return false;
	}

	/**
	 * API Request
	 * Get all the preferences from NF
	 * @return array
	 */
	public function getAllPreferences()
	{

		$remote_url = $this->api_url . "lookups/mailpreference/";

		// -------------------------------------------

		$result = wp_remote_get($remote_url, array(

			'sslverify' => false,

		));

		if( ! is_array($result) ) return false;

		if($result['response']['code'] != 200)
		{
			return false;
		}

		$content = json_decode($result['body'], true);

		return $content['email'];
	}


	/**
	 * API Request
	 * Get all the preferences associated to the user
	 * @param string $user_id
	 * @return array
	 */
	public function getUserPreferences($user_id)
	{

		$remote_url = $this->api_url . "users/preference/".$user_id;

		// -------------------------------------------

		$result = wp_remote_get($remote_url, array(

			'sslverify' => false,

		));

		if( ! is_array($result) ) return false;

		if($result['response']['code'] != 200)
		{
			return false;
		}

		$content = json_decode($result['body'], true);

		$return = array();

		foreach ($content as $object)
		{
			$return[$object['emo_mtp_key']] = $object;
		}

		return $return;
	}

	/**
	 * API Request
	 * SAVE all the preferences associated to the user
	 * @param string $user_id
	 * @param array $preferences
	 * @param array $selected preferences
	 * @return boolean
	 */
	public function saveUserPreferences($user_id, $preferences, $selected)
	{
		
		$remote_url = $this->api_url . "users/preference/";

		$result = wp_remote_post($remote_url, array(

			'sslverify' => false,
			'body' => array(
				'ind_cst_key' => $user_id,
				'update_preferences' => $preferences,
				'selected_preferences' => $selected,
				'referral' => 'quicksignup',
			),

		));

		if( is_array($result) && $result['response']['code'] == 202)
		{
			return true;
		}


		$this->message = $GLOBALS['uli_app_labels']['server_error'];
		$this->code = $result['response']['code'];
		return false;
	}
}
?>