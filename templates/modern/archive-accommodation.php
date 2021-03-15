<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_view, $trav_options, $search_max_rooms, $search_max_adults, $search_max_kids, $language_count;

$order_array = array( 'ASC', 'DESC' );
$text = esc_html__( 'name', 'trav' );
$text = esc_html__( 'price', 'trav' );
$text = esc_html__( 'rating', 'trav' );
$order_by_array = array(
	'name' => 'acc_title',
	'price' => 'cast(avg_price as unsigned)',
	'rating' => 'review'
);
$order_defaults = array(
	'name' => 'ASC',
	'price' => 'ASC',
	'rating' => 'DESC'
);

$s = isset($_REQUEST['s']) ? sanitize_text_field( $_REQUEST['s'] ) : '';
$rooms = ( isset( $_REQUEST['rooms'] ) && is_numeric( $_REQUEST['rooms'] ) ) ? sanitize_text_field( $_REQUEST['rooms'] ) : 1;
$adults = ( isset( $_REQUEST['adults'] ) && is_numeric( $_REQUEST['adults'] ) ) ? sanitize_text_field( $_REQUEST['adults'] ) : 1;
$kids = ( isset( $_REQUEST['kids'] ) && is_numeric( $_REQUEST['kids'] ) ) ? sanitize_text_field( $_REQUEST['kids'] ) : 0;
$min_price = ( isset( $_REQUEST['min_price'] ) && is_numeric( $_REQUEST['min_price'] ) ) ? sanitize_text_field( $_REQUEST['min_price'] ) : 0;
$max_price = ( isset( $_REQUEST['max_price'] ) && ( is_numeric( $_REQUEST['max_price'] ) || ( $_REQUEST['max_price'] == 'no_max' ) ) ) ? sanitize_text_field( $_REQUEST['max_price'] ) : 'no_max';
$rating = ( isset( $_REQUEST['rating'] ) && is_array( $_REQUEST['rating'] ) ) ? $_REQUEST['rating'] : array();
$order_by = ( isset( $_REQUEST['order_by'] ) && array_key_exists( $_REQUEST['order_by'], $order_by_array ) ) ? sanitize_text_field( $_REQUEST['order_by'] ) : 'price';
$order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], $order_array ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';
$acc_type = ( isset( $_REQUEST['acc_type'] ) ) ? ( is_array( $_REQUEST['acc_type'] ) ? $_REQUEST['acc_type'] : array( $_REQUEST['acc_type'] ) ):array();
$amenities = ( isset( $_REQUEST['amenities'] ) && is_array( $_REQUEST['amenities'] ) ) ? $_REQUEST['amenities'] : array();
$current_view = isset( $_REQUEST['view'] ) ? sanitize_text_field( $_REQUEST['view'] ) : 'list';
$page = ( isset( $_REQUEST['page'] ) && ( is_numeric( $_REQUEST['page'] ) ) && ( $_REQUEST['page'] >= 1 ) ) ? sanitize_text_field( $_REQUEST['page'] ) : 1;
$per_page = ( isset( $trav_options['acc_posts'] ) && is_numeric($trav_options['acc_posts']) ) ? $trav_options['acc_posts'] : 12;
$map_zoom = empty( $trav_options['acc_list_zoom'] )? 14 : $trav_options['acc_list_zoom'];

if ( is_tax() ) {
	$queried_taxonomy = get_query_var( 'taxonomy' );
	$queried_term = get_query_var( 'term' );
	$queried_term_obj = get_term_by('slug', $queried_term, $queried_taxonomy);
	if ( $queried_term_obj ) {
		if ( ( $queried_taxonomy == 'accommodation_type' ) && ( ! in_array( $queried_term_obj->term_id, $acc_type ) ) ) $acc_type[] = $queried_term_obj->term_id;
		if ( ( $queried_taxonomy == 'amenity' ) && ( ! in_array( $queried_term_obj->term_id, $amenities ) ) ) $amenities[] = $queried_term_obj->term_id;
	}
}

$date_from = isset( $_REQUEST['date_from'] ) ? trav_sanitize_date( $_REQUEST['date_from'] ) : '';
$date_to = isset( $_REQUEST['date_to'] ) ? trav_sanitize_date( $_REQUEST['date_to'] ) : '';
$datetimes = isset( $_REQUEST['datetimes'] ) ? $_REQUEST['datetimes'] : '';

if ( trav_strtotime( $date_from ) >= trav_strtotime( $date_to ) ) {
	$date_from = '';
	$date_to = '';
}

$results = trav_acc_get_search_result( $s, $date_from, $date_to, $rooms, $adults, $kids, $order_by_array[$order_by], $order, ( $page - 1 ) * $per_page, $per_page, $min_price, $max_price, $rating, $acc_type, $amenities );
$count = trav_acc_get_search_result_count( $min_price, $max_price, $rating, $acc_type, $amenities );

global $before_article, $after_article, $acc_list;

$before_article = '';
$after_article = '';

$acc_list = array();
foreach ( $results as $result ) {
	$acc_list[] = $result->acc_id;
}

$query_args = array(
		'adults'=> $adults,
		'kids' => $kids,
		'rooms' => $rooms,
		'date_from' => $date_from,
		'date_to' => $date_to
	);

$additional_class = '';

if ( $current_view == 'map' ) $additional_class = 'grid-view-2';
?>

<div class="main-content search-available-views <?php echo esc_attr( $current_view ); ?>-view <?php echo esc_attr( $additional_class ); ?>">
	<div class="page-top-search-box">
		<div class="hero-search-form-section container">
			<form action="<?php echo esc_url( get_post_type_archive_link( 'accommodation' ) ); ?>" class="hero-search-form acc-searchform" role="search" method="get" >
				<input type="hidden" name="view" value="<?php echo esc_attr( $current_view ); ?>">
				<input type="hidden" name="order_by" value="<?php echo esc_attr( $order_by ); ?>">
				<input type="hidden" name="order" value="<?php echo esc_attr( $order ); ?>">
				<?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
					<input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ); ?>">
				<?php } ?>

				<div class="direction-wrap">
					<label class="form-label"><?php echo esc_html__( 'Where', 'trav' ); ?></label>

					<div class="field-section">
						<i class="fas fa-map-marker-alt"></i>
						<input type="text" name="s" class="form-control" placeholder="<?php echo esc_attr__( 'Direction', 'trav' ); ?>" value="<?php echo esc_attr( $s ); ?>">
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
							<span class="date-value form-control"><?php echo empty( $date_from ) ? trav_get_date_format( 'html' ) : esc_attr( $date_from ); ?></span>
						</div>

						<div class="field-section date-out">
							<i class="far fa-calendar-alt"></i>
							<span class="date-value form-control"><?php echo empty( $date_from ) ? trav_get_date_format( 'html' ) : esc_attr( $date_to ); ?></span>
						</div>

						<input type="text" name="datetimes" class="hidden-field" value="<?php echo esc_attr( $datetimes ); ?>">
						<input type="hidden" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
						<input type="hidden" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
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
									<input type="text" name="rooms" class="count-value" min="1" value="<?php echo esc_attr( $rooms ); ?>">
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
									<input type="text" name="adults" class="count-value" min="1" value="<?php echo esc_attr( $adults ); ?>">
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
									<input type="text" name="kids" class="count-value" min="0" value="<?php echo esc_attr( $kids ); ?>">
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

						<p class="guest-description"><?php echo esc_html__( '102 guests maximum. Infants don’t count toward the number of guests.', 'trav' ); ?></p>
					</div>
				</div>

				<div class="form-submit">
					<button type="submit" class="submit-btn"><?php echo esc_html__( 'Update', 'trav' ); ?></button>
				</div>
			</form>
		</div>
	</div>

	<?php if ( $current_view != 'map' ) : ?>
		<div class="search-views-wrapper">
			<div class="container">
				<div class="view-top-area">
					<span class="description-txt"><?php echo esc_html( $count ); ?> <?php echo esc_html( _n( 'Hotel found.', 'Hotels found.', $count, 'trav' ) ); ?></span>

					<div class="order-view-filter">
						<div class="view-sort-selection dropdown">
							<a class="dropdown-toggle" href="#" id="sort-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html__( 'Sort By', 'trav' ); ?></a>

							<div class="dropdown-menu animate fadeIn" aria-labelledby="sort-dropdown">
								<a class="dropdown-item" href="<?php echo esc_url( add_query_arg( array( 'order_by' => 'price', 'order' => 'ASC' ) ) ); ?>"><?php echo esc_html__( 'Price: Low To High', 'trav' ); ?></a>
								<a class="dropdown-item" href="<?php echo esc_url( add_query_arg( array( 'order_by' => 'price', 'order' => 'DESC' ) ) ); ?>"><?php echo esc_html__( 'Price: High To Low', 'trav' ); ?></a>
							</div>
						</div>

						<div class="view-mode-section">
							<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'list' ) ) ); ?>" class="<?php echo ( $current_view == 'list' ) ? 'active' : ''; ?>"><i class="travelo-list"></i></a>
							<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'grid' ) ) ); ?>" class="<?php echo ( $current_view == 'grid' ) ? 'active' : ''; ?>"><i class="travelo-grid"></i></a>
						</div>
					</div>
				</div>

				<div class="search-view-main-area">
					<div class="row">
						<aside class="col-lg-3 sidebar">
							<div class="sidebar-section view-map-widget">
								<img src="<?php echo TRAV_IMAGE_URL . '/modern/google-map.png'; ?>" alt="Google Map Area">
								<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'map' ) ) ); ?>" class="view-map-link btn-map"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html__( 'View Map', 'trav' ); ?></a>
							</div>

							<?php if ( $trav_options['acc_enable_price_filter'] ) : ?>
							<div class="sidebar-section price-range-widget">
								<div class="widget-head">
									<span class="head-txt"><?php echo esc_html__( 'Price Range', 'trav' ); ?></span>
									<i class="fas fa-chevron-down"></i>
								</div>

								<div class="widget-content">
									<div class="price-filter-amount">
										<div class="min-price min-price-label">

										</div>

										<div class="max-price max-price-label">

										</div>
									</div>

									<div id="price-range" data-slide-last-val="<?php echo esc_attr( ( ! empty($trav_options['acc_price_filter_max']) && is_numeric($trav_options['acc_price_filter_max']) ) ? $trav_options['acc_price_filter_max'] :200 ) ?>" data-slide-step="<?php echo esc_attr( ( ! empty($trav_options['acc_price_filter_step']) && is_numeric($trav_options['acc_price_filter_step']) ) ? $trav_options['acc_price_filter_step'] :50 ) ?>" data-def-currency="<?php echo esc_attr( trav_get_site_currency_symbol() );?>" data-min-price="<?php echo esc_attr( $min_price ); ?>" data-max-price="<?php echo esc_attr( $max_price ); ?>" data-url-noprice="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price', 'page' ) ) ); ?>"></div>
								</div>
							</div>
							<?php endif; ?>

							<?php if ( $trav_options['acc_enable_review_filter'] ) : ?>
							<div id="accomodation-rating-filter" data-url-norating="<?php echo esc_url( remove_query_arg( array( 'rating', 'page' ) ) ); ?>" class="sidebar-section user-rating-widget">
								<div class="widget-head">
									<span class="head-txt"><?php echo esc_html__( 'User Rating', 'trav' ); ?></span>
									<i class="fas fa-chevron-down"></i>
								</div>

								<div class="widget-content">
									<?php for( $index = 5; $index >=0; $index-- ) : ?>
									<div class="individual-rating-state">
										<?php
										$checked = ( is_array( $rating ) && in_array( $index, $rating ) ) ? ' checked="checked"' : '';
										?>
										<input type="checkbox" name="five-star" id="rating-<?php echo esc_attr( $index ); ?>" value="<?php echo esc_attr( $index ); ?>" <?php echo $checked; ?> >
										<label for="rating-<?php echo esc_attr( $index ); ?>">
											<div class="five-stars-container">
												<span class="five-stars" style="width: <?php echo $index * 20; ?>%;"></span>
											</div>
											<span class="count">(<?php echo esc_html( trav_acc_get_search_result_count( $min_price, $max_price, array( $index ), $acc_type, $amenities ) ); ?>)</span>
										</label>
									</div>
									<?php endfor; ?>

								</div>
							</div>
							<?php endif; ?>

							<?php if ( $trav_options['acc_enable_acc_type_filter'] ) : ?>
							<div id="accomodation-type-filter" data-url-noacc_type="<?php echo esc_url( remove_query_arg( array( 'acc_type', 'page' ) ) ); ?>" class="sidebar-section property-type-widget">
								<div class="widget-head">
									<span class="head-txt"><?php echo esc_html__( 'Property Type', 'trav' ); ?></span>
									<i class="fas fa-chevron-down"></i>
								</div>

								<div class="widget-content">
									<?php $checked = empty( $acc_type ) ? 'checked="checked"': ''; ?>
									<div class="individual-property-type">
										<input type="checkbox" name="all" id="all-type" <?php echo $checked; ?> value="all" >
										<label for="all-type">
											<?php echo esc_html__( 'All', 'trav' ); ?> <span class="count">(<?php echo esc_html( $count ); ?>)</span>
										</label>
									</div>
									<?php

										$all_acc_types = get_terms( 'accommodation_type', array( 'hide_empty' => 0 ) );
										foreach ( $all_acc_types as $each_acc_type ) {
											$checked = ( ( is_array( $acc_type ) && in_array( $each_acc_type->term_id, $acc_type ) ) ) ? ' checked="checked"' : '';
											?>
											<div class="individual-property-type">
												<input type="checkbox" id="type_<?php echo esc_attr( $each_acc_type->term_id ); ?>" <?php echo $checked; ?> value="<?php echo esc_attr( $each_acc_type->term_id ); ?>">
												<label for="type_<?php echo esc_attr( $each_acc_type->term_id ); ?>">
													<?php echo esc_html( $each_acc_type->name ); ?> <span class="count">(<?php echo esc_html( trav_acc_get_search_result_count( $min_price, $max_price, $rating, array( $each_acc_type->term_id ), $amenities ) ); ?>)</span>
												</label>
											</div>
											<?php
										}
									?>

								</div>
							</div>
							<?php endif; ?>

							<?php if ( $trav_options['acc_enable_amenity_filter'] ) : ?>
							<div id="amenities-filter" data-url-noamenities="<?php echo esc_url( remove_query_arg( array( 'amenities', 'page' ) ) ); ?>" class="sidebar-section property-type-widget">
								<div class="widget-head">
									<span class="head-txt"><?php echo esc_html__( 'Amenity Type', 'trav' ); ?></span>
									<i class="fas fa-chevron-down"></i>
								</div>

								<div class="widget-content">
									<?php
										$args = array(
												'orderby'           => 'count',
												'order'             => 'DESC',
												'hide_empty' => 0
											);

										$all_amenities = get_terms( 'amenity', $args );

										foreach ( $all_amenities as $each_amenity ) {
											$checked = ( ( is_array( $amenities ) && in_array( $each_amenity->term_id, $amenities ) ) ) ? ' checked="checked"' : '';
											?>
											<div class="individual-property-type">
												<input type="checkbox" id="type_<?php echo esc_attr( $each_amenity->term_id ); ?>" <?php echo $checked; ?> value="<?php echo esc_attr( $each_amenity->term_id ); ?>">
												<label for="type_<?php echo esc_attr( $each_amenity->term_id ); ?>">
													<?php echo esc_html( $each_amenity->name ); ?> <span class="count">(<?php echo esc_html( trav_acc_get_search_result_count( $min_price, $max_price, $rating, $acc_type, array( $each_amenity->term_id ) ) ); ?>)</span>
												</label>
											</div>
											<?php
										}
									?>

								</div>
							</div>
							<?php endif; ?>

						</aside>

						<div class="col-lg-9">
							<?php if ( ! empty( $results ) ) { ?>
								<div class="available-travel-package-wrap">
									<?php trav_get_template( 'accommodation-list.php', '/templates/modern/accommodation/'); ?>
								</div>

							<?php
							if ( ! empty( $trav_options['ajax_pagination'] ) ) {
								if ( count( $results ) >= $per_page ) {
								?>
									<div class="load-more-btn">
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => ( $page + 1 ) ) ) ); ?>" class="border-btn-third btn-load-more-accs" data-view="<?php echo esc_attr( $current_view ); ?>" data-search-params="<?php echo esc_attr( http_build_query( $_GET, '', '&amp;' ) ) ?>"><?php echo esc_html__( 'Load More', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></a>
									</div>
								<?php
								}
							} else {
								unset( $_GET['page'] );

								$pagenum_link = strtok( $_SERVER["REQUEST_URI"], '?' ) . '%_%';
								$total = ceil( $count / $per_page );
								$args = array(
									'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
									'total' => $total,
									'format' => '?page=%#%',
									'current' => $page,
									'show_all' => false,
									'prev_next' => true,
									'prev_text' => __('Previous', 'trav'),
									'next_text' => __('Next', 'trav'),
									'end_size' => 1,
									'mid_size' => 2,
									'type' => 'list',
									'add_args' => $_GET,
								);

								?>
								<div class="travelo-pagination"><?php echo paginate_links( $args ); ?></div>
								<?php
							}
							?>

							<?php } else { ?>
								<div class="travelo-box"><?php echo esc_html__( 'No available accommodations', 'trav' ); ?></div>
							<?php } ?>

						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else : ?>
		<div class="search-views-wrapper">
			<div class="search-view-main-area">
				<div class="view-top-area">
					<span class="description-txt"><?php echo esc_html( $count ); ?> <?php echo esc_html( _n( 'Hotel found.', 'Hotels found.', $count, 'trav' ) ); ?></span>

					<div class="order-view-filter">
						<div class="view-sort-selection dropdown">
							<a class="dropdown-toggle" href="#" id="sort-dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo esc_html__( 'Sort By', 'trav' ); ?></a>

							<div class="dropdown-menu animate fadeIn" aria-labelledby="sort-dropdown">
								<a class="dropdown-item" href="<?php echo esc_url( add_query_arg( array( 'order_by' => 'price', 'order' => 'ASC' ) ) ); ?>"><?php echo esc_html__( 'Price: Low To High', 'trav' ); ?></a>
								<a class="dropdown-item" href="<?php echo esc_url( add_query_arg( array( 'order_by' => 'price', 'order' => 'DESC' ) ) ); ?>"><?php echo esc_html__( 'Price: High To Low', 'trav' ); ?></a>
							</div>
						</div>

						<div class="view-mode-section">
							<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'list' ) ) ); ?>" class="<?php echo ( $current_view == 'list' ) ? 'active' : ''; ?>"><i class="travelo-list"></i></a>
							<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'grid' ) ) ); ?>" class="<?php echo ( $current_view == 'grid' ) ? 'active' : ''; ?>"><i class="travelo-grid"></i></a>
						</div>
					</div>
				</div>

				<?php if ( ! empty( $results ) ) { ?>
					<div class="available-travel-package-wrap">
						<?php trav_get_template( 'accommodation-list.php', '/templates/modern/accommodation/'); ?>
					</div>

				<?php
				if ( ! empty( $trav_options['ajax_pagination'] ) ) {
					if ( count( $results ) >= $per_page ) {
					?>
						<div class="load-more-btn">
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => ( $page + 1 ) ) ) ); ?>" class="border-btn-third btn-load-more-accs" data-view="<?php echo esc_attr( $current_view ); ?>" data-search-params="<?php echo esc_attr( http_build_query( $_GET, '', '&amp;' ) ) ?>"><?php echo esc_html__( 'Load More', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></a>
						</div>
					<?php
					}
				} else {
					unset( $_GET['page'] );

					$pagenum_link = strtok( $_SERVER["REQUEST_URI"], '?' ) . '%_%';
					$total = ceil( $count / $per_page );
					$args = array(
						'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
						'total' => $total,
						'format' => '?page=%#%',
						'current' => $page,
						'show_all' => false,
						'prev_next' => true,
						'prev_text' => __('Previous', 'trav'),
						'next_text' => __('Next', 'trav'),
						'end_size' => 1,
						'mid_size' => 2,
						'type' => 'list',
						'add_args' => $_GET,
					);

					?>
					<div class="travelo-pagination"><?php echo paginate_links( $args ); ?></div>
					<?php
				}
				?>

				<?php } else { ?>
					<div class="travelo-box"><?php echo esc_html__( 'No available accommodations', 'trav' ); ?></div>
				<?php } ?>
			</div>

			<div class="map-area">
				<div id="map_listing"></div>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function(){
				//jQuery('#collapseMap').on('shown.bs.collapse', function(e){
					var zoom = <?php echo $map_zoom ?>;
					var markersData = {
						<?php foreach ( $acc_list as $acc_id ) {
							$acc_pos = get_post_meta( $acc_id, 'trav_accommodation_loc', true );
							if ( ! empty( $trav_options['map_marker_img'] ) && ! empty( $trav_options['map_marker_img']['url'] ) ) {
								$marker_img_url = $trav_options['map_marker_img']['url'];
							} else {
								$marker_img_url = TRAV_TEMPLATE_DIRECTORY_URI . "/images/modern/pin-marker.png";
							}

							if ( ! empty( $acc_pos ) ) {
								$acc_pos = explode( ',', $acc_pos );
								$brief = get_post_meta( $acc_id, 'trav_accommodation_brief', true );
								if ( empty( $brief ) ) {
									$brief = apply_filters('the_content', get_post_field('post_content', $acc_id));
									$brief = wp_trim_words( $brief, 20, '' );
								}

								$city = trav_acc_get_city( $acc_id );
								$country = trav_acc_get_country( $acc_id );
								if ( ! empty( $city ) || ! empty( $country ) ) {
									$location = $city . ', ' . $country;
								} else {
									$location = '';
								}

								$review_count = trav_modern_get_review_count( $acc_id );

								$review = get_post_meta( $acc_id, 'review', true );
								$review = ( ! empty( $review ) ) ? round( $review, 1 ) : 0;

								$avg_price = get_post_meta( $acc_id, 'trav_accommodation_avg_price', true );

							 ?>
								'<?php echo $acc_id; ?>' :  [{
									name: '<?php echo get_the_title( $acc_id ); ?>',
									type: 'Accommodation',
									location_latitude: <?php echo $acc_pos[0]; ?>,
									location_longitude: <?php echo $acc_pos[1]; ?>,
									location: '<?php echo $location; ?>',
									map_image: '<?php echo get_the_post_thumbnail( $acc_id, 'modern-map-thumb' ); ?>',
									name_point: '<?php echo get_the_title( $acc_id ); ?>',
									rate: <?php echo ( ! empty( $review ) ) ? floatval( $review ) : 0; ?>,
									review_number: <?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?>,
									price: '<?php echo trav_get_price_field( $avg_price ); ?>',
									price_unit: '<?php echo esc_html__( 'Pre Night', 'trav' ); ?>',
									url_point: '<?php echo esc_url( add_query_arg( $query_args, get_permalink( $acc_id ) ) ); ?>',
									closeBoxURL: '<?php echo TRAV_TEMPLATE_DIRECTORY_URI . "/images/modern/close_infobox.png"; ?>'
								}],
							<?php
							}
						} ?>
					};
					<?php
					$acc_pos = array();
					if ( ! empty( $acc_list ) ) {
						foreach ( $acc_list as $acc_id ) {
							$acc_pos = get_post_meta( $acc_id, 'trav_accommodation_loc', true );

							if ( ! empty( $acc_pos ) ) {
								$acc_pos = explode( ',', $acc_pos );
								break;
							}
						}
					}

					if ( ! empty( $acc_pos ) ) {
					?>
					var lati = <?php echo $acc_pos[0] ?>;
					var long = <?php echo $acc_pos[1] ?>;
					// var _center = [48.865633, 2.321236];
					var _center = [lati, long];
					renderMap( _center, markersData, zoom, google.maps.MapTypeId.ROADMAP, false, '<?php echo $marker_img_url; ?>' );
					<?php } ?>
				//});
			});
		</script>

	<?php endif; ?>

	<div class="footer-content-wrapper">
		<?php
		if ( ! empty( trav_get_opt( 'acc_footer_content' ) ) ) {
			echo do_shortcode( '[html_block block_id="' . trav_get_opt( 'acc_footer_content' ) . '"]' );
		}
		?>
	</div>
</div>
