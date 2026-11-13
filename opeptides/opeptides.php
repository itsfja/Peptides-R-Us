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
		wp_register_style( 'opeptides', OPEPTIDES_URL . 'assets/opeptides.css', array(), $css_version );
		wp_register_script( 'opeptides', OPEPTIDES_URL . 'assets/opeptides.js', array( 'jquery' ), OPEPTIDES_VERSION, true );
		wp_localize_script( 'opeptides', 'opeptidesData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'opeptides_nonce' ),
		) );
	}

	/**
	 * Show the liability header on any page that contains our shortcodes
	 * (or everywhere, if the sitewide option is on).
	 */
	public function inject_disclaimer( $content ) {
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
