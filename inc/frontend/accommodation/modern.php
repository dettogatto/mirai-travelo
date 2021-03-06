<?php


if ( ! function_exists( 'mirai_checkdays_bits_to_array' ) ) {
  function mirai_checkdays_bits_to_array($bits){
    $weekdays = [
      __("Monday", "trav"),
      __("Tuesday", "trav"),
      __("Wednesday", "trav"),
      __("Thursday", "trav"),
      __("Friday", "trav"),
      __("Saturday", "trav"),
      __("Sunday", "trav")
    ];
    $result = [];
    for($i = 0; $i < strlen($bits); $i++){
      if($bits[$i] === "1"){
        $result[] = $weekdays[$i];
      }
    }
    return $result;
  }
}

// Builds the room detail HTML
if ( ! function_exists( 'trav_modern_acc_get_room_detail_html' ) ) {
	function trav_modern_acc_get_room_detail_html( $room_type_id, $type = 'all', $room_price = 0, $number_of_days = 0, $rooms = 0, $info = []) { // available type - all,available,not_available,not_match
		$room_type_id = trav_room_clang_id( $room_type_id );

		$gallery_imgs = get_post_meta( $room_type_id, 'trav_gallery_imgs' );
		$max_adults = get_post_meta( $room_type_id, 'trav_room_max_adults', true );
		$max_kids = get_post_meta( $room_type_id, 'trav_room_max_kids', true );
		$facilities = wp_get_post_terms( $room_type_id, 'amenity' );
		$facility_names = array();
		foreach ( $facilities as $facility ) {
			$facility_names[] = $facility->name;
		}
		?>
		<div class="single-travel-item-wrap">
			<div class="single-travel-item detail-list-view-room">
				<div class="featured-imgs">
					<?php foreach ( $gallery_imgs as $gallery_img ) {
						echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
					} ?>
				</div>

				<div class="package-item-info">
					<h3 class="package-item-name"><?php echo get_the_title( $room_type_id ); ?></h3>
					<div class="guest-max-nums">
            <span class="guest-val"><?php echo esc_html__( 'Max Guest', 'trav' ); ?>: <span class="val"><?php echo esc_html( $max_adults ); ?></span></span>
            <span class="guest-val"><?php echo esc_html__( 'Max Kids', 'trav' );?>: <span class="val"><?php echo esc_html( $max_kids ); ?></span></span>
            <?php if($info["minimum_stay"]) : ?>
              <span class="guest-val <?php if(!$info["minimum_stay_valid"]) echo("error"); ?>"><?php echo esc_html__( 'Min Nights', 'trav' );?>: <span class="val"><?php echo esc_html( $info["minimum_stay"] ); ?></span></span>
            <?php endif; ?>
            <?php if($info["maximum_stay"]) : ?>
              <span class="guest-val <?php if(!$info["maximum_stay_valid"]) echo("error"); ?>"><?php echo esc_html__( 'Max Nights', 'trav' );?>: <span class="val"><?php echo esc_html( $info["maximum_stay"] ); ?></span></span>
            <?php endif; ?>
          </div>
          <div class="guest-max-nums">
            <?php if($info["checkin_days"] && $info["checkin_days"] != "1111111") : ?>
              <span class="guest-val <?php if(!$info["checkin_day_valid"]) echo("error"); ?>"><?php echo esc_html__( 'Checkin on', 'trav' );?>: <span class="val"><?php echo(implode(", ", mirai_checkdays_bits_to_array($info['checkin_days']))); ?></span></span>
            <?php endif; ?>
            <?php if($info["checkout_days"] && $info["checkout_days"] != "1111111") : ?>
              <span class="guest-val <?php if(!$info["checkout_day_valid"]) echo("error"); ?>"><?php echo esc_html__( 'Checkout on', 'trav' );?>: <span class="val"><?php echo(implode(", ", mirai_checkdays_bits_to_array($info['checkout_days']))); ?></span></span>
            <?php endif; ?>
          </div>
					<p class="description"><?php echo implode( ', ', $facility_names ); ?></p>
					<div class="info-bottom-part">
            <div class="price-field"><?php echo trav_get_price_field( $room_price ); ?> <span>/ <?php echo esc_html( $number_of_days ); ?> <?php echo esc_html( _n( 'Night', 'Nights', $number_of_days, 'trav' ) ); ?></span></div>
            <?php if($info['valid']) : ?>
              <button class="view-detail border-btn-third btn-book-now" data-room-type-id="<?php echo esc_attr( $room_type_id ); ?>"><?php echo esc_html__( 'Book Room', 'trav' ); ?></button>
            <?php else : ?>
              <div class="btn-book-now-disabled">
                <?php echo esc_html__( 'Invalid Dates', 'trav' ); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
			</div>
		</div>
		<?php
	}
}











// Ajax function to get availabilities result
if ( ! function_exists( 'trav_modern_ajax_acc_get_available_rooms' ) ) {
	function trav_modern_ajax_acc_get_available_rooms() {
		//validation and initiate variables
		$result_json = array(
			'success'   => 0,
			'result'    => ''
		);

		if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['accommodation_id'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'post-' . sanitize_text_field( $_POST['accommodation_id'] ) ) ) {
			$result_json['success'] = 0;
			$result_json['result'] = __( 'Sorry, your nonce did not verify.', 'trav' );

			wp_send_json( $result_json );
		}

		$rooms = ( isset( $_POST['rooms'] ) && is_numeric( $_POST['rooms'] ) ) ? sanitize_text_field( $_POST['rooms'] ) : 1;
		$adults = ( isset( $_POST['adults'] ) && is_numeric( $_POST['adults'] ) ) ? sanitize_text_field( $_POST['adults'] ) : 1;
		$kids = ( isset( $_POST['kids'] ) && is_numeric( $_POST['kids'] ) ) ? sanitize_text_field( $_POST['kids'] ) : 0;
		$child_ages = isset( $_POST['child_ages'] ) ? $_POST['child_ages'] : '';

		if ( isset( $_POST['accommodation_id'] ) && isset( $_POST['date_from'] ) && trav_strtotime( $_POST['date_from'] ) && isset( $_POST['date_to'] ) && trav_strtotime( $_POST['date_to'] ) && ( ( time()-(60*60*24) ) < trav_strtotime( $_POST['date_from'] ) ) ) {
			$acc_id = (int) $_POST['accommodation_id'];
			$except_booking_no = 0;
			$pin_code = 0;

			if ( isset( $_POST['edit_booking_no'] ) ) {
				$except_booking_no = sanitize_text_field( $_POST['edit_booking_no'] );
			}

			if ( isset( $_POST['pin_code'] ) ) {
				$pin_code = sanitize_text_field( $_POST['pin_code'] );
			}

			$return_value = trav_acc_get_available_rooms( $acc_id, $_POST['date_from'], $_POST['date_to'], $rooms, $adults, $kids, $child_ages, $except_booking_no, $pin_code );
      // echo("<pre>");
      // var_dump($return_value);
      // echo("</pre>");
      // die();

			if ( ! empty ( $return_value ) && is_array( $return_value ) ) {

				$number_of_days = count( $return_value['check_dates'] );

				ob_start();
				$available_room_type_ids = $return_value['bookable_room_type_ids'];
				?>
				<h3 class="section-inner-title"><?php echo esc_html__( 'Available Rooms', 'trav' ); ?></h3>

				<?php
				if ( empty( $available_room_type_ids ) ) {
					?>

					<div class="description-part">
						<p class="description"><?php echo esc_html__( 'No room found on your desired dates.', 'trav' ); ?></p>
						<a href="#check_availability_form" class="search-edit-btn"><i class="far fa-edit"></i> <?php echo esc_html__( 'Change Details', 'trav' );?></a>
					</div>

					<?php
				} else {
					?>
					<div class="description-part">
						<p class="description"><?php echo count( $available_room_type_ids ); ?> <?php echo esc_html( _n( 'room found on your desired dates.', 'rooms found on your desired dates.', count( $available_room_type_ids ), 'trav' ) ); ?></p>
						<a href="#check_availability_form" class="search-edit-btn"><i class="far fa-edit"></i> <?php echo esc_html__( 'Change Details', 'trav' );?></a>
					</div>
					<div class="available-travel-package-wrap">
						<?php
						foreach ( $available_room_type_ids as $room_type_id ) {
							$room_price = 0;
              $info = $return_value["additional_info"][$room_type_id];
              $info["prices"] = NULL;


							foreach ( $return_value['check_dates'] as $check_date ) {
								$room_price += (float) $return_value['prices'][ $room_type_id ][ $check_date ]['total'];
                $info["prices"] = $return_value['prices'][ $room_type_id ][ $check_date ];
							}


							trav_modern_acc_get_room_detail_html( $room_type_id, 'available', $room_price, $number_of_days, $rooms, $info );
						}
						?>
					</div>
					<?php
				}

				$output = ob_get_contents();
				ob_end_clean();

				$result_json['success'] = 1;
				$result_json['result'] = $output;
			} else {
				$result_json['success'] = 1;
				$result_json['result'] = $return_value;
			}
		} else {
			$result_json['success'] = 0;
			$result_json['result'] = __( 'Invalid input data', 'trav' );
		}

		wp_send_json( $result_json );
	}
}
