<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $login_url;

if ( have_posts() ) {
	while ( have_posts() ) : the_post();

		$acc_id = get_the_ID();
		$gallery_imgs = get_post_meta( $acc_id, 'trav_gallery_imgs' );
		$post_link = get_permalink( $acc_id );
		if ( empty( $gallery_imgs ) ) {
			$gallery_imgs = array();
		}

		$featured_img_id = get_post_thumbnail_id( $acc_id );
	    if ( $featured_img_id ) {
	        array_unshift( $gallery_imgs, $featured_img_id );
	    }

		$acc_meta['review'] = get_post_meta( $acc_id, 'review', true );
		$acc_review = ( ! empty( $acc_meta['review'] ) ) ? (float) $acc_meta['review'] : 0;
		$acc_review = round( $acc_review, 1 );
		$acc_meta['review_detail'] = get_post_meta( $acc_id, 'review_detail', true );
		$review_count = trav_modern_get_review_count( $acc_id );

		$acc_location = get_post_meta( $acc_id, 'trav_accommodation_address', true );
		$acc_avg_price = get_post_meta( $acc_id, 'trav_accommodation_avg_price', true );
		$acc_brief = get_post_meta( $acc_id, 'trav_accommodation_brief', true );
		$amenity_desc = get_post_meta( $acc_id, 'trav_accommodation_other_amenity_info', true );
		$acc_faqs = get_post_meta( $acc_id, 'trav_accommodation_modern_faq', true );
		$acc_accessibilities = get_post_meta( $acc_id, 'trav_accommodation_modern_accessibility', true );
		$acc_check_in = get_post_meta( $acc_id, 'trav_accommodation_check_in', true );
		$acc_check_out = get_post_meta( $acc_id, 'trav_accommodation_check_out', true );
		$acc_cancelation = get_post_meta( $acc_id, 'trav_accommodation_cancellation', true );
		$acc_bed_detail = get_post_meta( $acc_id, 'trav_accommodation_extra_beds_detail', true );
		$acc_pets = get_post_meta( $acc_id, 'trav_accommodation_pets', true );
		$acc_security_deposit = get_post_meta( $acc_id, 'trav_accommodation_security_deposit', true );
		$acc_card_accepted = get_post_meta( $acc_id, 'trav_accommodation_cards', true );
		$acc_other_policies = get_post_meta( $acc_id, 'trav_accommodation_other_policies', true );
		$acc_min_stay = get_post_meta( $acc_id, 'trav_accommodation_minimum_stay', true );
		$acc_logo = get_post_meta( $acc_id, 'trav_accommodation_logo', true );


		$facilities = wp_get_post_terms( $acc_id, 'amenity' );


/*		$room_types = get_posts( $args );
		$city = trav_acc_get_city( $acc_id );
		$country = trav_acc_get_country( $acc_id );

*/
		trav_update_user_recent_activity( $acc_id );

		?>

		<div class="main-content">
			<div class="single-detail-head">
				<div class="single-head-icons">
					<?php if ( is_user_logged_in() ) {
	                    $user_id = get_current_user_id();
	                    $wishlist = get_user_meta( $user_id, 'wishlist', true );
	                    if ( empty( $wishlist ) ) $wishlist = array();
	                    if ( ! in_array( trav_acc_org_id( $acc_id ), $wishlist) ) { ?>
	                        <a class="wishlist-ribbon btn-add-wishlist head-icon" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
	                    <?php } else { ?>
	                        <a class="wishlist-ribbon btn-remove-wishlist head-icon" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
	                    <?php } ?>
	                <?php } else { ?>
	                    <a href="<?php echo $login_url; ?>" class="wishlist-ribbon btn-add-wishlist"><i class="fas fa-heart"></i></a>
	                <?php } ?>

	                <a href="#" class="head-icon share-ribbon"><i class="fas fa-share-alt"></i></a>

	                <div class="post-share-buttons">
						<div class="travelo-social-button facebook-icon">
							<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $post_link ); ?>">
								<i class="fab fa-facebook-f"></i>
							</a>
						</div>

						<div class="travelo-social-button twitter-icon">
							<a href="https://twitter.com/share?url=<?php echo esc_attr( $post_link ); ?>">
								<i class="fab fa-twitter"></i>
							</a>
						</div>

						<div class="travelo-social-button instagram-icon">
							<a href="https://instagram.com/share?url=<?php echo esc_attr( $post_link ); ?>">
								<i class="fab fa-instagram"></i>
							</a>
						</div>

						<div class="travelo-social-button google-icon">
							<a href="https://plus.google.com/share?url=<?php echo esc_attr( $post_link ); ?>">
								<i class="fab fa-google-plus-g"></i>
							</a>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $gallery_imgs ) ) :  ?>
					<div class="single-featured-imgs">
						<div class="imgs-left-part">
							<div class="detail-img-wrap">
								<span class="featured-img-bg" style="background-image: url('<?php echo ( ! empty( $gallery_imgs[0] ) ) ? wp_get_attachment_image_url( $gallery_imgs[0], 'full' ) : ''; ?>');"></span>
							</div>
						</div>

						<?php if ( count( $gallery_imgs ) > 1 ) : ?>
							<div class="imgs-right-part" count="<?php echo count( $gallery_imgs ) - 1; ?>">
								<?php
								foreach ( $gallery_imgs as $key => $gallery_img ) {
									if ( $key == 0 ) {
										continue;
									}
									if ( $key >= 5 ) {
										break;
									}
									?>
									<div class="detail-img-wrap">
										<span class="featured-img-bg" style="background-image: url('<?php echo wp_get_attachment_image_url( $gallery_img, 'full' ); ?>');"></span>
									</div>
									<?php

								}
								?>
							</div>
						<?php endif; ?>
					</div>

					<div class="more-photos">
						<button type="button" class="more-photos-btn" data-toggle="modal" data-target="#single-featured-carousel"><?php echo esc_html__( 'More Photos', 'trav' ); ?> <i class="fas fa-chevron-right"></i></button>
					</div>

					<div class="modal fade" id="single-featured-carousel" role="dialog">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-body">
									<div class="featured-photo-carousel owl-carousel">
										<?php
										foreach ( $gallery_imgs as $key => $gallery_img ) {
											echo wp_get_attachment_image( $gallery_img, 'slider-gallery' );
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>

				<?php endif; ?>
			</div>

			<div class="single-detail-content">
				<div class="detail-content-inner container">
					<div class="row">
						<div class="main-content-area col-lg-8">
							<div class="content-head-part content-section">
								<span class="rating-review"><span class="value"><?php echo esc_html( $acc_review );?></span></span>

								<div class="review-rating">
									<div class="five-stars-container">
										<span class="five-stars" style="width: <?php echo $acc_review * 20; ?>%;"></span>
									</div>
									<span class="review-nums"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'reviews', 'trav' ); ?></span>
								</div>

								<h1 class="single-title"><?php the_title(); ?></h1>

								<?php if ( ! empty( $acc_location ) ) : ?>
									<div class="single-location">
										<i class="fas fa-map-marker-alt"></i>
										<?php echo esc_html( $acc_location ); ?>
									</div>
								<?php endif; ?>

								<div class="single-price">
									<?php if ( ! empty( $acc_avg_price ) ) : ?>
										<?php echo trav_get_price_field( $acc_avg_price ); ?>
										<span class="unit">/ <?php echo esc_html__( 'Per Night', 'trav' ); ?></span>
									<?php endif; ?>
								</div>

								<div class="single-description"><?php the_content(); ?></div>

							</div>

							<div class="content-amenities-part content-section">
								<h2 class="section-title"><?php echo esc_html__( 'Hotel Amenities', 'trav' ); ?></h2>

								<?php if ( ! empty( $amenity_desc ) ) : ?>
									<p><?php echo esc_html( $amenity_desc ); ?></p>
								<?php endif; ?>

								<div class="amenities-list">
									<?php foreach ( $facilities as $facility ) : ?>
										<?php $icon_class = get_term_meta( $facility->term_id, 'icon_class', true ); ?>
										<div class="single-amenity"><i class="<?php echo empty( $icon_class ) ? '' : $icon_class; ?>"></i> <?php echo esc_html( $facility->name ); ?></div>
									<?php endforeach; ?>
								</div>
							</div>

							<?php if ( ! empty( $acc_accessibilities ) ) : ?>
								<div class="content-accessibility-part content-section">
									<h2 class="section-title"><?php echo esc_html__( 'Accessibility', 'trav' ); ?></h2>

									<ul class="accessibility-list">
										<?php foreach ( $acc_accessibilities as $accessibility ) : ?>
											<li class="single-accessibility"><?php echo $accessibility; ?></li>
									<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>

							<div class="content-availability-part content-section">
								<h2 class="section-title"><?php echo esc_html__( 'Availability', 'trav' ); ?></h2>

								<div class="select-availability-form">
									<form id="check_availability_form" method="post" class="single-availability-form-wrap">
										<input type="hidden" name="accommodation_id" value="<?php echo esc_attr( $acc_id ); ?>">
										<input type="hidden" name="action" value="acc_get_available_rooms">
										<input type="hidden" name="date_from">
										<input type="hidden" name="date_to">

										<?php wp_nonce_field( 'post-' . $acc_id, '_wpnonce', false ); ?>

										<?php if ( isset( $_GET['edit_booking_no'] ) && ! empty( $_GET['edit_booking_no'] ) ) : ?>
											<input type="hidden" name="edit_booking_no" value="<?php echo esc_attr( $_GET['edit_booking_no'] ) ?>">
											<input type="hidden" name="pin_code" value="<?php echo esc_attr( $_GET['pin_code'] ) ?>">
										<?php endif; ?>

                    <div class="date-range-part">
                      <h3 class="section-inner-title"><?php echo esc_html__( 'Select Dates', 'trav' ); ?></h3>
                      <?php if(!empty( $acc_min_stay )) :  ?>
                        <p class="description"><?php echo ( empty( $acc_min_stay ) ) ? '1' : esc_html( $acc_min_stay ); ?> <?php echo esc_html__( 'night minimum stay.', 'trav' ); ?></p>
                      <?php endif; ?>
                      <div class="alert alert-error" style="display: none;"><span class="message"></span><span class="close"></span></div>
                      <input id="single-availability-dates" type="hidden" format="<?php echo trav_get_date_format('html'); ?>">
                      <div id="single-availability-dates-container" class="embedded-daterangepicker"></div>
                    </div>

										<div class="guest-selection-part">
											<h3 class="section-inner-title"><?php echo esc_html__( 'Who\'s Going?', 'trav' ); ?></h3>

											<div class="guest-counter-box">
												<div class="room-counter counter-box">
													<div class="counter-box-inner">
														<h4 class="counter-title"><?php echo esc_html__( 'Rooms', 'trav' ); ?></h4>
														<p class="counter-description"><?php echo esc_html__( 'Min 1 Room', 'trav' ); ?></p>

														<div class="counter-icon"><i class="travelo-room"></i></div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="rooms" class="count-value" min="1" value="1">
															<i class="fas fa-plus"></i>
														</div>
													</div>
												</div>

												<div class="adults-counter counter-box">
													<div class="counter-box-inner">
														<h4 class="counter-title"><?php echo esc_html__( 'Adults', 'trav' ); ?></h4>
														<p class="counter-description"><?php echo esc_html__( '17 Onward', 'trav' ); ?></p>

														<div class="counter-icon"><i class="travelo-adults"></i></div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="adults" class="count-value" min="1" value="1">
															<i class="fas fa-plus"></i>
														</div>
													</div>
												</div>

												<div class="children-counter counter-box children-age-field-container">
													<div class="counter-box-inner">
														<h4 class="counter-title"><?php echo esc_html__( 'Children', 'trav' ); ?></h4>
														<p class="counter-description"><?php echo esc_html__( 'Ages 2~12', 'trav' ); ?></p>

														<div class="counter-icon"><i class="travelo-children"></i></div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="kids" class="count-value" min="0" value="0">
															<i class="fas fa-plus"></i>
														</div>
													</div>
												</div>

                      </div>

                      <div class="guest-counter-box child-ages-box child-age-inputs-container">

                        <div class="children-counter counter-box single-child-age">
													<div class="counter-box-inner">
														<h4 class="counter-title"><?php echo esc_html__( 'Child age', 'trav' ); ?></h4>
														<p class="counter-description"><?php echo esc_html__( 'Age of child', 'trav' ); ?> #<span class="child-input-number">1</span></p>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="child_ages[]" class="count-value" min="2" value="2">
															<i class="fas fa-plus"></i>
														</div>
													</div>
												</div>


                      </div>



										</div>

										<div class="form-submit">
											<button type="submit" class="submit-btn"><?php echo esc_html__( 'Show Vacant Rooms', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></button>
										</div>
									</form>

									<div class="search-available-rooms room-list">

									</div>
								</div>

								<div id="reviews-result" class="reviews-result-section">
									<h2 class="section-title"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'Reviews', 'trav' ); ?></h2>

									<div class="review-rating-overview">
										<p class="rating-txt"><span class="rating-val"><?php echo esc_html( $acc_review );?>/5.0</span> <?php echo esc_html__( 'with an Overall Rating', 'trav' ); ?>: <span class="ribbon"><?php echo trav_get_review_based_text( $acc_review ); ?></span></p>

										<a href="#hotel-write-review" class="review-write-link border-btn-third"><?php echo esc_html__( 'Write a review', 'trav' ); ?> <i class="fas fa-chevron-down"></i></a>
									</div>

									<div class="individual-rating-part">

										<?php
											$review_factors = array(
													'cln' => esc_html__( 'Cleanliness', 'trav' ),
													'cft' => esc_html__( 'Comfort', 'trav' ),
													'loc' => esc_html__( 'Location', 'trav' ),
													'fac' => esc_html__( 'Facilities', 'trav' ),
													'stf' => esc_html__( 'Staff', 'trav' ),
													'vfm' => esc_html__( 'Value for money', 'trav' ),
												);
											$i = 0;
											$review_detail = array( 0, 0, 0, 0, 0, 0 );
											if ( ! empty( $acc_meta['review_detail'] ) ) {
												$review_detail = is_array( $acc_meta['review_detail'] ) ? $acc_meta['review_detail'] : unserialize( $acc_meta['review_detail'] );
											}

											foreach ( $review_factors as $factor => $label ) {
												?>
												<div class="special-rating">
													<span class="title"><?php echo esc_html( $label ); ?></span>
													<div class="five-stars-container">
														<span class="five-stars" style="width: <?php echo $review_detail[$i] * 20; ?>%;"></span>
													</div>
												</div>
												<?php
												$i++;
											}
										?>

									</div>
								</div>
							</div>

							<?php
								$per_page = 10;
								$reviews = trav_get_reviews( $acc_id, 0, $per_page );

								if ( ! empty( $reviews ) ) {
									?>
									<div class="content-comment-part content-section">
										<ul class="comments">
											<?php
												$review_count = trav_modern_get_review_html( $acc_id, 0, $per_page);
											?>
										</ul>

										<?php if ( $review_count >= $per_page ) { ?>
		                                    <a href="#" class="load-more-comment border-btn-primary more-acc-review"><?php echo __( 'Load More', 'trav' ) ?> <i class="fas fa-angle-double-down"></i></a>
		                                <?php } ?>
									</div>
									<?php
								}
							?>

							<div id="hotel-write-review" class="content-write-review content-section">
								<a href="#reviews-result" class="review-write-link border-btn-third"><?php echo esc_html__( 'Write a review', 'trav' ); ?> <i class="fas fa-chevron-up"></i></a>

								<?php echo trav_modern_get_write_review_form_html( $acc_id ); ?>

							</div>

							<?php if ( ! empty( $acc_faqs ) ) : ?>
								<div class="content-faq-policy-part content-section">
									<h2 class="section-title"><?php echo esc_html__( 'Frequently Asked Questions', 'trav' ); ?></h2>

									<div id="detail-faq-accordion" class="detail-accordion-wrap">

										<?php foreach ( $acc_faqs as $key => $faq ) : ?>
											<div class="card">
												<div class="card-header">
													<a class="card-link collapsed" data-toggle="collapse" href="#faq-collapse<?php echo $key; ?>"><?php echo $faq['question']; ?></a>
												</div>
												<div id="faq-collapse<?php echo $key; ?>" class="collapse" data-parent="#detail-faq-accordion">
													<div class="card-body">
														<?php echo $faq['answer']; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>

									<h2 class="section-title policy-section-title"><?php echo esc_html__( 'Hotel Policies', 'trav' ); ?></h2>

									<div class="detail-policy-description">

										<?php if ( ! empty( $acc_check_in ) || ! empty( $acc_check_out ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Timings', 'trav' ); ?>:</h4>
											<p class="description">
												<?php if ( ! empty( $acc_check_in ) ) : ?>
													<span class="check-in"><?php echo esc_html__( 'Check-in', 'trav' ); ?>:<?php echo esc_html( $acc_check_in ); ?></span>
												<?php endif; ?>

												<?php if ( ! empty( $acc_check_in ) ) : ?>
													<span class="check-out"><?php echo esc_html__( 'Check-out', 'trav' ); ?>:<?php echo esc_html( $acc_check_out ); ?></span>
												<?php endif; ?>
											</p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_cancelation ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Cancelation/prepayment', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_cancelation ); ?></p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_security_deposit ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Security Deposit Amount', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_security_deposit ); ?>%</p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_bed_detail ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Children and Extra Beds', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_bed_detail ); ?></p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_pets ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Pets', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_pets ); ?></p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_card_accepted ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Acceptable Cards', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_card_accepted ); ?></p>
										<?php endif; ?>

										<?php if ( ! empty( $acc_other_policies ) ) : ?>
											<h4 class="policy-title"><?php echo esc_html__( 'Other Policies', 'trav' ); ?>:</h4>
											<p class="description"><?php echo esc_html( $acc_other_policies ); ?></p>
										<?php endif; ?>


									</div>
								</div>
							<?php endif; ?>
						</div>

						<aside class="sidebar-content-area col-lg-4 sidebar">
							<div class="detail-sidebar-form">
								<form id="vacant-main-form" action="" class="vacant-main-form-inner">


									<?php if ( ! empty( $acc_logo ) ) { ?>
										<div class="detail-logo">
											<img src="<?php echo wp_get_attachment_url( $acc_logo );?>" alt="<?php echo get_the_title(); ?>">
										</div>
									<?php } ?>

									<div class="form-input-area">
										<div class="check-in-out-wrap">
											<div class="label-section">
												<label class="form-label"><?php echo esc_html__( 'Check In', 'trav' ); ?></label>
												<label class="form-label"><?php echo esc_html__( 'Check Out', 'trav' ); ?></label>
											</div>

											<div class="field-sections">
												<div class="field-section date-in">
													<i class="far fa-calendar-alt"></i>
													<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
												</div>

												<div class="field-section date-out">
													<i class="far fa-calendar-alt"></i>
													<span class="date-value form-control"><?php echo trav_get_date_format('html'); ?></span>
												</div>

												<input type="text" name="datetimes" class="hidden-field" format="<?php echo trav_get_date_format('html'); ?>">
												<input type="hidden" name="date_from">
												<input type="hidden" name="date_to">
											</div>
										</div>

										<div class="guest-wrap">
											<label class="form-label"><?php echo esc_html__( 'Guests', 'trav' ); ?></label>

											<div class="field-section">
												<i class="fas fa-user"></i>
												<span class="guest-value form-control"><?php echo esc_html__( 'Who\'s going?', 'trav' ); ?></span>
											</div>

											<div class="guest-dropdown-info">
												<div class="guest-qty-section">
													<div class="room-qty qty-field">
														<div class="label-wrap">
															<span class="title"><?php echo esc_html__( 'Rooms', 'trav' ); ?></span>
															<span class="desc"><?php echo esc_html__( 'Min 1 Room', 'trav' ); ?></span>
														</div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="rooms" class="count-value" min="1" value="1">
															<i class="fas fa-plus"></i>
														</div>
													</div>

													<div class="adults-qty qty-field">
														<div class="label-wrap">
															<span class="title"><?php echo esc_html__( 'Adults', 'trav' ); ?></span>
															<span class="desc"><?php echo esc_html__( '17 Onward', 'trav' ); ?></span>
														</div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="adults" class="count-value" min="1" value="1">
															<i class="fas fa-plus"></i>
														</div>
													</div>

													<div class="children-qty qty-field">
														<div class="label-wrap">
															<span class="title"><?php echo esc_html__( 'Children', 'trav' ); ?></span>
															<span class="desc"><?php echo esc_html__( 'Ages 2-17', 'trav' ); ?></span>
														</div>

														<div class="count-wrap">
															<i class="fas fa-minus"></i>
															<input type="text" name="kids" class="count-value" min="0" value="0">
															<i class="fas fa-plus"></i>
														</div>
													</div>

                          <div class="child-age-inputs-container child-age-small-container">

                            <div class="single-child-age qty-field">
  														<div class="label-wrap">
  															<span class="title"><?php echo esc_html__( 'Child age', 'trav' ); ?></span>
  															<span class="desc"><?php echo esc_html__( 'Age of child', 'trav' ); ?> #<span class="child-input-number">1</span></span>
  														</div>

  														<div class="count-wrap">
  															<i class="fas fa-minus"></i>
  															<input type="text" name="child_ages[]" class="count-value" min="2" max="17" value="2">
  															<i class="fas fa-plus"></i>
  														</div>
  													</div>

                          </div>

												</div>

												<p class="guest-description"><?php echo esc_html__( '3 guests maximum. Infants don’t count toward the number of guests.', 'trav' ); ?></p>
											</div>
										</div>

										<div class="form-submit">
											<button type="submit" class="submit-btn"><?php echo esc_html__( 'Show Vacant Rooms', 'trav' ); ?></button>
										</div>
									</div>
								</form>
							</div>

						</aside>
					</div>
				</div>

				<div class="detail-bottom-wrapper">
					<?php
					$acc_footer_html_block = get_post_meta( $acc_id, 'trav_accommodation_footer_html_block', true );
					if ( ! empty( $acc_footer_html_block ) ) {
						echo do_shortcode( '[html_block block_id="' . $acc_footer_html_block . '"]' );
					}
					?>
				</div>
			</div>
		</div>

		<?php

	endwhile;
}
