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
