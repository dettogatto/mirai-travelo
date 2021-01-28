<?php

if ( ! function_exists( 'mt_update_db' ) ) {
  function mt_update_db() {
    global $wpdb;

    $wpdb->query( "ALTER TABLE " . TRAV_ACCOMMODATION_VACANCIES_TABLE . " ADD treatment_id bigint(20) unsigned DEFAULT NULL" );
  }
}

add_action( "after_switch_theme", "mt_update_db", 100 );
