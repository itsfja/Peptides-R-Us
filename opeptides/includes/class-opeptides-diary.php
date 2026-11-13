<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Opeptides_Diary {

	public function __construct() {
		add_action( 'wp_ajax_opeptides_add_entry', array( $this, 'ajax_add_entry' ) );
		add_action( 'wp_ajax_opeptides_delete_entry', array( $this, 'ajax_delete_entry' ) );
		add_action( 'wp_ajax_opeptides_start_cycle', array( $this, 'ajax_start_cycle' ) );
	}

	public function create_table() {
		global $wpdb;
		$table   = $wpdb->prefix . 'opeptides_diary';
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( "CREATE TABLE $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			entry_type VARCHAR(20) NOT NULL DEFAULT 'log',
			peptide VARCHAR(191) NOT NULL DEFAULT '',
			volume_ml DECIMAL(10,3) NOT NULL DEFAULT 0,
			dose_mcg DECIMAL(10,2) NOT NULL DEFAULT 0,
			note TEXT NULL,
			entry_date DATE NOT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY entry_date (entry_date)
		) $charset;" );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'opeptides_diary';
	}

	public function get_entries( $user_id, $entry_type = null ) {
		global $wpdb;
		$table = self::table();
		if ( $entry_type ) {
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM $table WHERE user_id = %d AND entry_type = %s ORDER BY entry_date DESC, id DESC",
				$user_id, $entry_type
			) );
		}
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE user_id = %d ORDER BY entry_date DESC, id DESC",
			$user_id
		) );
	}

	public function total_stock_ml( $user_id ) {
		global $wpdb;
		$table = self::table();
		return (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(volume_ml),0) FROM $table WHERE user_id = %d AND entry_type = 'stock'",
			$user_id
		) );
	}

	private function verify() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'You must be logged in.' ), 403 );
		}
		if ( ! check_ajax_referer( 'opeptides_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce.' ), 403 );
		}
	}

	public function ajax_add_entry() {
		$this->verify();
		global $wpdb;
		$user_id     = get_current_user_id();
		$entry_type  = isset( $_POST['entry_type'] ) ? sanitize_key( $_POST['entry_type'] ) : 'log';
		if ( ! in_array( $entry_type, array( 'stock', 'log' ), true ) ) {
			$entry_type = 'log';
		}
		$peptide  = isset( $_POST['peptide'] ) ? sanitize_text_field( wp_unslash( $_POST['peptide'] ) ) : '';
		$note     = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
		$volume   = isset( $_POST['volume_ml'] ) ? (float) $_POST['volume_ml'] : 0;
		$dose     = isset( $_POST['dose_mcg'] ) ? (float) $_POST['dose_mcg'] : 0;
		$date     = isset( $_POST['entry_date'] ) ? sanitize_text_field( $_POST['entry_date'] ) : '';
		if ( ! $peptide ) {
			wp_send_json_error( array( 'message' => 'Peptide name is required.' ), 400 );
		}
		if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date = current_time( 'Y-m-d' );
		}
		$wpdb->insert( self::table(), array(
			'user_id'    => $user_id,
			'entry_type' => $entry_type,
			'peptide'    => $peptide,
			'volume_ml'  => $volume,
			'dose_mcg'   => $dose,
			'note'       => $note,
			'entry_date' => $date,
			'created_at' => current_time( 'mysql' ),
		) );
		wp_send_json_success( array( 'message' => 'Entry saved.' ) );
	}

	public function ajax_delete_entry() {
		$this->verify();
		global $wpdb;
		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$user_id = get_current_user_id();
		$wpdb->delete( self::table(), array( 'id' => $id, 'user_id' => $user_id ) );
		wp_send_json_success( array( 'message' => 'Entry deleted.' ) );
	}

	public function ajax_start_cycle() {
		$this->verify();
		global $wpdb;
		$user_id = get_current_user_id();
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$desc    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		if ( ! $name ) {
			wp_send_json_error( array( 'message' => 'Cycle name required.' ), 400 );
		}
		$wpdb->delete( self::table(), array( 'user_id' => $user_id, 'entry_type' => 'cycle', 'peptide' => $name ) );
		$wpdb->insert( self::table(), array(
			'user_id'    => $user_id,
			'entry_type' => 'cycle',
			'peptide'    => $name,
			'note'       => $desc,
			'entry_date' => current_time( 'Y-m-d' ),
			'created_at' => current_time( 'mysql' ),
		) );
		wp_send_json_success( array( 'message' => 'Cycle started.' ) );
	}
}
