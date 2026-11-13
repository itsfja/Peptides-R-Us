/* Opeptides front-end: calculator + diary AJAX. */
jQuery(function ($) {
	'use strict';

	/* ---------- Convipen cartridge calculator ---------- */
	/* IU convention: pen dial units, 1 IU = 0.01 ml (U-100). */
	function drawPenScale(cartridge) {
		var $scale = $('#opep-pen-scale');
		if ($scale.data('drawn')) { return; }
		var ticks = 10;
		for (var i = 0; i <= ticks; i++) {
			var x = 244 + (112 * i / ticks);
			var line = i % 5 === 0 ? 6 : 3;
			$scale.append($(document.createElementNS('http://www.w3.org/2000/svg', 'line'))
				.attr({ x1: x, y1: 44, x2: x, y2: 44 + line, stroke: '#888', 'stroke-width': 1 }));
			if (i % 5 === 0) {
				$scale.append($(document.createElementNS('http://www.w3.org/2000/svg', 'text'))
					.attr({ x: x, y: 57 }).text(((cartridge * 100) * i / ticks).toFixed(0)));
			}
		}
		$scale.data('drawn', 1);
	}

	function updatePen(doseVol, cartridge) {
		var $pen = $('#opep-pen');
		if (!$pen.length) { return; }
		var iu = doseVol * 100;
		$('#opep-pen-dial').text(iu > 0 ? iu.toFixed(1) : '0');
		var frac = cartridge > 0 ? Math.min(1, doseVol / cartridge) : 0;
		var $marker = $('#opep-pen-dose-marker');
		if (doseVol > 0) {
			$marker.attr({ x: 244 + (112 * frac), visibility: 'visible' });
		} else {
			$marker.attr('visibility', 'hidden');
		}
	}

	function runCalc() {
		var cartridge = parseFloat($('.opeptides-calc').data('cartridge')) || 3;
		var mass = parseFloat($('#opep-mass').val()) || 0;   // mg
		var vol = parseFloat($('#opep-volume').val()) || 0; // ml
		var dose = parseFloat($('#opep-dose').val()) || 0;  // mcg
		var doseIu = parseFloat($('#opep-dose-iu').val());  // IU (optional)
		var freq = Math.max(1, parseInt($('#opep-freq').val(), 10) || 1);

		var conc = vol > 0 ? (mass * 1000) / vol : 0; // mcg/ml
		var doseVol;

		if (isFinite(doseIu) && doseIu > 0) {
			/* IU drives the dose: derive the peptide-unit dose from it. */
			doseVol = doseIu / 100;
			dose = conc > 0 ? doseVol * conc : 0;
			$('#opep-dose').val(dose > 0 ? dose.toFixed(1) : '');
		} else {
			doseVol = conc > 0 ? dose / conc : 0;
			$('#opep-dose-iu').val(doseVol > 0 ? (doseVol * 100).toFixed(1) : '');
		}

		var totalDoses = doseVol > 0 ? vol / doseVol : 0;
		var days = totalDoses > 0 ? totalDoses / freq : 0;
		var atPointOne = conc * 0.1; // mcg delivered in 0.1 ml
		var iu = doseVol * 100;

		$('#opep-r-conc').text(conc > 0 ? conc.toFixed(1) + ' mcg/ml' : '—');
		$('#opep-r-vol').text(doseVol > 0 ? doseVol.toFixed(3) + ' ml per dose' : '—');
		$('#opep-r-days').text(days > 0 ? days.toFixed(1) + ' days (' + Math.floor(totalDoses) + ' doses)' : '—');
		$('#opep-r-01').text(atPointOne > 0 ? atPointOne.toFixed(1) + ' mcg' : '—');
		$('#opep-r-iu').text(iu > 0 ? iu.toFixed(1) + ' IU (dial units)' : '—');

		var notes = [];
		if (vol > cartridge) {
			notes.push('Warning: ' + vol + ' ml exceeds the ' + cartridge + ' ml cartridge capacity.');
		}
		if (doseVol > 0 && doseVol < 0.02) {
			notes.push('Warning: dose volume is under 0.02 ml — measurement error will be significant.');
		}
		$('#opep-r-note').text(notes.join(' '));
		$('#opep-calc-results').show();

		drawPenScale(cartridge);
		updatePen(doseVol, cartridge);
	}

	/* Typing an IU value overrides the peptide-unit dose until cleared. */
	$(document).on('input', '#opep-dose-iu', function () {
		var v = parseFloat($(this).val());
		if (isFinite(v) && v > 0) { $('#opep-dose').val(''); }
	});
	$(document).on('input', '#opep-dose', function () {
		$('#opep-dose-iu').val('');
	});
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
