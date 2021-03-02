<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage vacancies
 */
if ( ! class_exists( 'Trav_Acc_Vacancy_List_Table') ) :
class Trav_Acc_Vacancy_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'vacancy',     //singular name of the listed records
			'plural'    => 'vacancies',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		$link_pattern = '<a href="edit.php?post_type=%1s&page=%2$s&action=%3$s&vacancy_id=%4$s">%5$s</a>';
		switch( $column_name ) {
			case 'id':
			case 'rooms':
			case 'price_per_room':
			case 'price_per_person':
				return $item[ $column_name ];
			case 'date_from':
			case 'date_to':
				$actions = array(
					'edit'      => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'vacancies', 'edit', $item['id'], 'Edit' ),
					'delete'    => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'vacancies', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'vacancy_delete' ), 'Delete' )
				);
				$content = sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'vacancies', 'edit', $item['id'], $item[$column_name] );
				//Return the title contents
				return sprintf( '%1$s %2$s', $content, $this->row_actions( $actions ) );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function column_accommodation_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['acc_id'] ) . '">' . $item['accommodation_name'] . '</a>';
	}

	function column_room_type_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['room_type_id'] ) . '">' . $item['room_type_name'] . '</a>';
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'        => __( 'ID', 'trav' ),
			'date_from'=> __( 'Date From', 'trav' ),
			'date_to'  => __( 'Date To', 'trav' ),
			'accommodation_name'  => __( 'Accommodation Name', 'trav' ),
			'room_type_name' => __( 'Room Type', 'trav' ),
			'rooms'     => __( 'Number of Rooms', 'trav' ),
			'price_per_room'  => __( 'Price per Room(per night)', 'trav' ),
			'price_per_person'  => __( 'Price per Person(per night)', 'trav' )
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'            => array( 'id', false ),
			'date_from' => array( 'date_from', false ),
			'date_to'       => array( 'date_to', false ),
			'accommodation_name'    => array( 'accommodation_name', false ),
			'room_type_name'        => array( 'room_type_name', false ),
			'rooms'         => array( 'rooms', false ),
			'price_per_room'            => array( 'price_per_room', false ),
			'price_per_person'  => array( 'price_per_person', false )
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'bulk_delete'    => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		//Detect when a bulk action is being triggered...

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) )
				wp_die( 'Sorry, your nonce did not verify' );

		}
		if ( 'bulk_delete'===$this->current_action() ) {
			$selected_ids = $_GET[ $this->_args['singular'] ];
			$how_many = count($selected_ids);
			$placeholders = array_fill(0, $how_many, '%d');
			$format = implode(', ', $placeholders);
			$current_user_id = get_current_user_id();
			$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );
			$sql = '';

			if ( current_user_can( 'manage_options' ) ) {
				$sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_ACCOMMODATION_VACANCIES_TABLE, "$format" );
			} else {
				$sql = sprintf( 'DELETE %1$s FROM %1$s INNER JOIN %2$s as accommodation ON accommodation_id=accommodation.ID WHERE %1$s.id IN (%3$s) AND accommodation.post_author = %4$d', TRAV_ACCOMMODATION_VACANCIES_TABLE, $post_table_name, "$format", $current_user_id );
			}

			//$sql = sprintf( $sql, $selected_ids );
			$sql = $wpdb->prepare( $sql, $selected_ids );
			$wpdb->query( $sql );
			wp_redirect( admin_url( 'edit.php?post_type=accommodation&page=vacancies&bulk_delete=true') );
		}

	}

	function prepare_items() {
		global $wpdb;
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'id'; //If no sort, default to title
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc'; //If no order, default to desc
		$current_page = $this->get_pagenum();
		$post_table_name  = $wpdb->prefix . 'posts';

		$where = "1=1";
		if ( ! empty( $_REQUEST['accommodation_id'] ) ) $where .= " AND Trav_Vacancies.accommodation_id = '" . esc_sql( trav_acc_org_id( $_REQUEST['accommodation_id'] ) ) . "'";
		if ( ! empty( $_REQUEST['room_type_id'] ) ) $where .= " AND Trav_Vacancies.room_type_id = '" . esc_sql( trav_room_org_id( $_REQUEST['room_type_id'] ) ) . "'";
		if ( ! empty( $_REQUEST['date'] ) ) $where .= " AND Trav_Vacancies.date_from <= '" . esc_sql( $_REQUEST['date'] ) . "' and Trav_Vacancies.date_to > '" . $_REQUEST['date'] . "'" ;
		if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND accommodation.post_author = '" . get_current_user_id() . "' "; }

		$sql = sprintf( 'SELECT Trav_Vacancies.* , accommodation.ID as acc_id, accommodation.post_title as accommodation_name, room_type.ID as room_type_id, room_type.post_title as room_type_name FROM %1$s as Trav_Vacancies
			INNER JOIN %2$s as accommodation ON Trav_Vacancies.accommodation_id=accommodation.ID
			INNER JOIN %2$s as room_type ON Trav_Vacancies.room_type_id=room_type.ID
			WHERE ' . $where . ' ORDER BY %4$s %5$s
			LIMIT %6$s, %7$s' , TRAV_ACCOMMODATION_VACANCIES_TABLE, $post_table_name, '', $orderby, $order, ( $per_page * ( $current_page - 1 ) ), $per_page );

		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as Trav_Vacancies INNER JOIN %2$s as accommodation ON Trav_Vacancies.accommodation_id=accommodation.ID WHERE %3$s' , TRAV_ACCOMMODATION_VACANCIES_TABLE, $post_table_name, $where );
		$total_items = $wpdb->get_var( $sql );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items/$per_page )   //WE have to calculate the total number of pages
		) );
	}
}
endif;

/*
 * add vacancy list page to menu
 */
if ( ! function_exists( 'trav_acc_vacancy_add_menu_items' ) ) {
	function trav_acc_vacancy_add_menu_items() {
		$page = add_submenu_page( 'edit.php?post_type=accommodation', __('Accommodation Vacancies', 'trav'), __('Vacancies', 'trav'), 'edit_accommodations', 'vacancies', 'trav_acc_vacancy_render_pages' );
		add_action( 'admin_print_scripts-' . $page, 'trav_acc_vacancy_admin_enqueue_scripts' );
	}
}

/*
 * vacancy admin main actions
 */
if ( ! function_exists( 'trav_acc_vacancy_render_pages' ) ) {
	function trav_acc_vacancy_render_pages() {

		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			trav_acc_vacancy_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			trav_acc_vacancy_delete_action();
		} else {
			trav_acc_vacancy_render_list_page();
		}
	}
}

/*
 * render vacancy list page
 */
if ( ! function_exists( 'trav_acc_vacancy_render_list_page' ) ) {
	function trav_acc_vacancy_render_list_page() {
		global $wpdb;
		$travVancancyTable = new Trav_Acc_Vacancy_List_Table();
		$travVancancyTable->prepare_items();
		?>

		<div class="wrap">
			<h2><?php _e('Accommodation Vacancies', 'trav') ?> <a href="edit.php?post_type=accommodation&amp;page=vacancies&amp;action=add" class="add-new-h2"><?php _e('Add New', 'trav') ?></a></h2>
			<?php if ( isset( $_REQUEST['bulk_delete'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __('Vacancies deleted', 'trav') . '</p></div>'?>
			<select id="accommodation_filter">
				<option></option>
				<?php
				$args = array(
					'post_type'         => 'accommodation',
					'posts_per_page'    => -1,
					'orderby'           => 'title',
					'order'             => 'ASC'
				);
				/* bussinerss managers can see their own post only */
				if ( ! current_user_can( 'manage_options' ) ) {
					$args['author'] = get_current_user_id();
				}

				$accommodation_query = new WP_Query( $args );

				if ( $accommodation_query->have_posts() ) {
					while ( $accommodation_query->have_posts() ) {
						$accommodation_query->the_post();
						$selected = '';
						$id = $accommodation_query->post->ID;
						if ( ! empty( $_REQUEST['accommodation_id'] ) && ( $_REQUEST['accommodation_id'] == $id ) ) $selected = ' selected ';
						echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
					}
				} else {
					// no posts found
				}
				/* Restore original Post Data */
				wp_reset_postdata();
				?>
			</select>
			<select id="room_type_filter">
				<option></option>
				<?php
					$args = array(
						'post_type'         => 'room_type',
						'posts_per_page'    => -1,
						'orderby'           => 'title',
						'order'             => 'ASC'
					);
					/* bussinerss managers can see their own post only */
					if ( ! current_user_can( 'manage_options' ) ) {
						$args['author'] = get_current_user_id();
					}

					if ( ! empty( $_REQUEST['accommodation_id'] ) ) {
						$args['meta_query'] = array(
								array(
									'key'     => 'trav_room_accommodation',
									'value'   => sanitize_text_field( $_REQUEST['accommodation_id'] ),
								),
							);
					}
					$room_type_query = new WP_Query( $args );

					if ( $room_type_query->have_posts() ) {
						while ( $room_type_query->have_posts() ) {
							$room_type_query->the_post();
							$selected = '';
							$id = $room_type_query->post->ID;
							if ( ! empty( $_REQUEST['room_type_id'] ) && ( $_REQUEST['room_type_id'] == $id ) ) $selected = ' selected ';
							echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
						}
					} else {
						// no posts found
					}
					/* Restore original Post Data */
					wp_reset_postdata();
				?>
			</select>
			<input type="text" id="date_filter" placeholder="<?php _e('Filter by Date', 'trav') ?>" value="<?php echo isset($_REQUEST['date']) ? esc_attr( $_REQUEST['date'] ):'' ?>">
			<input type="button" name="vacancy_filter" id="vacancy-filter" class="button" value="<?php _e('Filter', 'trav') ?>">
			<a href="edit.php?post_type=accommodation&amp;page=vacancies" class="button-secondary"><?php _e('Show All', 'trav') ?></a>
			<form id="accomo-vacancies-filter" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $_REQUEST['post_type'] ) ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $travVancancyTable->display() ?>
			</form>

		</div>
		<?php
	}
}

/*
 * render vacancy detail page
 */
if ( ! function_exists( 'trav_acc_vacancy_render_manage_page' ) ) {
	function trav_acc_vacancy_render_manage_page() {

		global $wpdb;

		if ( ! empty( $_POST['save'] ) ) {
			trav_acc_vacancy_save_action();
			return;
		}

		$default_vacancy_data = array(
			'id'                => '',
			'accommodation_id'  => '',
			'room_type_id'      => '',
			'treatment_id'      => '',
			'rooms'        => 1,
			'date_from'        => date( 'Y-m-d' ),
			'date_to'          => '',
			'price_per_room'     => '',
			'price_per_person'=>''
		);
		$vacancy_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __('Add New Accommodation Vacancy', 'trav');
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __('Edit Accommodation Vacancy', 'trav') . '<a href="edit.php?post_type=accommodation&amp;page=vacancies&amp;action=add" class="add-new-h2">' . __('Add New', 'trav') . '</a>';

			if ( empty( $_REQUEST['vacancy_id'] ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
			$vacancy_id = sanitize_text_field( $_REQUEST['vacancy_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$where = 'Trav_Vacancies.id = %3$d';
			if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND accommodation.post_author = '" . get_current_user_id() . "' "; }

			$sql = sprintf( 'SELECT Trav_Vacancies.* , accommodation.post_title as accommodation_name, room_type.post_title as room_type_name FROM %1$s as Trav_Vacancies
							INNER JOIN %2$s as accommodation ON Trav_Vacancies.accommodation_id=accommodation.ID
							INNER JOIN %2$s as room_type ON Trav_Vacancies.room_type_id=room_type.ID
							WHERE ' . $where , TRAV_ACCOMMODATION_VACANCIES_TABLE, $post_table_name, $vacancy_id );

			$vacancy_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $vacancy_data ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
		}

		$vacancy_data = array_replace( $default_vacancy_data, $vacancy_data );
		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __('Vacancy saved', 'trav') . '</p></div>'?>
			<form method="post" onsubmit="return manage_vacancy_validateForm();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $vacancy_data['id'] ) ) echo esc_attr( $vacancy_data['id'] ); ?>">
				<table class="trav_admin_table trav_acc_vacancy_manage_table">

          <tr>
            <th colspan="3">
              <h3 class="mirai-admin-title">Room</h3>
            </th>
          </tr>


					<tr>
						<th><?php _e('Accommodation', 'trav') ?></th>
						<td>
							<select name="accommodation_id" id="accommodation_id">
								<option></option>
								<?php
									$args = array(
										'post_type'         => 'accommodation',
										'posts_per_page'    => -1,
										'orderby'           => 'title',
										'order'             => 'ASC'
									);
									/* bussinerss managers can see their own post only */
									if ( ! current_user_can( 'manage_options' ) ) {
										$args['author'] = get_current_user_id();
									}
									$accommodation_query = new WP_Query( $args );

									if ( $accommodation_query->have_posts() ) {
										while ( $accommodation_query->have_posts() ) {
											$accommodation_query->the_post();
											$selected = '';
											$id = $accommodation_query->post->ID;
											if ( ( ! empty( $vacancy_data['accommodation_id'] ) ) && ( $vacancy_data['accommodation_id'] == trav_acc_org_id( $id ) ) ) $selected = ' selected ';
											echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( trav_acc_org_id( $id ) ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
										}
									}
									wp_reset_postdata();
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e('Room Type', 'trav') ?></th>
						<td>
							<select name="room_type_id" id="room_type_id">
								<option></option>
								<?php
									$args = array(
										'post_type'         => 'room_type',
										'posts_per_page'    => -1,
										'orderby'           => 'title',
										'order'             => 'ASC',
									);
									/* bussinerss managers can see their own post only */
									if ( ! current_user_can( 'manage_options' ) ) {
										$args['author'] = get_current_user_id();
									}
									if ( ! empty( $vacancy_data['accommodation_id'] ) ) {
										$args['meta_query'] = array(
												array(
													'key'     => 'trav_room_accommodation',
													'value'   => trav_acc_clang_id( $vacancy_data['accommodation_id'] ),
												),
											);
									}
									$room_type_query = new WP_Query( $args );

									if ( $room_type_query->have_posts() ) {
										while ( $room_type_query->have_posts() ) {
											$room_type_query->the_post();
											$selected = '';
											$id = $room_type_query->post->ID;
											if ( ( ! empty( $vacancy_data['room_type_id'] ) ) && ( $vacancy_data['room_type_id'] == trav_room_org_id( $id ) ) ) $selected = ' selected ';
											echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( trav_room_org_id( $id ) ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
										}
									}
									wp_reset_postdata();
								?>
							</select>
						</td>
					</tr>

					<tr>
						<th><?php _e('Treatment', 'trav') ?></th>
						<td>
							<select name="treatment_id" id="treatment_id" style="width: 250px">
								<option></option>
								<?php
								// List the treatments

                $treatments = get_terms(array(
                  'taxonomy' => 'treatment',
                  'hide_empty' => false
                ));

                foreach ($treatments as $treatment) {
                  $selected = '';
                  if ( ! empty( $vacancy_data['treatment_id'] ) && $vacancy_data['treatment_id'] == $treatment->term_id ) {
                    $selected = 'selected';
                  }
                  echo('<option '.esc_attr( $selected ).' value="'.$treatment->term_id.'">'.$treatment->name.'</option>');
                }

								?>
							</select>
						</td>
					</tr>

					<tr>
						<th><?php _e('Number of Rooms', 'trav') ?></th>
						<td><input type="number" name="rooms" min="1" value="<?php if ( ! empty( $vacancy_data['rooms'] ) ) echo esc_attr( $vacancy_data['rooms'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e('Date From', 'trav') ?></th>
						<td><input type="text" name="date_from" id="date_from" value="<?php if ( ! empty( $vacancy_data['date_from'] ) ) echo esc_attr( $vacancy_data['date_from'] ); ?>"></td>
						<td><span><?php _e('If you leave this field blank it will be set as current date', 'trav') ?></span></td>
					</tr>
					<tr>
						<th><?php _e('Date To', 'trav') ?></th>
						<td><input type="text" name="date_to" id="date_to" value="<?php if ( ( ! empty( $vacancy_data['date_to'] ) ) && ( $vacancy_data['date_to'] != '9999-12-31' ) ) echo esc_attr( $vacancy_data['date_to'] ); ?>"></td>
						<td><span><?php _e('Leave it blank if this rooms are available all the time', 'trav') ?></span></td>
					</tr>
          <tr>
            <th><?php _e('Minimum stay (nights)', 'trav') ?></th>
            <td><input type="number" name="minimum_stay" value="<?php if ( ! empty( $vacancy_data['minimum_stay'] ) ) echo esc_attr( $vacancy_data['minimum_stay'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank for no restriction', 'trav') ?></span></td>
          </tr>
          <tr>
            <th><?php _e('Maximum stay (nights)', 'trav') ?></th>
            <td><input type="number" name="maximum_stay" value="<?php if ( ! empty( $vacancy_data['maximum_stay'] ) ) echo esc_attr( $vacancy_data['maximum_stay'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank for no restriction', 'trav') ?></span></td>
          </tr>

          <tr>
            <th>Weekdays</th>
            <td colspan="2">
              <table class="admin-weekdays-table">
                <tr>
                  <td></td>
                  <th style="padding-left: 20px;">Mon</th>
                  <th style="padding-left: 20px;">Tue</th>
                  <th style="padding-left: 20px;">Wed</th>
                  <th style="padding-left: 20px;">Thu</th>
                  <th style="padding-left: 20px;">Fri</th>
                  <th style="padding-left: 20px;">Sat</th>
                  <th style="padding-left: 20px;">Sun</th>
                  <td></td>
                </tr>
                <tr>
                  <th class="check-title">Check-in</th>
                  <td><input type="checkbox" name="checkin_days[0]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][0] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[1]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][1] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[2]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][2] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[3]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][3] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[4]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][4] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[5]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][5] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkin_days[6]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkin_days'][6] ) echo esc_attr( 'checked' ); ?>></td>
                  <td>
                    <a href="#" class="button-primary mt-check-all-row">ALL</a>
                    <a href="#" class="button-primary mt-uncheck-all-row">NONE</a>
                  </td>
                </tr>
                <tr>
                  <th class="check-title">Check-out</th>
                  <td><input type="checkbox" name="checkout_days[0]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][0] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[1]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][1] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[2]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][2] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[3]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][3] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[4]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][4] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[5]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][5] ) echo esc_attr( 'checked' ); ?>></td>
                  <td><input type="checkbox" name="checkout_days[6]" value="1" <?php if ( !$vacancy_data['id'] || $vacancy_data['checkout_days'][6] ) echo esc_attr( 'checked' ); ?>></td>
                  <td>
                    <a href="#" class="button-primary mt-check-all-row">ALL</a>
                    <a href="#" class="button-primary mt-uncheck-all-row">NONE</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>




					<tr>
						<th><?php _e('Price Per Room (per night)', 'trav') ?></th>
						<td><input type="number" name="price_per_room" value="<?php if ( ! empty( $vacancy_data['price_per_room'] ) ) echo esc_attr( $vacancy_data['price_per_room'] ); ?>" step=".01"></td>
					</tr>

          <tr>
            <th colspan="3">
              <h3 class="mirai-admin-title">Adults</h3>
            </th>
          </tr>

          <tr>
            <th><?php _e('Minimum adults', 'trav') ?></th>
            <td><input type="number" name="minimum_adults" value="<?php if ( ! empty( $vacancy_data['minimum_adults'] ) ) echo esc_attr( $vacancy_data['minimum_adults'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank to use room\'s value', 'trav') ?></span></td>
          </tr>
          <tr>
            <th><?php _e('Maximum adults', 'trav') ?></th>
            <td><input type="number" name="maximum_adults" value="<?php if ( ! empty( $vacancy_data['maximum_adults'] ) ) echo esc_attr( $vacancy_data['maximum_adults'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank to use room\'s value', 'trav') ?></span></td>
          </tr>

          <tr class="mirai-admin-padding">
            <th><?php _e('Advanced price', 'trav') ?></th>
            <td>
              <label for="advanced_cost_yn">
                <input type="checkbox" id="advanced_cost_yn" name="advanced_cost_yn" value="y" <?php if ( ! empty( $vacancy_data['advanced_price'] ) ) echo esc_attr( 'checked' ); ?>>
                <?php _e('Choose individual prices for the adults', 'trav') ?>
              </label>
            </td>
          </tr>

          <tr id="price_per_person_container">
            <th><?php _e('Price Per Person (per night)', 'trav') ?></th>
            <td><input type="number" name="price_per_person" value="<?php if ( ! empty( $vacancy_data['price_per_person'] ) ) echo esc_attr( $vacancy_data['price_per_person'] ); ?>" step=".01"></td>
          </tr>





          <?php

          // $advanced_price = array(
          //   array(
          //     [50], // price of adult #1
          //     [50], // price of adult #2
          //     [25], // price of adult #3
          //   )
          // );

          $advanced_price = array();
          if ( ! empty( $vacancy_data['advanced_price'] ) ) {
            $advanced_price = unserialize( $vacancy_data['advanced_price'] );
          }



          if ( empty( $advanced_price ) ) {
            $advanced_price = array(
              array(
                [""]
              )
            );
          }

            $i = 0;

            foreach ($advanced_price as $current_advanced_price) {

              ?>

              <tr class="advanced-cost single-advanced-cost">
    						<th><?php _e('Price for Adult', 'trav') ?> #<span class="n_of_adult"><?php echo($i+1); ?></span> (per night)</th>
    						<td>
                  <input type="number" name="advanced_price[]" value="<?php echo($current_advanced_price); ?>" step=".01">
                  <a href="#" class="button-primary mt-advanced-percentage-price">%</a>
                  <a href="#" class="button-secondary mt-advanced-price-remove-adult">-</a>
                </td>
    					</tr>

              <?php


              $i++;

            }

          ?>

          <tr class="advanced-cost mirai-admin-padding">
            <th></th>
            <td>
              <a href="#" class="button-primary mt-add-advanced-cost">Add adult</a>
            </td>
            <td>
              <span>The price of further adults will be the same as the last price specified</span>
            </td>
          </tr>
















          <tr>
            <th colspan="3">
              <h3 class="mirai-admin-title">Children</h3>
            </th>
          </tr>

          <tr>
            <th><?php _e('Minimum kids', 'trav') ?></th>
            <td><input type="number" name="minimum_kids" value="<?php if ( ! empty( $vacancy_data['minimum_kids'] ) ) echo esc_attr( $vacancy_data['minimum_kids'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank to use room\'s value', 'trav') ?></span></td>
          </tr>
          <tr>
            <th><?php _e('Maximum kids', 'trav') ?></th>
            <td><input type="number" name="maximum_kids" value="<?php if ( ! empty( $vacancy_data['maximum_kids'] ) ) echo esc_attr( $vacancy_data['maximum_kids'] ); ?>" step="1"></td>
            <td><span><?php _e('Leave blank to use room\'s value', 'trav') ?></span></td>
          </tr>

					<tr class="mirai-admin-padding">
						<th><?php _e('Charge for Children?', 'trav') ?></th>
						<td>
							<label for="child_cost_yn">
								<input type="checkbox" id="child_cost_yn" name="child_cost_yn" value="y" <?php if ( ! empty( $vacancy_data['child_price'] ) ) echo esc_attr( 'checked' ); ?>>
								<?php _e('Charge for Children', 'trav') ?>
							</label>
						</td>
					</tr>



          <?php

          // $child_price = array(
          //   array(
          //     [1, 3, 50], // [min-age, max-age, price]
          //     [4, 7, 100],
          //     [8, 10, 110],
          //   ),
          //   array(
          //     [1, 3, 25],
          //     [4, 7, 50],
          //     [8, 10, 55],
          //   )
          // );

          $child_price = array();
          if ( ! empty( $vacancy_data['child_price'] ) ) {
            $child_price = unserialize( $vacancy_data['child_price'] );
          }



          if ( empty( $child_price ) ) {
            $child_price = array(
              array(
                ["", "", ""]
              )
            );
          }

            $i = 0;

            foreach ($child_price as $current_child_price) {

              ?>

              <tr class="child_cost single_child_cost">
                <th style="padding-top: 0px;">
                  <?php _e('Price of Children', 'trav') ?> #<span class="n_of_child"><?php echo($i+1); ?></span>
                  <br>
                  (per night)
                </th>
                <td colspan="2">
                  <table>
                    <tr>
                      <th>min age</th>
                      <th style="padding-left: 20px;">max age</th>
                      <th style="padding-left: 20px;">price</th>
                      <td></td>
                    </tr>
                    <?php
                    $j = 0;
                    foreach ($current_child_price as $age_price) {
                      ?>
                      <tr class="child_cost_age">
                        <td style="padding-left: 0px;">
                          <input style="width: 60px" type="number" name="child_price[<?= $i; ?>][<?= $j; ?>][0]" value="<?= $age_price[0] ?>" min="0">
                        </td>
                        <td>
                          <input style="width: 60px" type="number" name="child_price[<?= $i; ?>][<?= $j; ?>][1]" value="<?= $age_price[1] ?>" min="0">
                        </td>
                        <td>
                          <input style="width: 100px" type="number" name="child_price[<?= $i; ?>][<?= $j; ?>][2]" value="<?= $age_price[2] ?>" min="0">
                          <a href="#" class="button-primary mt-child-percentage-price">%</a>
                        </td>
                        <td>
                          <a href="#" class="button-secondary mt-child-remove-age">-</a>
                        </td>
                      </tr>
                      <?php
                      $j++;
                    }
                    ?>
                  </table>
                  <div class="mirai-admin-padding">
                    <a href="#" class="button-primary mt-child-add-age">Add age</a>
                  </div>
                </td>
              </tr>

              <?php


              $i++;

            }

          ?>


        <tr class="child_cost">
          <th></th>
          <td></td>
          <td><span>The price of further childs will be calculated with the last rates specified</span></td>
        </tr>

        </table>
        <div class="child_cost mirai-admin-padding" style="display:none;">
          <a href="#" class="button-primary mt-child-add-child">Add child</a>
        </div>
				<input type="submit" class="button-primary" name="save" value="<?php _e('Save Vacancy', 'trav') ?>">
				<a href="edit.php?post_type=accommodation&amp;page=vacancies" class="button-secondary"><?php _e('Cancel', 'trav') ?></a>
				<?php wp_nonce_field('trav_acc_vacancy_manage','vacancy_save'); ?>
			</form>
		</div>
		<?php
	}
}

/*
 * vacancy delete action
 */
if ( ! function_exists( 'trav_acc_vacancy_delete_action' ) ) {
	function trav_acc_vacancy_delete_action() {

		global $wpdb;
		// data validation
		if ( empty( $_REQUEST['vacancy_id'] ) ) {
			print __('Sorry, you tried to remove nothing.', 'trav');
			exit;
		}

		// nonce check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'vacancy_delete' ) ) {
			print __('Sorry, your nonce did not verify.', 'trav');
			exit;
		}

		// check ownership if user is not admin
		if ( ! current_user_can( 'manage_options' ) ) {
			$sql = sprintf( 'SELECT Trav_Vacancies.accommodation_id FROM %1$s as Trav_Vacancies WHERE Trav_Vacancies.id = %2$d' , TRAV_ACCOMMODATION_VACANCIES_TABLE, $_REQUEST['vacancy_id'] );
			$acc_id = $wpdb->get_var( $sql );
			$post_author_id = get_post_field( 'post_author', $acc_id );
			if ( get_current_user_id() != $post_author_id ) {
				print __('You don\'t have permission to remove other\'s item.', 'trav');
				exit;
			}
		}

		// do action
		$wpdb->delete( TRAV_ACCOMMODATION_VACANCIES_TABLE, array( 'id' => $_REQUEST['vacancy_id'] ) );
		//}
		wp_redirect( admin_url( 'edit.php?post_type=accommodation&page=vacancies') );
		exit;
	}
}

/*
 * vacancy save action
 */
if ( ! function_exists( 'trav_acc_vacancy_save_action' ) ) {
	function trav_acc_vacancy_save_action() {

		if ( ! isset( $_POST['vacancy_save'] ) || ! wp_verify_nonce( $_POST['vacancy_save'], 'trav_acc_vacancy_manage' ) ) {
		   print __('Sorry, your nonce did not verify.', 'trav');
		   exit;
		} else {

			global $wpdb;

			$default_vacancy_data = array(
				'accommodation_id'  => '',
				'room_type_id'      => '',
        'treatment_id'      => NULL,
				'rooms'             => 0,
				'date_from'         => date( 'Y-m-d' ),
				'date_to'           => '9999-12-31',
				'price_per_room'    => 0,
				'price_per_person'  => 0,
				'checkin_days'  => "1111111",
				'checkout_days'  => "1111111",
        'minimum_stay' => NULL,
        'maximum_stay' => NULL,
        'child_price' => NULL,
        'advanced_price' => NULL,
        'minimum_adults' => NULL,
        'maximum_adults' => NULL,
        'minimum_kids' => NULL,
        'maximum_kids' => NULL
			);

			$table_fields = array( 'date_from', 'date_to', 'accommodation_id', 'room_type_id', 'treatment_id', 'rooms', 'price_per_room', 'price_per_person', 'minimum_stay', 'maximum_stay', 'minimum_adults', 'maximum_adults', 'minimum_kids', 'maximum_kids' );
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}


			if ( ! empty( $_POST['child_cost_yn'] ) ) {
        $child_price_data = $_POST['child_price'];
        foreach ($child_price_data as $key => $value) {
          // filter out empty rows
          $child_price_data[$key] = array_filter($value, function($v){
            return ($v[0] != "" && $v[1] != "" && $v[0] <= $v[1]);
          });
          // sort by min age
          usort($child_price_data[$key], function($a, $b) { return $a[0] - $b[0]; });
        }
        $child_price_data = array_filter($child_price_data, function($v){
          return !empty($v);
        });

        if(empty($child_price_data)){
          $data['child_price'] = NULL;
        } else {
          $data['child_price'] = serialize( $child_price_data );
        }
			}

			if ( ! empty( $_POST['advanced_cost_yn'] ) ) {
        $advanced_price_data = $_POST['advanced_price'];
        $advanced_price_data = array_filter($advanced_price_data, function($v){
          return !empty($v);
        });
        if(empty($advanced_price_data)){
          $data['advanced_price'] = NULL;
        } else {
          $data['price_per_person'] = $advanced_price_data[0];
          $data['advanced_price'] = serialize( $advanced_price_data );
        }
			}

      $data['checkin_days'] = "";
      if(!empty($_POST["checkin_days"])){
        for($i = 0; $i < 7; $i++){
          if($_POST["checkin_days"][$i]){
            $data['checkin_days'] .= "1";
          } else {
            $data['checkin_days'] .= "0";
          }
        }
      }
      $data['checkout_days'] = "";
      if(!empty($_POST["checkout_days"])){
        for($i = 0; $i < 7; $i++){
          if($_POST["checkout_days"][$i]){
            $data['checkout_days'] .= "1";
          } else {
            $data['checkout_days'] .= "0";
          }
        }
      }



			$data = array_replace( $default_vacancy_data, $data );
			$data['accommodation_id'] = trav_acc_org_id( $data['accommodation_id'] );
			$data['room_type_id'] = trav_room_org_id( $data['room_type_id'] );
			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( TRAV_ACCOMMODATION_VACANCIES_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_ACCOMMODATION_VACANCIES_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}
			wp_redirect( admin_url( 'edit.php?post_type=accommodation&page=vacancies&action=edit&vacancy_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * vacancy admin enqueue script action
 */
if ( ! function_exists( 'trav_acc_vacancy_admin_enqueue_scripts' ) ) {
	function trav_acc_vacancy_admin_enqueue_scripts() {

		// support select2
		wp_enqueue_style( 'rwmb_select2', RWMB_URL . 'css/select2/select2.css', array(), '3.2' );
		wp_enqueue_script( 'rwmb_select2', RWMB_URL . 'js/select2/select2.min.js', array(), '3.2', true );

		// datepicker
		$url = RWMB_URL . 'css/jqueryui';
		wp_register_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
		wp_register_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );
		wp_enqueue_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );

		// Load localized scripts
		$locale = str_replace( '_', '-', get_locale() );
		$file_path = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js';
		$deps = array( 'jquery-ui-datepicker' );
		if ( file_exists( RWMB_DIR . 'js/' . $file_path ) )
		{
			wp_register_script( 'jquery-ui-datepicker-i18n', RWMB_URL . 'js/' . $file_path, $deps, '1.8.17', true );
			$deps[] = 'jquery-ui-datepicker-i18n';
		}

		wp_enqueue_script( 'rwmb-date', RWMB_URL . 'js/' . 'date.js', $deps, RWMB_VER, true );
		wp_localize_script( 'rwmb-date', 'RWMB_Datepicker', array( 'lang' => $locale ) );

		// custom style and js
		wp_enqueue_style( 'trav_admin_acc_style' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/css/style.css' );
		wp_enqueue_script( 'trav_admin_acc_script' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/accommodation/js/script.js', array('jquery'), '1.0', true );
		wp_enqueue_script( 'mt_admin_children_script' , MT_TEMPLATE_DIRECTORY_URI . '/inc/admin/accommodation/js/children.js', array('jquery'), '1.0', true );
    wp_enqueue_style( 'mt_admin_acc_style' , MT_TEMPLATE_DIRECTORY_URI . '/inc/admin/css/style.css' );

	}
}

add_action( 'admin_menu', 'trav_acc_vacancy_add_menu_items' );
