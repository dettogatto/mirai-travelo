<?php
/**
 * Search Form Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_search_form]
function mt_shortcode_search_form( $atts, $content = null ) {

	$variables = array(
						'extra_class'	=> '',
						'method'		=> 'get',
						'post_type'		=> 'post',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-search-form-' . $id );

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	$method = ( $method == 'get' ) ? 'get' : 'post';
	$def_post_types = array( 'accommodation', 'tour', 'post' );

	if ( ! in_array( $post_type, $def_post_types ) ) $def_post_types = 'post';

	global $trav_options, $search_max_rooms, $search_max_adults, $search_max_kids, $def_currency, $search_max_passengers, $search_max_flighters;

	ob_start();
	if ( $post_type == 'accommodation' && empty( $content ) ) { ?>

		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="search-box <?php echo esc_attr( $content_class ); ?>">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="acc-searchform hero-search-form">
				<input type="hidden" name="post_type" value="accommodation">

				<div class="direction-wrap">
					<label class="form-label"><?php echo esc_html__( 'Where', 'trav' ); ?></label>

					<div class="field-section">
						<i class="fas fa-map-marker-alt"></i>
						<input type="text" class="form-control" name="s" placeholder="<?php echo esc_attr__( 'Direction', 'trav' ); ?>">
					</div>
				</div>

				<div class="check-in-out-wrap">
					<div class="label-section">
						<label class="form-label"><?php echo esc_html__( 'Check In', 'trav' ); ?></label>
						<label class="form-label"><?php echo esc_html__( 'Check Out', 'trav' ); ?></label>
					</div>

					<div class="field-sections">
						<div class="field-section date-in">
							<i class="far fa-calendar-alt"></i>
							<span class="date-value form-control"><?php echo trav_get_date_format( 'html' ); ?></span>
						</div>

						<div class="field-section date-out">
							<i class="far fa-calendar-alt"></i>
							<span class="date-value form-control"><?php echo trav_get_date_format( 'html' ); ?></span>
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

							<div class="children-qty qty-field children-age-field-container">
								<div class="label-wrap">
									<span class="title"><?php echo esc_html__( 'Children', 'trav' ); ?></span>
									<span class="desc"><?php echo esc_html__( 'Ages 2-17', 'trav' ); ?></span>
								</div>

								<div class="count-wrap">
									<i class="fas fa-minus"></i>
									<input type="text" name="kids" class="count-value" min="0" value="0">
									<i class="fas fa-plus"></i>
								</div>
								<input type="hidden" name="child_ages[]" value="2">
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
					<button type="submit" class="submit-btn"><?php echo esc_html__( 'Search', 'trav' ); ?></button>
				</div>
			</form>
		</div>

	<?php } elseif ( $post_type == 'tour' && empty( $content ) ) { ?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="search-box <?php echo esc_attr( $content_class ); ?>">
			<form role="search" method="get" class="tour-searchform hero-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="tour">

				<div class="direction-wrap">
					<label class="form-label"><?php echo esc_html__( 'Where', 'trav' ); ?></label>

					<div class="field-section">
						<i class="fas fa-map-marker-alt"></i>
						<input type="text" class="form-control" name="s" placeholder="<?php echo esc_attr__( 'Direction', 'trav' ); ?>">
					</div>
				</div>

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

				<div class="category-wrap dropdown-control-wrap">
					<label class="form-label"><?php echo esc_html__( 'Category', 'trav' ); ?></label>

					<?php $trip_types = get_terms( 'tour_type', array( 'hide_empty' => 0 ) ); ?>

					<div class="field-section">
						<i class="fas fa-tree"></i>
						<div class="tour-type-category category-selection form-control">
							<select name="tour_types" class="full-width">
								<option value=""><?php _e( 'Trip Type', 'trav' ) ?></option>
								<?php foreach ( $trip_types as $trip_type ) : ?>
									<option value="<?php echo $trip_type->term_id ?>"><?php _e( $trip_type->name, 'trav' ) ?></option>
								<?php endforeach; ?>
							</select>
							<span class="dropdown-nav"><i class="fas fa-chevron-down"></i></span>
						</div>
					</div>
				</div>

				<div class="budget-wrap">
					<label class="form-label"><?php echo esc_html__( 'Budget', 'trav' ); ?></label>

					<div class="field-section">
						<i class="fas fa-wallet"></i>
						<input type="text" name="max_price" class="form-control" placeholder="<?php echo sprintf( esc_html__( 'Amount (%s)', 'trav'), strtoupper( $def_currency ) ); ?>">
					</div>
				</div>

				<div class="form-submit">
					<button type="submit" class="submit-btn"><?php echo esc_html__( 'Search', 'trav' ); ?></button>
				</div>
			</form>
		</div>

	<?php } else { ?>
		<form action="<?php echo esc_url( home_url( '/' ) ) ?>" method="<?php echo esc_attr( $method ) ?>"<?php echo $class?>><input type="hidden" value="<?php echo esc_attr( $post_type ) ?>" name="post_type">
			<?php echo do_shortcode( $content ); ?>
		</form>
	<?php }
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'mt_modern_search_form', 'mt_shortcode_search_form' );
