<?php

// --------------------------------------------
// THE URL OF THE ECOMMERCE APPLICATION
// FROM THIS URL WILL BE ASSIGNED OTHER URLS
// - logout
// - profile
// - password reset url
// --------------------------------------------
$uli_app_config['app_url'] = 'https://my-test.uli.org/';



// --------------------------------------------
// THE URL OF THE ULI API
// All the data is coming from NetForum via the API
// --------------------------------------------
$uli_app_config['api_url'] = 'https://api-test.uli.org/1.2/';



// --------------------------------------------
// THE MAIN DOMAIN OF THE ENVIRONMENT
// the login cookies are set with that domain
// --------------------------------------------
$uli_app_config['cookie_main_domain'] = '.uli.org';

// --------------------------------------------

$uli_app_config['logout_url'] = $uli_app_config['app_url'] . 'remote/login/logout/';
$uli_app_config['profile_url'] = $uli_app_config['app_url'] . 'profile/';
$uli_app_config['password_reset_url'] = $uli_app_config['app_url'] . 'login/?tab=3';


// --------------------------------------------
// --------------------------------------------
// --------------------------------------------


$uli_app_labels['login_error'] = 'Your email or password do not match our records';
$uli_app_labels['email_exists'] = 'The email already exists in our records';
$uli_app_labels['server_error'] = 'Request Error. Please, try again later';

?>