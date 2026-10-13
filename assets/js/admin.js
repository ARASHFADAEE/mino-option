/**
 * Arash Settings Framework — admin interactions.
 * Scoped to .asf-settings; no global pollution beyond asfAdmin localization.
 */
(function () {
	'use strict';

	var cfg = window.asfAdmin || {};

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function q(root, sel) {
		return root.querySelector(sel);
	}

	function qa(root, sel) {
		return Array.prototype.slice.call(root.querySelectorAll(sel));
	}

	function parseRequire(raw) {
		if (!raw) return null;
		try {
			return JSON.parse(raw);
		} catch (e) {
			return null;
		}
	}

	function readValue(root, fieldId) {
		var radio = root.querySelector('input[type="radio"][name="' + fieldId + '"]:checked');
		if (radio) return radio.value;

		var el = root.querySelector('[name="' + fieldId + '"], #' + CSS.escape(fieldId));
		if (!el) {
			// Multiselect
			var multi = root.querySelectorAll('select[name="' + fieldId + '[]"] option:checked');
			if (multi.length) {
				return Array.prototype.map.call(multi, function (o) { return o.value; });
			}
			return '';
		}
		if (el.type === 'checkbox') return el.checked;
		if (el.classList && el.classList.contains('asf-switch-input')) {
			return el.value === '1' || el.value === 'true';
		}
		return el.value != null ? el.value : '';
	}

	function stringify(v) {
		if (typeof v === 'boolean') return v ? '1' : '0';
		if (Array.isArray(v)) return v.map(String).join(',');
		return String(v == null ? '' : v);
	}

	function looseEqual(a, b) {
		if (typeof b === 'boolean') {
			var av = a === true || a === '1' || a === 'true' || a === 1;
			return av === b;
		}
		return stringify(a) === stringify(b);
	}

	function isEmpty(v) {
		if (v === null || v === undefined || v === false) return true;
		if (typeof v === 'string') return v === '';
		if (Array.isArray(v)) return v.length === 0;
		return false;
	}

	function compare(current, op, needed) {
		op = String(op || '==').toLowerCase();
		if (op === '=' || op === '===') op = '==';
		if (op === '!==') op = '!=';

		switch (op) {
			case '!=':
			case 'not':
				return !looseEqual(current, needed);
			case 'empty':
				return isEmpty(current);
			case 'not_empty':
			case '!empty':
				return !isEmpty(current);
			case 'in':
				return (Array.isArray(needed) ? needed : [needed]).map(stringify).indexOf(stringify(current)) !== -1;
			case 'not_in':
				return (Array.isArray(needed) ? needed : [needed]).map(stringify).indexOf(stringify(current)) === -1;
			case '==':
			default:
				return looseEqual(current, needed);
		}
	}

	function evaluateRules(rules, root) {
		if (!rules) return true;
		var relation = 'AND';
		var list = rules;

		if (!Array.isArray(rules) && typeof rules === 'object') {
			relation = String(rules.relation || 'AND').toUpperCase() === 'OR' ? 'OR' : 'AND';
			list = [];
			Object.keys(rules).forEach(function (k) {
				if (k === 'relation') return;
				if (Array.isArray(rules[k]) || (rules[k] && typeof rules[k] === 'object')) {
					list.push(rules[k]);
				}
			});
			if (Array.isArray(rules.rules)) list = rules.rules;
		}

		if (!Array.isArray(list) || !list.length) return true;

		// Single triple without nesting: ['field','==',true]
		if (typeof list[0] === 'string') {
			list = [list];
		}

		var results = list.map(function (rule) {
			if (!Array.isArray(rule) || rule.length < 2) return true;
			var field = String(rule[0] || '');
			var op = rule[1];
			var needed = rule.length > 2 ? rule[2] : true;
			return compare(readValue(root, field), op, needed);
		});

		if (relation === 'OR') return results.indexOf(true) !== -1;
		return results.indexOf(false) === -1;
	}

	function applyConditionals(scope) {
		var root = scope || document;
		qa(root, '[data-asf-require]').forEach(function (box) {
			var rules = parseRequire(box.getAttribute('data-asf-require'));
			var panel = box.closest('.asf-panel') || box.closest('.asf-settings') || document;
			var ok = evaluateRules(rules, panel);
			box.classList.toggle('is-hidden', !ok);
			box.setAttribute('aria-hidden', ok ? 'false' : 'true');
			qa(box, 'input, select, textarea, button').forEach(function (el) {
				if (el.dataset.asfKeepDisabled === '1') return;
				el.disabled = !ok || el.hasAttribute('data-asf-orig-disabled');
			});
		});
	}

	function initTabs(root) {
		qa(root, '.asf-tab').forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				// Allow modifier/middle clicks to open normally.
				if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
				e.preventDefault();
				var id = tab.getAttribute('data-asf-tab');
				qa(root, '.asf-tab').forEach(function (t) {
					var on = t === tab;
					t.classList.toggle('is-active', on);
					t.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				qa(root, '.asf-panel').forEach(function (panel) {
					var on = panel.getAttribute('data-asf-panel') === id;
					panel.classList.toggle('is-active', on);
					if (on) {
						panel.removeAttribute('hidden');
					} else {
						panel.setAttribute('hidden', 'hidden');
					}
				});
				if (window.history && window.history.replaceState) {
					try {
						var url = new URL(window.location.href);
						url.searchParams.set('asf_tab', id);
						window.history.replaceState({}, '', url.toString());
					} catch (err) { /* ignore */ }
				}
				applyConditionals(root);
			});
		});
	}

	function initSwitches(root) {
		qa(root, '[data-asf-switch]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var on = btn.getAttribute('aria-checked') !== 'true';
				btn.setAttribute('aria-checked', on ? 'true' : 'false');
				btn.classList.toggle('is-on', on);
				var input = btn.parentNode.querySelector('.asf-switch-input');
				if (input) {
					input.value = on ? '1' : '0';
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		});

		qa(root, '.asf-buttonset-input').forEach(function (input) {
			input.addEventListener('change', function () {
				qa(input.closest('.asf-buttonset'), '.asf-buttonset-item').forEach(function (item) {
					item.classList.toggle('is-active', item.querySelector('input') === input && input.checked);
				});
			});
		});
	}

	function initMedia(root) {
		if (typeof wp === 'undefined' || !wp.media) return;

		qa(root, '[data-asf-media]').forEach(function (wrap) {
			var input = q(wrap, '.asf-media-id');
			var preview = q(wrap, '.asf-media-preview');
			var library = wrap.getAttribute('data-library') || 'all';
			var frame;

			q(wrap, '.asf-media-select').addEventListener('click', function (e) {
				e.preventDefault();
				if (frame) {
					frame.open();
					return;
				}
				frame = wp.media({
					title: (cfg.i18n && cfg.i18n.selectMedia) || 'Select media',
					button: { text: (cfg.i18n && cfg.i18n.useMedia) || 'Use selection' },
					library: library === 'all' ? {} : { type: library },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					input.value = att.id;
					var url = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) || att.url;
					preview.innerHTML = url
						? '<img src="' + url.replace(/"/g, '&quot;') + '" alt="" />'
						: '<span class="asf-media-filename"></span>';
					var removeBtn = q(wrap, '.asf-media-remove');
					if (removeBtn) removeBtn.disabled = false;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				});
				frame.open();
			});

			var remove = q(wrap, '.asf-media-remove');
			if (remove) {
				remove.addEventListener('click', function (e) {
					e.preventDefault();
					input.value = '0';
					preview.innerHTML = '<span class="asf-media-empty">' +
						((cfg.i18n && cfg.i18n.noFile) || 'No file selected') + '</span>';
					remove.disabled = true;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				});
			}
		});

		qa(root, '[data-asf-gallery]').forEach(function (wrap) {
			var input = q(wrap, '.asf-gallery-ids');
			var list = q(wrap, '.asf-gallery-list');
			var frame;

			q(wrap, '.asf-gallery-select').addEventListener('click', function (e) {
				e.preventDefault();
				if (!frame) {
					frame = wp.media({
						title: (cfg.i18n && cfg.i18n.selectMedia) || 'Select media',
						button: { text: (cfg.i18n && cfg.i18n.useMedia) || 'Use selection' },
						library: { type: 'image' },
						multiple: true
					});
					frame.on('select', function () {
						var selection = frame.state().get('selection').toJSON();
						var ids = selection.map(function (a) { return a.id; });
						input.value = ids.join(',');
						list.innerHTML = selection.map(function (a) {
							var url = (a.sizes && a.sizes.thumbnail && a.sizes.thumbnail.url) || a.url;
							return '<li data-id="' + a.id + '"><img src="' + String(url).replace(/"/g, '&quot;') + '" alt="" /></li>';
						}).join('');
						var clearBtn = q(wrap, '.asf-gallery-clear');
						if (clearBtn) clearBtn.disabled = ids.length === 0;
						input.dispatchEvent(new Event('change', { bubbles: true }));
					});
				}
				frame.open();
			});

			var clear = q(wrap, '.asf-gallery-clear');
			if (clear) {
				clear.addEventListener('click', function (e) {
					e.preventDefault();
					input.value = '';
					list.innerHTML = '';
					clear.disabled = true;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				});
			}
		});
	}

	function reindexRepeater(wrap) {
		var rows = qa(wrap, '[data-asf-repeater-row]');
		var base = wrap.getAttribute('data-name');
		rows.forEach(function (row, index) {
			qa(row, 'input, select, textarea').forEach(function (el) {
				if (!el.name) return;
				el.name = el.name.replace(new RegExp('^' + base.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[(?:\\d+|__i__)\\]'), base + '[' + index + ']');
				if (el.id) {
					el.id = el.id.replace(/_\d+_/, '_' + index + '_').replace(/___i___/, '_' + index + '_');
				}
			});
			var label = q(row, '.asf-repeater-row-label');
			if (label) label.textContent = label.textContent.replace(/\d+|{{INDEX}}/, String(index + 1));
		});
		var empty = q(wrap, '.asf-repeater-empty');
		if (empty) empty.style.display = rows.length ? 'none' : '';
	}

	function initRepeater(root) {
		qa(root, '[data-asf-repeater]').forEach(function (wrap) {
			var rowsWrap = q(wrap, '[data-asf-repeater-rows]');
			var tpl = q(wrap, '.asf-repeater-template');
			var max = parseInt(wrap.getAttribute('data-max') || '50', 10);
			var min = parseInt(wrap.getAttribute('data-min') || '0', 10);

			function rowCount() {
				return qa(wrap, '[data-asf-repeater-row]').length;
			}

			q(wrap, '.asf-repeater-add').addEventListener('click', function (e) {
				e.preventDefault();
				if (rowCount() >= max) return;
				if (!tpl) return;
				var html = tpl.innerHTML
					.replace(/__i__/g, String(rowCount()))
					.replace(/\{\{INDEX\}\}/g, String(rowCount()));
				var div = document.createElement('div');
				div.innerHTML = html.trim();
				var row = div.firstElementChild;
				var empty = q(wrap, '.asf-repeater-empty');
				if (empty) empty.remove();
				rowsWrap.appendChild(row);
				reindexRepeater(wrap);
				initSwitches(row);
				initMedia(row);
				applyConditionals(wrap.closest('.asf-panel') || root);
			});

			wrap.addEventListener('click', function (e) {
				var removeBtn = e.target.closest('.asf-repeater-remove');
				if (removeBtn) {
					e.preventDefault();
					if (rowCount() <= min) return;
					var row = removeBtn.closest('[data-asf-repeater-row]');
					if (row) row.remove();
					reindexRepeater(wrap);
					if (!rowCount()) {
						var p = document.createElement('p');
						p.className = 'asf-repeater-empty';
						p.textContent = 'No items yet. Add a row to get started.';
						rowsWrap.appendChild(p);
					}
					return;
				}
				var dupBtn = e.target.closest('.asf-repeater-duplicate');
				if (dupBtn) {
					e.preventDefault();
					if (rowCount() >= max) return;
					var source = dupBtn.closest('[data-asf-repeater-row]');
					if (!source) return;
					var clone = source.cloneNode(true);
					rowsWrap.appendChild(clone);
					reindexRepeater(wrap);
					initSwitches(clone);
					initMedia(clone);
				}
			});

			if (window.jQuery && jQuery.fn.sortable) {
				jQuery(rowsWrap).sortable({
					handle: '.asf-repeater-handle',
					items: '[data-asf-repeater-row]',
					update: function () { reindexRepeater(wrap); }
				});
			}
		});
	}

	function initReset(root) {
		qa(root, '[data-asf-reset]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				var msg = (cfg.i18n && cfg.i18n.confirmReset) || 'Reset all settings?';
				if (!window.confirm(msg)) {
					e.preventDefault();
				}
			});
		});
	}

	function initColor(root) {
		if (!window.jQuery || !jQuery.fn.wpColorPicker) return;
		jQuery(root).find('.asf-color-picker').wpColorPicker({
			change: function () {
				applyConditionals(root);
			}
		});
	}

	ready(function () {
		qa(document, '.asf-settings').forEach(function (root) {
			initTabs(root);
			initSwitches(root);
			initMedia(root);
			initRepeater(root);
			initReset(root);
			initColor(root);
			applyConditionals(root);

			root.addEventListener('change', function () {
				applyConditionals(root);
			});
			root.addEventListener('input', function () {
				applyConditionals(root);
			});
		});
	});
})();
