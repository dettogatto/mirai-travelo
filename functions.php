<?php

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/dettogatto/mirai-travelo/',
  __FILE__,
  'mirai-travelo'
);

//Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('your-token-here');

//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');


//constants
define( 'MT_CORE_FOLDER', __DIR__ . '/core' );
define( 'MT_INC_FOLDER', __DIR__ . '/inc' );
define( 'MT_TEMPLATE_DIRECTORY_URI', get_stylesheet_directory_uri() );


require_once( MT_CORE_FOLDER . '/db.php' );
require_once( MT_CORE_FOLDER . '/treatment.php' );
require_once( MT_INC_FOLDER . '/admin/accommodation/vacancies-admin-panel.php' );


// Enqueue child theme accommodation script (overwriting it)
add_action( 'wp_enqueue_scripts', function(){
  wp_enqueue_script( 'trav_script_accommodation', MT_TEMPLATE_DIRECTORY_URI . '/js/modern/accommodation.js', array( 'jquery' ), '', true );
  wp_localize_script( 'trav_script_accommodation', 'date_format', trav_get_date_format('js') );
});
