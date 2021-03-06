<?php

/*
 * Return matched accs to given data. It is used for check availability function & price calculation
 */
if ( ! function_exists( 'trav_acc_get_available_rooms' ) ) {
	function trav_acc_get_available_rooms( $acc_id, $from_date, $to_date, $rooms=1, $adults=1, $kids, $child_ages, $except_booking_no=0, $pin_code=0, $room_type_id=NULL) {

		// validation
		$acc_id = trav_acc_org_id( $acc_id );
		$minimum_stay = get_post_meta( $acc_id, 'trav_accommodation_minimum_stay', true );
		$minimum_stay = is_numeric($minimum_stay)?$minimum_stay:0;
		if ( ! trav_strtotime( $from_date ) || ! trav_strtotime( $to_date ) || ( trav_strtotime( $from_date .' + ' . $minimum_stay . ' days' ) > trav_strtotime( $to_date) ) || ( ( time()-(60*60*24) ) > trav_strtotime( $from_date ) ) ) {
			return __( 'Invalid date. Please check your booking date again.', 'trav' ); //invalid data
		}

		// initiate variables
		global $wpdb;

		if ( ! is_array($child_ages) ) $child_ages = unserialize($child_ages);

		$sql = "SELECT DISTINCT pm0.post_id FROM " . $wpdb->postmeta . " as pm0 INNER JOIN " . $wpdb->posts . " AS room ON (pm0.post_id = room.ID) AND (room.post_status = 'publish') AND (room.post_type = 'room_type') WHERE meta_key = 'trav_room_accommodation' AND meta_value = " . esc_sql( $acc_id );
		$all_room_ids = $wpdb->get_col( $sql );
		if ( empty( $all_room_ids ) ){
			return __( 'No Rooms', 'trav' ); //invalid data
		}

		$avg_adults = ceil( $adults / $rooms );
		$avg_kids = ceil( $kids / $rooms );


		// get available accommodation room_type_id based on max_adults and max_kids
		$sql = "SELECT DISTINCT pm0.post_id AS room_type_id FROM (SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key = 'trav_room_accommodation' AND meta_value = " . esc_sql( $acc_id ) . " ) AS pm0
				INNER JOIN " . $wpdb->posts . " AS room ON (pm0.post_id = room.ID) AND (room.post_status = 'publish') AND (room.post_type = 'room_type')
				INNER JOIN " . $wpdb->postmeta . " AS pm1 ON (pm0.post_id = pm1.post_id) AND (pm1.meta_key = 'trav_room_max_adults')
				LEFT JOIN " . $wpdb->postmeta . " AS pm2 ON (pm0.post_id = pm2.post_id) AND (pm2.meta_key = 'trav_room_max_kids')
				WHERE ( pm1.meta_value >= " . esc_sql( $avg_adults ) . " ) AND ( pm1.meta_value + IFNULL(pm2.meta_value,0) >= " . esc_sql( $avg_adults + $avg_kids ) . " )";

    // If room_type_id is provided use that one, else search them up
		$matched_room_ids = $room_type_id ? [$room_type_id] : $wpdb->get_col( $sql ); //object (room_type_id)

		if ( empty( $matched_room_ids ) ){
			$return_value = array(
				'all_room_type_ids' => $all_room_ids,
				'matched_room_type_ids' => array(),
				'bookable_room_type_ids' => array(),
				'check_dates' => array(),
				'prices' => array()
			);
			return $return_value;
		}

		// get available accommodation room_type_id and price based on date
		// initiate variables
		$check_dates = array();
		$price_data = array();
		$total_price_data = array();

		// prepare date for loop
		$from_date_obj = new DateTime( '@' . trav_strtotime( $from_date ) );
		$to_date_obj = new DateTime( '@' . trav_strtotime( $to_date ) );
		// $to_date_obj = $to_date_obj->modify( '+1 day' );
		$date_interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);

		$acc_id = esc_sql( $acc_id );
		$rooms = esc_sql( $rooms );
		$adults = esc_sql( $adults );
		$kids = esc_sql( $kids );
		$child_ages = esc_sql( $child_ages );
		$except_booking_no = esc_sql( $except_booking_no );
		$pin_code = esc_sql( $pin_code );

		$bookable_room_ids = $matched_room_ids;


    $vacancy_room_info = [];

		foreach ( $period as $dt ) {
      $bookable_room_ids = $matched_room_ids;

			$check_date = esc_sql( $dt->format( "Y-m-d" ) );
			$check_dates[] = $check_date;

      // MT: Look for vacancies that fit maximum_guests minimum_guests maximum_kids and minimum_kids
      $minmax_guests_sql = '';
      $minmax_guests_sql .= ' AND (minimum_guests IS NULL OR minimum_guests <= ' . esc_sql( $avg_adults + $avg_kids ) . ' )';
      $minmax_guests_sql .= ' AND (maximum_guests IS NULL OR maximum_guests >= ' . esc_sql( $avg_adults + $avg_kids ) . ' )';
      $minmax_guests_sql .= ' AND (minimum_kids IS NULL OR minimum_kids <= ' . esc_sql( $avg_kids ) . ' )';
      $minmax_guests_sql .= ' AND (maximum_kids IS NULL OR maximum_kids >= ' . esc_sql( $avg_kids ) . ' )';

			$sql = "SELECT vacancies.room_type_id, vacancies.price_per_room , vacancies.price_per_person, vacancies.child_price, vacancies.checkin_days, vacancies.checkout_days, vacancies.minimum_stay, vacancies.maximum_stay, vacancies.advanced_price
					FROM (SELECT room_type_id, rooms, price_per_room, price_per_person, child_price, checkin_days, checkout_days, minimum_stay, maximum_stay, advanced_price
							FROM " . TRAV_ACCOMMODATION_VACANCIES_TABLE . "
							WHERE 1=1 AND accommodation_id='" . $acc_id . "' AND room_type_id IN (" . implode( ',', $bookable_room_ids ) . ") AND date_from <= '" . $check_date . "'  AND date_to >= '" . $check_date . "' ".$minmax_guests_sql." ) AS vacancies
					LEFT JOIN (SELECT room_type_id, SUM(rooms) AS rooms
							FROM " . TRAV_ACCOMMODATION_BOOKINGS_TABLE . "
							WHERE 1=1 AND status!='0' AND accommodation_id='" . $acc_id . "' AND date_to > '" . $check_date . "'  AND date_from <= '" . $check_date . "'" . ( ( empty( $except_booking_no ) || empty( $pin_code ) )?"":( " AND NOT ( booking_no = '" . $except_booking_no . "' AND pin_code = '" . $pin_code . "' )" ) ) . " GROUP BY room_type_id
					) AS bookings ON vacancies.room_type_id = bookings.room_type_id
					WHERE vacancies.rooms - IFNULL(bookings.rooms,0) >= " . $rooms . ";";

			$results = $wpdb->get_results( $sql ); // object (room_type_id, price_per_room, price_per_person, child_price)

			if ( empty( $results ) ) { //if no available rooms on selected date
				$return_value = array(
					'all_room_type_ids' => $all_room_ids,
					'matched_room_type_ids' => $matched_room_ids,
					'bookable_room_type_ids' => array(),
					'check_dates' => array(),
					'prices' => array(),
				);
				return $return_value;
			}

			$day_available_room_type_ids = array();

			foreach ( $results as $result ) {
				$day_available_room_type_ids[] = $result->room_type_id;
				$price_per_room = (float) $result->price_per_room;
				$price_per_person = (float) $result->price_per_person;
				$child_price_data = unserialize( $result->child_price );
				$advanced_price_data = unserialize( $result->advanced_price );
				$checkin_days_bits = strlen($result->checkin_days) === 7 ? $result->checkin_days : "1111111";
				$checkout_days_bits = strlen($result->checkout_days) === 7 ? $result->checkout_days : "1111111";
				$minimum_stay = (int) $result->minimum_stay;
				$maximum_stay = (int) $result->maximum_stay;
        if(!$vacancy_room_info[$result->room_type_id]){
          $vacancy_room_info[$result->room_type_id]["checkin_days"] = $checkin_days_bits;
          $vacancy_room_info[$result->room_type_id]["checkout_days"] = $checkout_days_bits;
          $vacancy_room_info[$result->room_type_id]["minimum_stay"] = $minimum_stay;
          $vacancy_room_info[$result->room_type_id]["maximum_stay"] = $maximum_stay;
        } else {
          $vacancy_room_info[$result->room_type_id]["checkout_days"] = $checkout_days_bits;
        }



        // TODO calculate child price
				$child_price = array();
				$total_child_price = 0;



				if ( ( $kids > 0 ) && ( ! empty( $child_price_data ) ) && ( ! empty( $child_ages ) ) ) {

          // Calculate price for ages in ascending order

          sort($child_ages);

          $total_asc = 0;
          $detail_asc = array();
          $i = 0;
          foreach($child_ages as $child_age){
            $is_child = false;
            $cp_data = is_array($child_price_data[$i]) ? $child_price_data[$i] : $child_price_data[count($child_price_data) - 1];
            foreach ( $cp_data as $age_price_trio ) {
              if ( is_array( $age_price_trio ) && ( count( $age_price_trio ) >= 3 ) && ( (int) $child_age >= (int) $age_price_trio[0] ) && ( (int) $child_age <= (int) $age_price_trio[1] ) ) {
                $is_child = true;
                $detail_asc[] = (float) $age_price_trio[2];
                $total_asc += (float) $age_price_trio[2];
                break;
              }
            }
            if( !$is_child ){
              $detail_asc[] = 1000;
              $total_asc += 1000;
            }
            $i++;
          }

          // Calculate price for ages in descending order

          rsort($child_ages);

          $total_desc = 0;
          $detail_desc = array();
          $i = 0;
          foreach($child_ages as $child_age){
            $is_child = false;
            $cp_data = is_array($child_price_data[$i]) ? $child_price_data[$i] : $child_price_data[count($child_price_data) - 1];
            foreach ( $cp_data as $age_price_trio ) {
              if ( is_array( $age_price_trio ) && ( count( $age_price_trio ) >= 3 ) && ( (int) $child_age >= (int) $age_price_trio[0] ) && ( (int) $child_age <= (int) $age_price_trio[1] ) ) {
                $is_child = true;
                $detail_desc[] = (float) $age_price_trio[2];
                $total_desc += (float) $age_price_trio[2];
                break;
              }
            }
            if( !$is_child ){
              $detail_desc[] = 1000;
              $total_desc += 1000;
            }
            $i++;
          }

          // Choose lowest price

          if($total_desc < $total_asc){
            $child_price = $detail_desc;
    				$total_child_price = $total_desc;
          } else {
            $child_price = $detail_asc;
    				$total_child_price = $total_asc;
          }

        }

        // Calculate adults price

        $adults_price = $price_per_person * $adults;
        $adults_detail = array();

        $advanced_price = $result->advanced_price;


        if( !empty($advanced_price_data) ){
          $adults_price = 0;
          for($i = 0; $i < $adults; $i++){
            $ap_data = $advanced_price_data[$i] ? $advanced_price_data[$i] : $advanced_price_data[count($advanced_price_data) - 1];
            $adults_detail[] = (float) $ap_data;
            $adults_price += (float) $ap_data;
          }
        }

        // Calculate total price

				$total_price = $price_per_room * $rooms + $adults_price + $total_child_price;
				$price_data[ $result->room_type_id ][ $check_date ] = array(
					'ppr' => $price_per_room,
					'ppp' => $price_per_person,
          'ap' => $adults_detail,
					'cp' => $child_price,
					'total' => $total_price
				);
			}

			$bookable_room_ids = $day_available_room_type_ids;
		}

		//$number_of_days = count( $check_dates );
		$return_value = array(
			'all_room_type_ids' => $all_room_ids,
			'matched_room_type_ids' => $matched_room_ids,
			'bookable_room_type_ids' => $bookable_room_ids,
			'check_dates' => $check_dates,
			'prices' => $price_data,
      'additional_info' => $vacancy_room_info
		);

    foreach ($return_value['additional_info'] as $room => $info) {
      $from_weekday = ( $from_date_obj->format("w") + 6 ) % 7; // shift to 0=monday 6=sunday
      $to_weekday = ( $to_date_obj->format("w") + 6 ) % 7;
      $return_value['additional_info'][$room]['checkin_day_valid'] = ($info['checkin_days'][$from_weekday] === "1");
      $return_value['additional_info'][$room]['checkout_day_valid'] = ($info['checkout_days'][$to_weekday] === "1");
      $return_value['additional_info'][$room]['minimum_stay_valid'] = (!$info['minimum_stay'] || $info['minimum_stay'] <= count($return_value['check_dates']));
      $return_value['additional_info'][$room]['maximum_stay_valid'] = (!$info['maximum_stay'] || $info['maximum_stay'] >= count($return_value['check_dates']));
      $return_value['additional_info'][$room]['valid'] = (
        $return_value['additional_info'][$room]['checkin_day_valid'] &&
        $return_value['additional_info'][$room]['checkout_day_valid'] &&
        $return_value['additional_info'][$room]['minimum_stay_valid'] &&
        $return_value['additional_info'][$room]['maximum_stay_valid']
      );
    }

		return $return_value;
	}
}


/*
 * Calculate the price of selected accommodation room and return price array data
 */
if ( ! function_exists( 'trav_acc_get_room_price_data' ) ) {
  function trav_acc_get_room_price_data( $acc_id, $room_type_id, $from_date, $to_date, $rooms=1, $adults=1, $kids=0, $child_ages, $except_booking_no=0, $pin_code=0 ) {

    $acc_id = trav_acc_org_id( $acc_id );
		$room_type_id = trav_room_org_id( $room_type_id );

		//validation
		if ( ! is_array( $child_ages ) ){ $child_ages = unserialize($child_ages); }
		$room_accommodation_id = get_post_meta( $room_type_id, 'trav_room_accommodation', true );
		if ( $room_accommodation_id != $acc_id ) return false;
    if ( ( time()-( 60*60*24 ) ) > trav_strtotime( $from_date ) ) return false;

    $from_date_obj = new DateTime( '@' . trav_strtotime( $from_date ) );
		$to_date_obj = new DateTime( '@' . trav_strtotime( $to_date ) );
    $date_interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($from_date_obj, $date_interval, $to_date_obj);

    $check_dates = array();
    $prices = array();
    $total_price = 0;

    foreach ( $period as $dt ) {
			$check_date = esc_sql( $dt->format( "Y-m-d" ) );
      $next_day = esc_sql( $dt->modify('+1 day')->format( "Y-m-d" ) );
      $check_dates[] = $check_date;
      $available_room = trav_acc_get_available_rooms( $acc_id, $check_date, $next_day, $rooms, $adults, $kids, $child_ages, $except_booking_no, $pin_code, $room_type_id);
      $prices[$check_date] = $available_room["prices"][$room_type_id][$check_date];
      $total_price += $prices[$check_date]['total'];
    }

    $return_value = array(
			'check_dates' => $check_dates,
			'prices'      => $prices,
			'total_price' => $total_price
		);

		return $return_value;
  }
}
