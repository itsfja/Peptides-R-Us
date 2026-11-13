<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Opeptides_CPT {

	const POST_TYPE = 'opeptide';
	const TAXONOMY  = 'opeptide_category';

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
	}

	public function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels' => array(
				'name'          => 'Peptides',
				'singular_name' => 'Peptide',
				'add_new_item'  => 'Add New Peptide',
				'edit_item'     => 'Edit Peptide',
				'search_items'  => 'Search Peptides',
			),
			'public'       => true,
			'has_archive'  => 'peptides',
			'rewrite'      => array( 'slug' => 'peptide' ),
			'menu_icon'    => 'dashicons-flask',
			'supports'     => array( 'title', 'editor', 'excerpt', 'revisions' ),
			'show_in_rest' => true,
		) );
	}

	public function register_taxonomy() {
		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels' => array(
				'name'          => 'Peptide Categories',
				'singular_name' => 'Peptide Category',
			),
			'hierarchical' => true,
			'show_in_rest' => true,
		) );
		register_taxonomy_for_object_type( self::TAXONOMY, self::POST_TYPE );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'opeptide_details',
			'Peptide Data',
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'opeptide_meta', 'opeptide_meta_nonce' );
		$fields = $this->meta_fields();
		echo '<table class="form-table"><tbody>';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, '_opeptide_' . $key, true );
			printf(
				'<tr><th><label for="%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="%1$s" name="%1$s" value="%3$s" /></td></tr>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( $value )
			);
		}
		echo '</tbody></table>';
	}

	private function meta_fields() {
		return array(
			'formula'        => 'Molecular Formula',
			'weight'         => 'Molecular Weight (Da)',
			'half_life'      => 'Half-life',
			'reconstitution' => 'Reconstitution / Diluent',
			'storage'        => 'Storage',
			'typical_dose'   => 'Typical Dose (reported range)',
			'side_effects'   => 'Known Side Effects',
			'pen_dial'       => 'Convipen Dial Notes',
		);
	}

	public function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST['opeptide_meta_nonce'] ) || ! wp_verify_nonce( $_POST['opeptide_meta_nonce'], 'opeptide_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( $this->meta_fields() as $key => $label ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, '_opeptide_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}
	}
}
