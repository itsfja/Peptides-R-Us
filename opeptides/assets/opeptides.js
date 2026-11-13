/* Opeptides front-end: calculator + diary AJAX. */
jQuery(function ($) {
	'use strict';

	/* ---------- Convipen cartridge calculator ---------- */
	function runCalc() {
		var cartridge = parseFloat($('.opeptides-calc').data('cartridge')) || 3;
		var mass = parseFloat($('#opep-mass').val()) || 0;   // mg
		var vol = parseFloat($('#opep-volume').val()) || 0; // ml
		var dose = parseFloat($('#opep-dose').val()) || 0;  // mcg
		var freq = Math.max(1, parseInt($('#opep-freq').val(), 10) || 1);

		var conc = vol > 0 ? (mass * 1000) / vol : 0; // mcg/ml
		var doseVol = conc > 0 ? dose / conc : 0;    // ml per dose
		var totalDoses = doseVol > 0 ? vol / doseVol : 0;
		var days = totalDoses > 0 ? totalDoses / freq : 0;
		var atPointOne = conc * 0.1; // mcg delivered in 0.1 ml

		$('#opep-r-conc').text(conc > 0 ? conc.toFixed(1) + ' mcg/ml' : '—');
		$('#opep-r-vol').text(doseVol > 0 ? doseVol.toFixed(3) + ' ml per dose' : '—');
		$('#opep-r-days').text(days > 0 ? days.toFixed(1) + ' days (' + Math.floor(totalDoses) + ' doses)' : '—');
		$('#opep-r-01').text(atPointOne > 0 ? atPointOne.toFixed(1) + ' mcg' : '—');

		var notes = [];
		if (vol > cartridge) {
			notes.push('Warning: ' + vol + ' ml exceeds the ' + cartridge + ' ml cartridge capacity.');
		}
		if (doseVol > 0 && doseVol < 0.02) {
			notes.push('Warning: dose volume is under 0.02 ml — measurement error will be significant.');
		}
		$('#opep-r-note').text(notes.join(' '));
		$('#opep-calc-results').show();
	}
	$(document).on('click', '#opep-calc-run', runCalc);
	$(document).on('keypress', '.opeptides-calc-form input', function (e) {
		if (e.which === 13) { e.preventDefault(); runCalc(); }
	});

	/* ---------- Diary AJAX ---------- */
	function ajax(action, data) {
		return $.post(opeptidesData.ajaxUrl, $.extend({
			action: action,
			nonce: opeptidesData.nonce
		}, data));
	}

	$('.opeptides-diary').on('submit', '.opeptides-form', function (e) {
		e.preventDefault();
		var $form = $(this);
		var fields = {};
		$form.serializeArray().forEach(function (f) { fields[f.name] = f.value; });
		ajax('opeptides_add_entry', fields).done(function () {
			window.location.reload();
		});
	});

	$('.opeptides-diary').on('click', '.opep-delete', function () {
		var $row = $(this).closest('tr');
		if (!window.confirm('Delete this entry?')) { return; }
		ajax('opeptides_delete_entry', { id: $row.data('id') }).done(function () {
			$row.fadeOut(200, function () { $(this).remove(); });
		});
	});

	$('.opeptides-diary').on('click', '.opep-start-cycle', function () {
		var $btn = $(this);
		ajax('opeptides_start_cycle', {
			name: $btn.data('name'),
			description: $btn.data('description')
		}).done(function () {
			window.location.reload();
		});
	});
});
