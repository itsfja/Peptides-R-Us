<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Opeptides_Seeder {

	public static function seed() {
		$existing = get_posts( array(
			'post_type'      => Opeptides_CPT::POST_TYPE,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		) );
		if ( ! empty( $existing ) ) {
			return;
		}

		$peptides = array(
			array(
				'title'   => 'BPC-157',
				'excerpt' => 'Synthetic pentadecapeptide derived from a gastric juice protein, studied for tissue repair and gut healing in animal models.',
				'content' => "BPC-157 (Body Protection Compound-157) is a 15-amino-acid fragment studied extensively in rodents for its effects on wound healing, tendon-to-bone repair, and gastrointestinal protection. Human data remains limited and largely anecdotal.\n\nReported observations in the informal literature include accelerated healing of ligaments, tendons and gut lining, and reduced NSAID-induced damage in animal studies. It is stable in gastric acid, which is why oral and subcutaneous routes are both discussed.\n\nThis entry is informational only. Do not interpret it as a recommendation.",
				'meta'    => array(
					'formula'        => 'C62H98N16O22',
					'weight'         => '1419.5 Da',
					'half_life'      => 'Minutes to a few hours (subcutaneous, estimated)',
					'reconstitution' => 'Bacteriostatic water; 2ml per 5mg vial is common',
					'storage'        => 'Lyophilised: refrigerated. Reconstituted: 2-8°C, use within ~4 weeks',
					'typical_dose'   => '250-500 mcg daily (reported, unverified)',
					'side_effects'   => 'Rarely reported: mild fatigue, headache. Long-term human safety unknown',
					'pen_dial'       => 'At 5mg/3ml the concentration is ~1667 mcg/ml; 250 mcg ≈ 0.15 ml',
				),
				'category' => 'Healing / Recovery',
			),
			array(
				'title'   => 'TB-500 (Thymosin Beta-4 fragment)',
				'excerpt' => 'Fragment of thymosin beta-4 studied for cell migration, tissue repair and inflammation reduction.',
				'content' => "TB-500 is the active region of thymosin beta-4 (Ac-SDKP sequence context), a peptide involved in actin regulation, cell migration and wound repair. Animal studies show promise for cardiac repair and dermal healing.\n\nHuman clinical data is minimal. Commonly discussed in equine medicine contexts, which is where much of the dosing folklore originates.\n\nThis entry is informational only.",
				'meta'    => array(
					'formula'        => 'C12H35N5O6 (fragment) — full Tb4: C212H350N62O67S',
					'weight'         => '~4963 Da (full thymosin beta-4)',
					'half_life'      => 'Hours (estimated)',
					'reconstitution' => 'Bacteriostatic water; 1-2ml per vial',
					'storage'        => 'Refrigerated once reconstituted',
					'typical_dose'   => '2.0-4.0 mg per week, split (reported, unverified)',
					'side_effects'   => 'Occasionally reported: injection site redness, transient lethargy',
					'pen_dial'       => 'At 10mg/3ml the concentration is ~3333 mcg/ml; 2mg ≈ 0.6 ml',
				),
				'category' => 'Healing / Recovery',
			),
			array(
				'title'   => 'Ipamorelin',
				'excerpt' => 'Selective growth hormone secretagogue (GHS) with minimal effect on other pituitary hormones.',
				'content' => "Ipamorelin is a pentapeptide growth hormone releasing peptide (GHRP) that stimulates GH release via the ghrelin receptor with high selectivity — it shows little to no effect on cortisol, prolactin or ACTH at studied doses, unlike older GHRPs such as GHRP-6.\n\nOften discussed alongside CJC-1295 for a combined GH pulse. Human clinical data exists for Ipamorelin (it has been in Phase II trials for post-surgical ileus) but chronic-use safety data does not.\n\nThis entry is informational only.",
				'meta'    => array(
					'formula'        => 'C38H49N9O5',
					'weight'         => '711.9 Da',
					'half_life'      => '~2 hours',
					'reconstitution' => 'Bacteriostatic water; 2ml per 5mg vial typical',
					'storage'        => 'Refrigerated once reconstituted',
					'typical_dose'   => '100-300 mcg per dose, 1-3x daily (reported, unverified)',
					'side_effects'   => 'Possible water retention, mild numbness of hands, hunger modulation',
					'pen_dial'       => 'At 5mg/3ml the concentration is ~1667 mcg/ml; 100 mcg ≈ 0.06 ml',
				),
				'category' => 'GH Secretagogues',
			),
			array(
				'title'   => 'CJC-1295 (with DAC)',
				'excerpt' => 'Long-acting GHRH analogue that extends the GH-releasing pulse over several days.',
				'content' => "CJC-1295 DAC is a modified growth hormone releasing hormone (GHRH 1-29) analogue with a Drug Affinity Complex tail that binds to albumin, extending its half-life to roughly 6-8 days versus minutes for native GHRH.\n\nCompared to CJC-1295 without DAC (mod GRF 1-29), the DAC version produces a more sustained GH elevation rather than a sharp pulse. Often stacked with Ipamorelin in informal protocols.\n\nThis entry is informational only.",
				'meta'    => array(
					'formula'        => 'C165H269N47O46S',
					'weight'         => '3647.2 Da',
					'half_life'      => '~6-8 days (with DAC)',
					'reconstitution' => 'Bacteriostatic water; 2ml per 2mg or 5mg vial',
					'storage'        => 'Refrigerated once reconstituted',
					'typical_dose'   => '100 mcg daily or 300 mcg 2x weekly (reported, unverified)',
					'side_effects'   => 'Water retention, flushing, fatigue; possible cortisol effects at high doses',
					'pen_dial'       => 'At 2mg/3ml the concentration is ~667 mcg/ml; 100 mcg ≈ 0.15 ml',
				),
				'category' => 'GH Secretagogues',
			),
			array(
				'title'   => 'GHK-Cu (Copper tripeptide)',
				'excerpt' => 'Naturally occurring copper-binding tripeptide studied for skin repair, collagen stimulation and hair growth.',
				'content' => "GHK-Cu (glycyl-L-histidyl-L-lysine : copper(II)) is an endogenous tripeptide that declines with age. It is one of the better-studied cosmetic peptides, with published in vitro and small clinical work on wound healing, collagen synthesis and skin remodelling.\n\nUsed topically in many skincare formulations; subcutaneous use is discussed informally but has less supporting evidence.\n\nThis entry is informational only.",
				'meta'    => array(
					'formula'        => 'C14H24N6O4 · Cu',
					'weight'         => '403.9 Da',
					'half_life'      => 'Hours',
					'reconstitution' => 'Bacteriostatic water or saline',
					'storage'        => 'Cool, dark; copper peptides are light sensitive',
					'typical_dose'   => 'Topical 0.5-2%; injectable 2-3 mg daily (reported, unverified)',
					'side_effects'   => 'Topical: transient irritation. Injectable: largely unknown',
					'pen_dial'       => 'Rarely used in pen cartridges; topical is more common',
				),
				'category' => 'Cosmetic / Skin',
			),
			array(
				'title'   => 'PT-141 (Bremelanotide)',
				'excerpt' => 'Melanocortin receptor agonist studied for sexual dysfunction; notable for blood pressure effects.',
				'content' => "PT-141 (Bremelanotide) is a cyclic heptapeptide melanocortin-4 receptor agonist derived from the tanning peptide Melanotan II. It has actual FDA approval (as Vyleesi) for premenopausal hypoactive sexual desire disorder, which makes it unusual among informally-used peptides.\n\nImportant: it causes transient increases in blood pressure and heart rate, and is contraindicated with uncontrolled hypertension and cardiovascular disease. Nasal spray variants sold online are not pharmaceutical grade.\n\nThis entry is informational only and is not a recommendation.",
				'meta'    => array(
					'formula'        => 'C50H68N14O10',
					'weight'         => '1025.2 Da',
					'half_life'      => '~2.7 hours',
					'reconstitution' => 'Bacteriostatic water',
					'storage'        => 'Refrigerated once reconstituted',
					'typical_dose'   => '1.0-1.75 mg as needed (approved product dosing); informally often lower',
					'side_effects'   => 'Nausea (common), flushing, blood pressure spike, headache. BP contraindications are real',
					'pen_dial'       => 'At 10mg/3ml the concentration is ~3333 mcg/ml; 1mg ≈ 0.3 ml',
				),
				'category' => 'Melanocortins',
			),
			array(
				'title'   => 'Semax',
				'excerpt' => 'Neurotropic heptapeptide derived from ACTH(4-10), used clinically in Russia for brain injury and nootropic purposes.',
				'content' => "Semax is a synthetic fragment of ACTH(4-10) with the addition of a Pro-Gly-Pro tail, developed in Russia where it is a registered drug for stroke, optic nerve disease and cognitive disorders. It modulates BDNF and NGF expression in animal studies.\n\nTypically administered intranasally. Western clinical data is essentially absent; everything here is informational only.",
				'meta'    => array(
					'formula'        => 'C31H45N7O9S (Semax base)',
					'weight'         => '~681 Da',
					'half_life'      => 'Minutes intranasally; CNS effects outlast plasma',
					'reconstitution' => 'Usually supplied as nasal drops, not injectable',
					'storage'        => 'Refrigerated',
					'typical_dose'   => '0.2-1 mg intranasal daily (reported, unverified)',
					'side_effects'   => 'Occasional irritability, headache',
					'pen_dial'       => 'Not applicable — intranasal route',
				),
				'category' => 'Nootropic / Neuro',
			),
		);

		foreach ( $peptides as $p ) {
			$post_id = wp_insert_post( array(
				'post_type'    => Opeptides_CPT::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_excerpt' => $p['excerpt'],
				'post_content' => $p['content'],
			) );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}
			foreach ( $p['meta'] as $key => $value ) {
				update_post_meta( $post_id, '_opeptide_' . $key, $value );
			}
			$term = term_exists( $p['category'], Opeptides_CPT::TAXONOMY );
			if ( ! $term ) {
				$term = wp_insert_term( $p['category'], Opeptides_CPT::TAXONOMY );
			}
			if ( ! is_wp_error( $term ) ) {
				$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
				wp_set_object_terms( $post_id, $term_id, Opeptides_CPT::TAXONOMY );
			}
		}
	}
}
