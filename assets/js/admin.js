/**
 * Mino Option — admin interactions.
 * Scoped to .mino-settings; no global pollution beyond minoAdmin localization.
 */
(function () {
	'use strict';

	var cfg = window.minoAdmin || {};

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
		if (el.classList && el.classList.contains('mino-switch-input')) {
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
		qa(root, '[data-mino-require]').forEach(function (box) {
			var rules = parseRequire(box.getAttribute('data-mino-require'));
			var panel = box.closest('.mino-panel') || box.closest('.mino-settings') || document;
			var ok = evaluateRules(rules, panel);
			box.classList.toggle('is-hidden', !ok);
			box.setAttribute('aria-hidden', ok ? 'false' : 'true');
			qa(box, 'input, select, textarea, button').forEach(function (el) {
				if (el.dataset.asfKeepDisabled === '1') return;
				el.disabled = !ok || el.hasAttribute('data-mino-orig-disabled');
			});
		});
	}

	function initTabs(root) {
		qa(root, '.mino-tab').forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				// Allow modifier/middle clicks to open normally.
				if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
				e.preventDefault();
				var id = tab.getAttribute('data-mino-tab');
				qa(root, '.mino-tab').forEach(function (t) {
					var on = t === tab;
					t.classList.toggle('is-active', on);
					t.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				qa(root, '.mino-panel').forEach(function (panel) {
					var on = panel.getAttribute('data-mino-panel') === id;
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
						url.searchParams.set('mino_tab', id);
						window.history.replaceState({}, '', url.toString());
					} catch (err) { /* ignore */ }
				}
				applyConditionals(root);
			});
		});
	}

	function initSwitches(root) {
		qa(root, '[data-mino-switch]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var on = btn.getAttribute('aria-checked') !== 'true';
				btn.setAttribute('aria-checked', on ? 'true' : 'false');
				btn.classList.toggle('is-on', on);
				var input = btn.parentNode.querySelector('.mino-switch-input');
				if (input) {
					input.value = on ? '1' : '0';
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		});

		qa(root, '.mino-buttonset-input').forEach(function (input) {
			input.addEventListener('change', function () {
				qa(input.closest('.mino-buttonset'), '.mino-buttonset-item').forEach(function (item) {
					item.classList.toggle('is-active', item.querySelector('input') === input && input.checked);
				});
			});
		});
	}

	function initMedia(root) {
		if (typeof wp === 'undefined' || !wp.media) return;

		qa(root, '[data-mino-media]').forEach(function (wrap) {
			var input = q(wrap, '.mino-media-id');
			var preview = q(wrap, '.mino-media-preview');
			var library = wrap.getAttribute('data-library') || 'all';
			var frame;

			q(wrap, '.mino-media-select').addEventListener('click', function (e) {
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
						: '<span class="mino-media-filename"></span>';
					var removeBtn = q(wrap, '.mino-media-remove');
					if (removeBtn) removeBtn.disabled = false;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				});
				frame.open();
			});

			var remove = q(wrap, '.mino-media-remove');
			if (remove) {
				remove.addEventListener('click', function (e) {
					e.preventDefault();
					input.value = '0';
					preview.innerHTML = '<span class="mino-media-empty">' +
						((cfg.i18n && cfg.i18n.noFile) || 'No file selected') + '</span>';
					remove.disabled = true;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				});
			}
		});

		qa(root, '[data-mino-gallery]').forEach(function (wrap) {
			var input = q(wrap, '.mino-gallery-ids');
			var list = q(wrap, '.mino-gallery-list');
			var frame;

			q(wrap, '.mino-gallery-select').addEventListener('click', function (e) {
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
						var clearBtn = q(wrap, '.mino-gallery-clear');
						if (clearBtn) clearBtn.disabled = ids.length === 0;
						input.dispatchEvent(new Event('change', { bubbles: true }));
					});
				}
				frame.open();
			});

			var clear = q(wrap, '.mino-gallery-clear');
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

	function clearRowValues(row) {
		qa(row, 'input, select, textarea').forEach(function (el) {
			if (el.type === 'checkbox' || el.type === 'radio') {
				el.checked = false;
				return;
			}
			if (el.classList && el.classList.contains('mino-switch-input')) {
				el.value = '0';
				var btn = el.parentNode && el.parentNode.querySelector('[data-mino-switch]');
				if (btn) {
					btn.classList.remove('is-on');
					btn.setAttribute('aria-checked', 'false');
				}
				return;
			}
			if (el.classList && (el.classList.contains('mino-media-id') || el.classList.contains('mino-gallery-ids'))) {
				el.value = el.classList.contains('mino-gallery-ids') ? '' : '0';
				var media = el.closest('.mino-media, .mino-gallery');
				if (media) {
					var preview = q(media, '.mino-media-preview');
					if (preview) {
						preview.innerHTML = '<span class="mino-media-empty">' +
							((cfg.i18n && cfg.i18n.noFile) || 'No file selected') + '</span>';
					}
					var list = q(media, '.mino-gallery-list');
					if (list) list.innerHTML = '';
					var removeBtn = q(media, '.mino-media-remove, .mino-gallery-clear');
					if (removeBtn) removeBtn.disabled = true;
				}
				return;
			}
			if (el.tagName === 'SELECT') {
				el.selectedIndex = 0;
				return;
			}
			el.value = '';
		});
	}

	function reindexRepeater(wrap) {
		var rows = qa(wrap, '[data-mino-repeater-row]');
		var base = wrap.getAttribute('data-name') || '';
		var esc = base.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
		rows.forEach(function (row, index) {
			qa(row, 'input, select, textarea').forEach(function (el) {
				if (!el.name) return;
				el.name = el.name.replace(
					new RegExp('^' + esc + '\\[(?:\\d+|__i__)\\]'),
					base + '[' + index + ']'
				);
				if (el.id) {
					el.id = el.id
						.replace(/_\d+_/, '_' + index + '_')
						.replace(/___i___/, '_' + index + '_');
				}
			});
			var label = q(row, '.mino-repeater-row-label');
			if (label) {
				label.textContent = label.textContent.replace(/\d+|{{INDEX}}|__i__/, String(index + 1));
				if (!/\d/.test(label.textContent)) {
					label.textContent = 'Item ' + (index + 1);
				}
			}
		});
		var empty = q(wrap, '.mino-repeater-empty');
		if (empty) empty.style.display = rows.length ? 'none' : '';
	}

	function createRepeaterRow(wrap, tpl) {
		var html = '';
		if (tpl && tpl.content && tpl.content.childElementCount) {
			var cloneNode = tpl.content.firstElementChild.cloneNode(true);
			var holder = document.createElement('div');
			holder.appendChild(cloneNode);
			html = holder.innerHTML;
		} else if (tpl && tpl.innerHTML && tpl.innerHTML.trim()) {
			html = tpl.innerHTML;
		}

		if (html) {
			html = html
				.replace(/__i__/g, '0')
				.replace(/\{\{INDEX\}\}/g, '0');
			var div = document.createElement('div');
			div.innerHTML = html.trim();
			var fromTpl = div.querySelector('[data-mino-repeater-row]') || div.firstElementChild;
			if (fromTpl) {
				clearRowValues(fromTpl);
				return fromTpl;
			}
		}

		// Fallback: clone an existing row (preview / empty template).
		var existing = q(wrap, '[data-mino-repeater-row]');
		if (!existing) return null;
		var clone = existing.cloneNode(true);
		clearRowValues(clone);
		return clone;
	}

	function initRepeater(root) {
		qa(root, '[data-mino-repeater]').forEach(function (wrap) {
			var rowsWrap = q(wrap, '[data-mino-repeater-rows]');
			var tpl = q(wrap, '.mino-repeater-template');
			var max = parseInt(wrap.getAttribute('data-max') || '50', 10);
			var min = parseInt(wrap.getAttribute('data-min') || '0', 10);
			var addBtn = q(wrap, '.mino-repeater-add');

			function rowCount() {
				return qa(wrap, '[data-mino-repeater-row]').length;
			}

			function appendRow(row) {
				if (!row || !rowsWrap) return;
				var empty = q(wrap, '.mino-repeater-empty');
				if (empty) empty.remove();
				rowsWrap.appendChild(row);
				reindexRepeater(wrap);
				initSwitches(row);
				initMedia(row);
				applyConditionals(wrap.closest('.mino-panel') || root);
			}

			if (addBtn) {
				addBtn.addEventListener('click', function (e) {
					e.preventDefault();
					if (rowCount() >= max) return;
					appendRow(createRepeaterRow(wrap, tpl));
				});
			}

			wrap.addEventListener('click', function (e) {
				var removeBtn = e.target.closest('.mino-repeater-remove');
				if (removeBtn) {
					e.preventDefault();
					if (rowCount() <= min) return;
					var row = removeBtn.closest('[data-mino-repeater-row]');
					if (row) row.remove();
					reindexRepeater(wrap);
					if (!rowCount() && rowsWrap) {
						var p = document.createElement('p');
						p.className = 'mino-repeater-empty';
						p.textContent = 'No items yet. Add a row to get started.';
						rowsWrap.appendChild(p);
					}
					return;
				}
				var dupBtn = e.target.closest('.mino-repeater-duplicate');
				if (dupBtn) {
					e.preventDefault();
					if (rowCount() >= max) return;
					var source = dupBtn.closest('[data-mino-repeater-row]');
					if (!source) return;
					var clone = source.cloneNode(true);
					appendRow(clone);
				}
			});

			if (window.jQuery && jQuery.fn.sortable) {
				jQuery(rowsWrap).sortable({
					handle: '.mino-repeater-handle',
					items: '[data-mino-repeater-row]',
					update: function () { reindexRepeater(wrap); }
				});
			}
		});
	}

	function ensureOverlay(root) {
		var overlay = q(root, '.mino-loading-overlay');
		if (overlay) return overlay;
		overlay = document.createElement('div');
		overlay.className = 'mino-loading-overlay';
		overlay.hidden = true;
		overlay.setAttribute('aria-hidden', 'true');
		overlay.innerHTML =
			'<div class="mino-loader" role="status" aria-live="polite">' +
			'<span class="mino-loader-spinner" aria-hidden="true"></span>' +
			'<span class="mino-loader-text"></span></div>';
		root.insertBefore(overlay, root.firstChild);
		return overlay;
	}

	function ensureToast(root) {
		var toast = q(root, '.mino-toast');
		if (toast) return toast;
		toast = document.createElement('div');
		toast.className = 'mino-toast';
		toast.hidden = true;
		toast.setAttribute('role', 'status');
		toast.setAttribute('aria-live', 'polite');
		root.insertBefore(toast, root.firstChild);
		return toast;
	}

	function setLoading(root, on, label) {
		var overlay = ensureOverlay(root);
		var text = q(overlay, '.mino-loader-text');
		if (text && label) text.textContent = label;
		root.classList.toggle('is-loading', !!on);
		root.setAttribute('aria-busy', on ? 'true' : 'false');
		if (on) {
			overlay.hidden = false;
			overlay.setAttribute('aria-hidden', 'false');
		} else {
			overlay.hidden = true;
			overlay.setAttribute('aria-hidden', 'true');
		}
	}

	function showToast(root, message, type) {
		var toast = ensureToast(root);
		toast.textContent = message || '';
		toast.className = 'mino-toast is-' + (type || 'success');
		toast.hidden = false;
		window.clearTimeout(toast._asfTimer);
		toast._asfTimer = window.setTimeout(function () {
			toast.hidden = true;
		}, 3200);
	}

	function syncEditors(form) {
		if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
			window.tinymce.triggerSave();
		}
		qa(form, 'textarea.wp-editor-area').forEach(function (el) {
			if (window.tinymce) {
				var ed = window.tinymce.get(el.id);
				if (ed) el.value = ed.getContent();
			}
		});
	}

	function parseAjaxResponse(raw) {
		try {
			return typeof raw === 'string' ? JSON.parse(raw) : raw;
		} catch (e) {
			return null;
		}
	}

	function submitFormAjax(root, form, action) {
		var i18n = cfg.i18n || {};
		var loadingLabel = action === 'reset'
			? (i18n.resetting || 'Resetting settings…')
			: (i18n.saving || 'Saving settings…');

		syncEditors(form);
		setLoading(root, true, loadingLabel);

		var data = new FormData(form);
		data.set('mino_action', action);
		data.set('action', cfg.ajaxAction || 'mino_save_settings');
		if (cfg.nonce && !data.get('mino_nonce')) {
			data.set('mino_nonce', cfg.nonce);
		}

		var url = cfg.ajaxUrl || (window.ajaxurl || '');
		var mock = !!cfg.mockAjax || !url;

		function finish(ok, payload) {
			setLoading(root, false);
			var message = (payload && payload.data && payload.data.message)
				|| (payload && payload.message)
				|| (ok
					? (action === 'reset' ? (i18n.resetDone || 'Settings reset to defaults.') : (i18n.saved || 'Settings saved.'))
					: (i18n.saveFailed || 'Save failed. Please try again.'));

			if (!ok && payload && payload.data && payload.data.errors) {
				var errs = payload.data.errors;
				var list = Object.keys(errs).map(function (k) { return errs[k]; }).filter(Boolean);
				if (list.length) message = list.join(' · ');
			}

			showToast(root, message, ok ? 'success' : 'error');

			var flash = q(root, '.mino-flash');
			if (flash && ok) {
				flash.className = 'notice notice-success is-dismissible mino-flash';
				flash.innerHTML = '<p>' + message.replace(/</g, '&lt;') + '</p>';
			}
		}

		if (mock) {
			window.setTimeout(function () {
				finish(true, { success: true, data: { message: action === 'reset' ? i18n.resetDone : i18n.saved } });
			}, 900);
			return;
		}

		fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		})
			.then(function (res) {
				return res.text().then(function (text) {
					var json = parseAjaxResponse(text);
					return { okHttp: res.ok, json: json, text: text };
				});
			})
			.then(function (pack) {
				var json = pack.json;
				var ok = !!(json && json.success);
				finish(ok, json || { message: pack.text });
			})
			.catch(function () {
				finish(false, null);
			});
	}

	function initAjaxForms(root) {
		qa(root, 'form.mino-form').forEach(function (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (root.classList.contains('is-loading')) return;
				submitFormAjax(root, form, 'save');
			});

			qa(form, '[data-mino-reset]').forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					if (root.classList.contains('is-loading')) return;
					var msg = (cfg.i18n && cfg.i18n.confirmReset) || 'Reset all settings?';
					if (!window.confirm(msg)) return;
					submitFormAjax(root, form, 'reset');
				});
			});
		});
	}

	function initColor(root) {
		if (!window.jQuery || !jQuery.fn.wpColorPicker) return;
		jQuery(root).find('.mino-color-picker').wpColorPicker({
			change: function () {
				applyConditionals(root);
			}
		});
	}

	ready(function () {
		qa(document, '.mino-settings').forEach(function (root) {
			ensureOverlay(root);
			ensureToast(root);
			initTabs(root);
			initSwitches(root);
			initMedia(root);
			initRepeater(root);
			initAjaxForms(root);
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
