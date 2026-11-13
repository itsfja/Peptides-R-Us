<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Opeptides_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
	}

	public function menu() {
		add_submenu_page(
			'edit.php?post_type=opeptide',
			'Opeptides Settings',
			'Settings',
			'manage_options',
			'opeptides-settings',
			array( $this, 'render' )
		);
	}

	public function register() {
		register_setting( 'opeptides', 'opeptides_disclaimer_text' );
		register_setting( 'opeptides', 'opeptides_sitewide_disclaimer', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'absint',
		) );
		register_setting( 'opeptides', 'opeptides_cartridge_volume', array(
			'type'              => 'float',
			'sanitize_callback' => 'floatval',
		) );
		register_setting( 'opeptides', 'opeptides_cycle_templates', array(
			'sanitize_callback' => array( $this, 'sanitize_templates' ),
		) );
	}

	public function sanitize_templates( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}
		$clean = array();
		foreach ( $input as $tpl ) {
			$name = isset( $tpl['name'] ) ? sanitize_text_field( $tpl['name'] ) : '';
			$desc = isset( $tpl['description'] ) ? sanitize_textarea_field( $tpl['description'] ) : '';
			if ( $name ) {
				$clean[] = array( 'name' => $name, 'description' => $desc );
			}
		}
		return $clean;
	}

	public function defaults() {
		return array(
			'disclaimer_text'   => 'This site is for internal educational and research reference only. Nothing here is medical advice, and no content should be taken as encouragement to use any substance. Don’t do drugs. If you ignore that, that’s on you — the authors accept no liability whatsoever.',
			'sitewide'           => 0,
			'cartridge_volume'   => 3.0,
			'cycle_templates'    => array(
				array(
					'name'        => 'BPC-157 — 4 week gut/joint protocol',
					'description' => "250 mcg once daily, subcutaneous, 4 weeks on / 2 weeks off. Log injection site rotation daily.",
				),
				array(
					'name'        => 'TB-500 — 6 week recovery protocol',
					'description' => "2.0 mg twice weekly for 4-6 weeks, then 2.0 mg weekly as maintenance.",
				),
				array(
					'name'        => 'CJC-1295 / Ipamorelin — 12 week night-time protocol',
					'description' => "100 mcg of each, combined in one syringe, before bed nightly. Monitor sleep and morning fasted state.",
				),
				array(
					'name'        => 'GHK-Cu — 8 week skin/hair protocol',
					'description' => "Topical or subcutaneous 2-3 mg daily; track skin condition weekly in the diary.",
				),
			),
		);
	}

	public function get( $key ) {
		$defaults = $this->defaults();
		switch ( $key ) {
			case 'disclaimer_text':
				return get_option( 'opeptides_disclaimer_text', $defaults['disclaimer_text'] );
			case 'sitewide':
				return (bool) get_option( 'opeptides_sitewide_disclaimer', $defaults['sitewide'] );
			case 'cartridge_volume':
				return (float) get_option( 'opeptides_cartridge_volume', $defaults['cartridge_volume'] );
			case 'cycle_templates':
				$templates = get_option( 'opeptides_cycle_templates', $defaults['cycle_templates'] );
				return is_array( $templates ) ? $templates : $defaults['cycle_templates'];
		}
		return null;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Nope.' );
		}
		?>
		<div class="wrap">
			<h1>Opeptides Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'opeptides' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">Disclaimer text</th>
						<td>
							<textarea name="opeptides_disclaimer_text" rows="4" class="large-text"><?php echo esc_textarea( $this->get( 'disclaimer_text' ) ); ?></textarea>
							<p class="description">Shown at the top of every page containing Opeptides shortcodes, or sitewide if enabled below.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Sitewide disclaimer</th>
						<td><label><input type="checkbox" name="opeptides_sitewide_disclaimer" value="1" <?php checked( $this->get( 'sitewide' ) ); ?> /> Inject the disclaimer into every page and post on this site</label></td>
					</tr>
					<tr>
						<th scope="row">Cartridge volume (ml)</th>
						<td><input type="number" step="0.1" min="0.1" name="opeptides_cartridge_volume" value="<?php echo esc_attr( $this->get( 'cartridge_volume' ) ); ?>" /></td>
					</tr>
				</table>
				<h2>Suggested cycle templates</h2>
				<p class="description">These appear on the diary page as suggested protocols. Edit freely.</p>
				<table class="widefat striped" id="opeptides-templates">
					<thead><tr><th>Name</th><th>Description / protocol</th><th>&nbsp;</th></tr></thead>
					<tbody>
					<?php foreach ( $this->get( 'cycle_templates' ) as $i => $tpl ) : ?>
						<tr>
							<td><input type="text" class="regular-text" name="opeptides_cycle_templates[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $tpl['name'] ); ?>" /></td>
							<td><textarea rows="2" class="large-text" name="opeptides_cycle_templates[<?php echo esc_attr( $i ); ?>][description]"><?php echo esc_textarea( $tpl['description'] ); ?></textarea></td>
							<td><button type="button" class="button opeptides-remove-template">Remove</button></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p><button type="button" class="button button-secondary" id="opeptides-add-template">Add template</button></p>
				<?php submit_button(); ?>
			</form>
		</div>
		<script>
		jQuery(function($){
			var idx = 100;
			$('#opeptides-add-template').on('click', function(){
				idx++;
				$('#opeptides-templates tbody').append(
					'<tr><td><input type="text" class="regular-text" name="opeptides_cycle_templates[' + idx + '][name]" value="" /></td>' +
					'<td><textarea rows="2" class="large-text" name="opeptides_cycle_templates[' + idx + '][description]"></textarea></td>' +
					'<td><button type="button" class="button opeptides-remove-template">Remove</button></td></tr>'
				);
			});
			$(document).on('click', '.opeptides-remove-template', function(){
				$(this).closest('tr').remove();
			});
		});
		</script>
		<?php
	}
}
