<?php
/**
 * Plugin Name: Opeptides
 * Plugin URI:  https://github.com/itsfja/Peptides-R-Us
 * Description: Internal peptide knowledge base: encyclopaedia, Convipen 3ml cartridge calculator, per-user stock &amp; cycle diary, and a liability disclaimer on every page.
 * Version:     1.0.0
 * Author:      itsfja
 * License:     MIT
 * Text Domain: opeptides
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OPEPTIDES_VERSION', '1.0.0' );
define( 'OPEPTIDES_FILE', __FILE__ );
define( 'OPEPTIDES_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPEPTIDES_URL', plugin_dir_url( __FILE__ ) );

require_once OPEPTIDES_DIR . 'includes/class-opeptides-cpt.php';
require_once OPEPTIDES_DIR . 'includes/class-opeptides-settings.php';
require_once OPEPTIDES_DIR . 'includes/class-opeptides-diary.php';
require_once OPEPTIDES_DIR . 'includes/class-opeptides-shortcodes.php';
require_once OPEPTIDES_DIR . 'includes/class-opeptides-seeder.php';

final class Opeptides {

	private static $instance = null;

	public $cpt;
	public $settings;
	public $diary;
	public $shortcodes;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->cpt        = new Opeptides_CPT();
		$this->settings   = new Opeptides_Settings();
		$this->diary      = new Opeptides_Diary();
		$this->shortcodes = new Opeptides_Shortcodes();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'the_content', array( $this, 'inject_disclaimer' ), 20 );
	}

	public function activate() {
		$this->cpt->register_post_type();
		$this->cpt->register_taxonomy();
		flush_rewrite_rules();
		$this->diary->create_table();
		Opeptides_Seeder::seed();
	}

	public function enqueue() {
		$css_version = file_exists( OPEPTIDES_DIR . 'assets/opeptides.css' ) ? (string) filemtime( OPEPTIDES_DIR . 'assets/opeptides.css' ) : OPEPTIDES_VERSION;
		$js_version = file_exists( OPEPTIDES_DIR . 'assets/opeptides.js' ) ? (string) filemtime( OPEPTIDES_DIR . 'assets/opeptides.js' ) : OPEPTIDES_VERSION;
		wp_register_style( 'opeptides', OPEPTIDES_URL . 'assets/opeptides.css', array(), $css_version );
		wp_register_script( 'opeptides', OPEPTIDES_URL . 'assets/opeptides.js', array( 'jquery' ), $js_version, true );
		wp_localize_script( 'opeptides', 'opeptidesData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'opeptides_nonce' ),
		) );
	}

	/**
	 * Show the liability header on any page that contains our shortcodes
	 * (or everywhere, if the sitewide option is on).
	 */
	/**
	 * Single peptide pages: append the data fields, attributed studies,
	 * bro-lore notes, and a research-use-only disclaimer.
	 */
	public function single_entry_sections( $content ) {
		if ( ! is_singular( Opeptides_CPT::POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		wp_enqueue_style( 'opeptides' );
		$post_id = get_the_ID();

		$labels = array(
			'formula'        => 'Formula',
			'weight'        => 'Molecular weight',
			'half_life'      => 'Half-life',
			'reconstitution' => 'Reconstitution',
			'storage'        => 'Storage',
			'typical_dose'   => 'Reported dose',
			'side_effects'   => 'Side effects',
			'pen_dial'       => 'Convipen dial',
		);
		$rows = '';
		foreach ( $labels as $key => $label ) {
			$value = get_post_meta( $post_id, '_opeptide_' . $key, true );
			if ( $value ) {
				$rows .= '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
			}
		}
		if ( $rows ) {
			$content .= '<h3>Peptide data</h3><table class="opeptides-table opeptides-single-meta"><tbody>' . $rows . '</tbody></table>';
		}

		$studies_json = get_post_meta( $post_id, '_opeptide_studies', true );
		$studies = $studies_json ? json_decode( $studies_json, true ) : array();
		if ( is_array( $studies ) && ! empty( $studies ) ) {
			$quality_labels = array(
				'peer-reviewed'                              => 'Peer-reviewed human study',
				'animal/preclinical'                         => 'Animal / preclinical study',
				'RU literature (limited replication)'        => 'Russian literature — unreplicated, treat as unreliable',
				'bro-lore (anecdotal)'                       => 'Bro-lore — anecdotal, no evidence base',
			);
			$content .= '<h3>Studies &amp; evidence</h3><ul class="opeptides-studies">';
			foreach ( $studies as $study ) {
				$q = isset( $study['quality'] ) ? $study['quality'] : '';
				$ql = isset( $quality_labels[ $q ] ) ? $quality_labels[ $q ] : $q;
				$content .= '<li>';
				$content .= '<span class="opeptides-study-quality opeptides-quality-' . esc_attr( md5( $q ) ) . '">' . esc_html( $ql ) . '</span> ';
				$content .= '<strong>' . esc_html( isset( $study['citation'] ) ? $study['citation'] : '' ) . '</strong>';
				if ( ! empty( $study['finding'] ) ) {
					$content .= '<br />' . esc_html( $study['finding'] );
				}
				$content .= '</li>';
			}
			$content .= '</ul>';
		} elseif ( ! $studies_json ) {
			$content .= '<h3>Studies &amp; evidence</h3><p class="opeptides-calc-note">No peer-reviewed human studies located for this compound. Treat all circulating protocols as unverified.</p>';
		}

		$bro = get_post_meta( $post_id, '_opeptide_bro_lore', true );
		if ( $bro ) {
			$content .= '<h3>Bro-lore</h3><p class="opeptides-bro-lore"><strong>Attributed as anecdotal:</strong> ' . esc_html( $bro ) . '</p>';
		}

		$content .= '<div class="opeptides-disclaimer opeptides-research-disclaimer" role="note">RESEARCH USE ONLY: this entry is for internal educational reference. It is not medical advice, not a recommendation to use any substance, and no dosing suggestion should be inferred. Don&rsquo;t do drugs.</div>';

		return $content;
	}

	public function inject_disclaimer( $content ) {
		if ( is_singular( Opeptides_CPT::POST_TYPE ) ) {
			// single peptide pages get the banner plus their own sections
			return $this->shortcodes->render_disclaimer() . $this->single_entry_sections( $content );
		}
		$sitewide = get_option( 'opeptides_sitewide_disclaimer', 0 );
		$has_shortcode = false;
		foreach ( array( 'opeptide_encyclopaedia', 'opeptide_calc', 'opeptide_diary', 'opeptide_disclaimer' ) as $tag ) {
			if ( has_shortcode( $content, $tag ) ) {
				$has_shortcode = true;
				break;
			}
		}
		if ( ! $sitewide && ! $has_shortcode ) {
			return $content;
		}
		if ( has_shortcode( $content, 'opeptide_disclaimer' ) ) {
			return $content; // already placed manually.
		}
		return $this->shortcodes->render_disclaimer() . $content;
	}
}

Opeptides::instance();
