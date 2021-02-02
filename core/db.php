<?php

if ( ! function_exists( 'mt_update_db' ) ) {
  function mt_update_db() {
    global $wpdb;

    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD treatment_id bigint(20) unsigned DEFAULT NULL" );
    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD checkin_days varchar(7) DEFAULT '1111111'" );
    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD checkout_days varchar(7) DEFAULT '1111111'" );
    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD minimum_stay tinyint(11) DEFAULT NULL" );
    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD maximum_stay tinyint(11) DEFAULT NULL" );
  }
}

add_action( "after_switch_theme", "mt_update_db", 100 );
