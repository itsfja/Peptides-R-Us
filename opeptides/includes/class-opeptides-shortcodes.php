<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Opeptides_Shortcodes {

	public function __construct() {
		add_shortcode( 'opeptide_disclaimer', array( $this, 'sc_disclaimer' ) );
		add_shortcode( 'opeptide_encyclopaedia', array( $this, 'sc_encyclopaedia' ) );
		add_shortcode( 'opeptide_calc', array( $this, 'sc_calc' ) );
		add_shortcode( 'opeptide_diary', array( $this, 'sc_diary' ) );
	}

	private function settings() {
		return Opeptides::instance()->settings;
	}

	public function render_disclaimer() {
		$text = $this->settings()->get( 'disclaimer_text' );
		return '<div class="opeptides-disclaimer" role="note">'
			. '<span class="opeptides-disclaimer-icon">&#9888;</span> '
			. '<strong>Liability notice:</strong> ' . esc_html( $text )
			. '</div>';
	}

	public function sc_disclaimer() {
		return $this->render_disclaimer();
	}

	public function sc_encyclopaedia( $atts ) {
		$atts = shortcode_atts( array( 'search' => '' ), $atts, 'opeptide_encyclopaedia' );
		if ( ! $atts['search'] && isset( $_GET['opeptide_q'] ) ) {
			$atts['search'] = sanitize_text_field( wp_unslash( $_GET['opeptide_q'] ) );
		}
		wp_enqueue_style( 'opeptides' );

		$args = array(
			'post_type'      => Opeptides_CPT::POST_TYPE,
			'posts_per_page'=> 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			's'              => $atts['search'],
		);
		$query   = new WP_Query( $args );
		$terms   = get_terms( array( 'taxonomy' => Opeptides_CPT::TAXONOMY, 'hide_empty' => false ) );

		$out = '<div class="opeptides-encyclopaedia">';
		$out .= '<form class="opeptides-search" method="get"><input type="search" name="opeptide_q" placeholder="Search peptides&hellip;" value="' . esc_attr( $atts['search'] ) . '" /><button type="submit" class="button">Search</button></form>';

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$out .= '<div class="opeptides-cats">';
			foreach ( $terms as $term ) {
				$out .= '<a class="opeptides-cat" href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a> ';
			}
			$out .= '</div>';
		}

		if ( $query->have_posts() ) {
			$out .= '<div class="opeptides-grid">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$meta = array();
				foreach ( array( 'formula', 'weight', 'half_life', 'reconstitution', 'storage', 'typical_dose', 'side_effects', 'pen_dial' ) as $key ) {
					$value = get_post_meta( get_the_ID(), '_opeptide_' . $key, true );
					if ( $value ) {
						$meta[ $key ] = $value;
					}
				}
				$out .= '<div class="opeptides-card">';
				$out .= '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
				if ( has_excerpt() ) {
					$out .= '<p>' . esc_html( get_the_excerpt() ) . '</p>';
				}
				if ( $meta ) {
					$out .= '<dl class="opeptides-meta">';
					$labels = array(
						'formula'        => 'Formula',
						'weight'         => 'MW',
						'half_life'      => 'Half-life',
						'reconstitution' => 'Reconstitution',
						'storage'        => 'Storage',
						'typical_dose'   => 'Reported dose',
						'side_effects'   => 'Side effects',
						'pen_dial'       => 'Convipen dial',
					);
					foreach ( $meta as $key => $value ) {
						$out .= '<dt>' . esc_html( $labels[ $key ] ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
					}
					$out .= '</dl>';
				}
				$out .= '</div>';
			}
			wp_reset_postdata();
			$out .= '</div>';
		} else {
			$out .= '<p>No peptides found. Add some under Peptides in the admin, or re-activate the plugin to load the starter set.</p>';
		}
		$out .= '</div>';
		return $out;
	}

	public function sc_calc( $atts ) {
		wp_enqueue_style( 'opeptides' );
		wp_enqueue_script( 'opeptides' );
		$cartridge = $this->settings()->get( 'cartridge_volume' );
		ob_start();
		?>
		<div class="opeptides-calc" data-cartridge="<?php echo esc_attr( $cartridge ); ?>">
			<h3>Convipen 3ml Cartridge Calculator</h3>
			<div class="opeptides-calc-form">
				<label>Peptide mass in cartridge/vial (mg)
					<input type="number" id="opep-mass" step="0.1" min="0" value="5" />
				</label>
				<label>Total solution volume (ml) <em>(cartridge holds <?php echo esc_html( $cartridge ); ?> ml)</em>
					<input type="number" id="opep-volume" step="0.1" min="0" value="3" />
				</label>
				<label>Target dose (mcg)
					<input type="number" id="opep-dose" step="10" min="0" value="250" />
				</label>
				<label>Doses per day
					<input type="number" id="opep-freq" step="1" min="1" value="1" />
				</label>
				<button type="button" class="button" id="opep-calc-run">Calculate</button>
			</div>
			<div class="opeptides-calc-results" id="opep-calc-results" style="display:none;">
				<table class="opeptides-table">
					<tbody>
						<tr><th>Concentration</th><td id="opep-r-conc">&nbsp;</td></tr>
						<tr><th>Volume per dose</th><td id="opep-r-vol">&nbsp;</td></tr>
						<tr><th>Cartridge lasts</th><td id="opep-r-days">&nbsp;</td></tr>
						<tr><th>Equivalent dose at 0.1 ml</th><td id="opep-r-01">&nbsp;</td></tr>
					</tbody>
				</table>
				<p class="opeptides-calc-note" id="opep-r-note"></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function sc_diary() {
		wp_enqueue_style( 'opeptides' );
		wp_enqueue_script( 'opeptides' );

		if ( ! is_user_logged_in() ) {
			return '<div class="opeptides-diary"><p>Please <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">log in</a> to use your peptide diary. Each user\'s diary is private.</p></div>';
		}

		$user_id  = get_current_user_id();
		$diary    = Opeptides::instance()->diary;
		$stock    = $diary->get_entries( $user_id, 'stock' );
		$logs     = $diary->get_entries( $user_id, 'log' );
		$cycles   = $diary->get_entries( $user_id, 'cycle' );
		$total_ml = $diary->total_stock_ml( $user_id );
		$templates = $this->settings()->get( 'cycle_templates' );

		ob_start();
		?>
		<div class="opeptides-diary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'opeptides_nonce' ) ); ?>">
			<h3>My Stock</h3>
			<p class="opeptides-stock-total">Total reconstituted volume on hand: <strong id="opep-stock-total"><?php echo esc_html( $total_ml ); ?> ml</strong></p>
			<form class="opeptides-form" id="opep-stock-form">
				<input type="hidden" name="entry_type" value="stock" />
				<input type="text" name="peptide" placeholder="Peptide (e.g. BPC-157)" required />
				<input type="number" name="volume_ml" step="0.01" min="0" placeholder="Volume (ml)" required />
				<input type="date" name="entry_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" />
				<button type="submit" class="button">Add stock</button>
			</form>
			<table class="opeptides-table" id="opep-stock-table">
				<thead><tr><th>Peptide</th><th>Volume (ml)</th><th>Date</th><th>&nbsp;</th></tr></thead>
				<tbody>
				<?php foreach ( $stock as $row ) : ?>
					<tr data-id="<?php echo esc_attr( $row->id ); ?>">
						<td><?php echo esc_html( $row->peptide ); ?></td>
						<td><?php echo esc_html( $row->volume_ml ); ?></td>
						<td><?php echo esc_html( $row->entry_date ); ?></td>
						<td><button type="button" class="button opep-delete">&times;</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h3>Dose Log</h3>
			<form class="opeptides-form" id="opep-log-form">
				<input type="hidden" name="entry_type" value="log" />
				<input type="text" name="peptide" placeholder="Peptide" required />
				<input type="number" name="dose_mcg" step="1" min="0" placeholder="Dose (mcg)" required />
				<input type="number" name="volume_ml" step="0.01" min="0" placeholder="Volume injected (ml)" />
				<input type="date" name="entry_date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" />
				<input type="text" name="note" placeholder="Notes (site, effects...)" />
				<button type="submit" class="button">Log dose</button>
			</form>
			<table class="opeptides-table" id="opep-log-table">
				<thead><tr><th>Date</th><th>Peptide</th><th>Dose (mcg)</th><th>Volume (ml)</th><th>Note</th><th>&nbsp;</th></tr></thead>
				<tbody>
				<?php foreach ( $logs as $row ) : ?>
					<tr data-id="<?php echo esc_attr( $row->id ); ?>">
						<td><?php echo esc_html( $row->entry_date ); ?></td>
						<td><?php echo esc_html( $row->peptide ); ?></td>
						<td><?php echo esc_html( $row->dose_mcg ); ?></td>
						<td><?php echo esc_html( $row->volume_ml ); ?></td>
						<td><?php echo esc_html( $row->note ); ?></td>
						<td><button type="button" class="button opep-delete">&times;</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h3>My Cycles</h3>
			<ul class="opeptides-cycles">
			<?php foreach ( $cycles as $row ) : ?>
				<li><strong><?php echo esc_html( $row->peptide ); ?></strong> (started <?php echo esc_html( $row->entry_date ); ?>)<br /><?php echo esc_html( $row->note ); ?></li>
			<?php endforeach; ?>
			</ul>

			<h3>Suggested Cycles</h3>
			<div class="opeptides-templates">
			<?php foreach ( $templates as $tpl ) : ?>
				<div class="opeptides-template">
					<strong><?php echo esc_html( $tpl['name'] ); ?></strong>
					<p><?php echo esc_html( $tpl['description'] ); ?></p>
					<button type="button" class="button opep-start-cycle"
						data-name="<?php echo esc_attr( $tpl['name'] ); ?>"
						data-description="<?php echo esc_attr( $tpl['description'] ); ?>">Start this cycle</button>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
