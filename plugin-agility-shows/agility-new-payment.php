<?php

/*
 Plugin Name: WP_List_Table Class Example
Plugin URI: http://sitepoint.com
Description: Demo on how WP_List_Table Class works
Version: 1.0
Author: Agbonghama Collins
Author URI:  http://w3guy.com
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Results_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
				'singular' => __( 'Entry', 'sp' ), //singular name of the listed records
				'plural'   => __( 'Entries', 'sp' ), //plural name of the listed records
				'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_records($user_id) {

		global $wpdb;

		$sql = "select t1.ID, t1.post_date as date_entered, t3.post_name as show_name, t4.meta_value as total_owed, t5.meta_value as payments 
				from {$wpdb->prefix}posts t1 inner join {$wpdb->prefix}postmeta t2 on t1.ID=t2.post_id 
				inner join {$wpdb->prefix}posts t3 on t2.meta_value=t3.ID 
				inner join {$wpdb->prefix}postmeta t4 on t1.ID=t4.post_id 
				left outer join (
					select post_id, meta_value from {$wpdb->prefix}postmeta where meta_key='paid-pm'
				) as t5 on t1.ID=t5.post_id 
				where t1.post_author=".$user_id." and t1.post_status='publish' and t2.meta_key='show_id-pm' and t4.meta_key='total_cost-pm'";
		
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}
	



	public function get_user_id_for_ref($user_ref){
	
		global $wpdb;
	
		$sql = "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key='user_ref' and meta_value='".$user_ref."'";
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		if (count($result) == 0){
			return 0;
		}
	
		return $result[0]['user_id'];
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
				"{$wpdb->prefix}customers",
				[ 'ID' => $id ],
				[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}customers";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No results avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'date_entered':
				return $item[ $column_name ];
			case 'show_name':
				return ucwords(str_replace('-', ' ', $item[ $column_name ]));
			case 'total_owed':
				if (!isset($item['paid-pm'])){
					return '&pound;'.sprintf("%.2f", $item['total_owed']);
				}
				return print_r( $item, true );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
				'<input type="checkbox" name="bulk-assign[]" value="%s" />', $item['ID']
				);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$assign_nonce = wp_create_nonce( 'assign_payment' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
//				'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
				'assign' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Assign Payment</a>', esc_attr( $_REQUEST['page'] ), 'assign', absint( $item['ID'] ), $assign_nonce ),
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
				'cb'      => '<input type="checkbox" />',
				'date_entered' => __( 'Date Entered', 'sp' ),
				'show_name'    => __( 'Show', 'sp' ),
				'total_owed'    => __( 'Amount Owed', 'sp' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
//	public function get_sortable_columns() {
//		$sortable_columns = array(
//				'name' => array( 'name', true ),
//				'city' => array( 'city', false )
//		);
//
//		return $sortable_columns;
//	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
				'bulk-assign' => 'Assign Payment'
		];

		return $actions;
	}
	
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items($user_id) {

		$this->_column_headers = $this->get_column_info();
		$total_items  = self::record_count();
		$this->items = self::get_records( $user_id );
	}


	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_customer( absint( $_GET['customer'] ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-assign' )
				|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-assign' )
				) {

					$ids = esc_sql( $_POST['bulk-assign'] );

					// loop over the array of record IDs and delete them
					foreach ( $ids as $id ) {
						//self::delete_customer( $id );

					}

					// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
					// add_query_arg() return the current url
					wp_redirect( esc_url_raw(add_query_arg()) );
					exit;
				}
	}

}


class New_Payment {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $results_obj;

	// class constructor
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public function plugin_menu() {

		add_submenu_page(
				'agility_payments',
				'Add New Payment',
				'Add New',
				'manage_options',
				'new_agility_payment',
				[ $this, 'new_payment_menu' ]
				);

		$this->results_obj = new Results_List();
	
	}


	/**
	 * Plugin settings page
	 */
	public function new_payment_menu(){
		?>
		<div class="wrap">
			<h2>Add New Payment</h2>
			<pre><?php print_r($_POST); ?></pre>
	
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<form method="post">
							<label for="user_ref_search" style="font-size:14px;font-weight:600;vertical-align:middle;">Enter a user ref to search for:</label></strong> 
							<input name="user_ref_search" type="text" id="user_ref_search" value="<?php echo $_REQUEST['user_ref_search']; ?>" class="regular-text"> 
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Go">
							<?php if (isset($_REQUEST['user_ref_search']) && $_REQUEST[''] != 'user_ref_search'){
								echo '<h4>Results</h4>';
								//$this->results_obj->display();
								$user_id = $this->results_obj->get_user_id_for_ref($_REQUEST['user_ref_search']);
								if ($user_id == 0){
									echo '<p>No user can be found with that reference, please try again.</p>';
								}
								else{
									$this->results_obj->prepare_items($user_id);
									$this->results_obj->display();
								}
							} ?>
						</form>
					</div>
				</div>
			<br class="clear">
			</div>						
		</div>
	<?php		
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
	New_Payment::get_instance();
} );
