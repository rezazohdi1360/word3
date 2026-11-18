/*!
 * قالب عابد — رفتارهای صفحه (بدون jQuery)
 * بخش‌ها: پنل‌ها و کشوها · هدر و مگامنو · تعداد · افزودن به لیست خرید و قطعه‌ها ·
 * جستجوی آنی · آرشیو بدون بارگذاری مجدد · سفارش سریع · صفحه محصول · ابزارهای کوچک
 */
(function () {
	'use strict';

	var A = window.ABED || {};
	var d = document;
	var $ = function (s, c) { return (c || d).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
	var on = function (el, ev, sel, fn) {
		el.addEventListener(ev, function (e) {
			var t = e.target.closest ? e.target.closest(sel) : null;
			if (t && el.contains(t)) fn.call(t, e, t);
		});
	};
	var I18N = A.i18n || {};

	/* ------------------------------------------------------------------ ابزار */
	function later(fn) {
		var run = function () { (window.requestIdleCallback || function (f) { return setTimeout(f, 1); })(fn, { timeout: 1500 }); };
		if (d.readyState === 'complete') run(); else window.addEventListener('load', run, { once: true });
	}
	// اجرای fn وقتی el نزدیک دید کاربر می‌رسد (خواندن ابعاد فقط برای همان بخش، نه همه صفحه در یک کار طولانی).
	function whenVisible(el, fn) {
		if (!('IntersectionObserver' in window)) { later(fn); return; }
		var io = new IntersectionObserver(function (en) {
			if (en.some(function (x) { return x.isIntersecting; })) { io.disconnect(); fn(); }
		}, { rootMargin: '300px 0px' });
		io.observe(el);
	}
	function fmt(n) {
		n = Math.round(Number(n) || 0);
		return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, A.sep || ',');
	}
	function wcUrl(endpoint) {
		return (A.wcAjax || '/?wc-ajax=%%endpoint%%').replace('%%endpoint%%', endpoint);
	}
	function post(endpoint, data) {
		var body = data instanceof FormData ? data : new URLSearchParams(data);
		return fetch(wcUrl(endpoint), { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) {
			return r.json().then(function (j) { return { res: r, json: j }; });
		});
	}
	function cookie(name) {
		var m = d.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.$?*|{}()[\]\\/+^]/g, '\\$&') + '=([^;]*)'));
		return m ? decodeURIComponent(m[1]) : '';
	}
	var toastTimer;
	function toast(msg, link, isError) {
		var t = $('[data-ab-toast]');
		if (!t) return;
		t.className = 'ab-toast' + (isError ? ' is-error' : '');
		t.innerHTML = '';
		var s = d.createElement('span');
		s.textContent = msg;
		t.appendChild(s);
		if (link) {
			var a = d.createElement('a');
			a.href = link.href || '#';
			a.textContent = link.text;
			if (link.open) a.setAttribute('data-ab-open', link.open);
			t.appendChild(a);
		}
		t.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { t.hidden = true; }, 3800);
	}

	/* ------------------------------------------------------------- پنل‌ها */
	var overlay = $('[data-ab-overlay]');
	var openPanel = null;
	var lastFocus = null;

	function panel(name) { return $('[data-ab-panel="' + name + '"]'); }

	function open(name) {
		var p = panel(name);
		if (!p) return false;
		if (openPanel && openPanel !== p) close(true);
		lastFocus = d.activeElement;
		p.hidden = false;
		void p.offsetWidth; // اجرای انیمیشن
		p.classList.add('is-open');
		if (overlay) overlay.hidden = false;
		d.body.classList.add('ab-lock');
		openPanel = p;
		$$('[aria-controls="' + p.id + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
		var f = p.querySelector('[data-ab-close], button, a, input');
		if (f) setTimeout(function () { f.focus({ preventScroll: true }); }, 60);
		return true;
	}
	function close(keepOverlay) {
		var p = openPanel;
		if (!p) return;
		p.classList.remove('is-open');
		$$('[aria-controls="' + p.id + '"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
		if (p.classList.contains('ab-offcanvas') || p.classList.contains('ab-drawer')) {
			setTimeout(function () { if (!p.classList.contains('is-open')) p.hidden = true; }, 300);
		}
		openPanel = null;
		if (!keepOverlay) {
			if (overlay) overlay.hidden = true;
			d.body.classList.remove('ab-lock');
			if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
		}
	}
	on(d, 'click', '[data-ab-open]', function (e, el) {
		if (open(el.getAttribute('data-ab-open'))) e.preventDefault();
	});
	on(d, 'click', '[data-ab-close]', function (e) { e.preventDefault(); close(); });
	if (overlay) overlay.addEventListener('click', function () { close(); });
	// منوهای کوچک <details data-ab-dropdown> (مثل انتخاب زبان هدر): با کلیک بیرون یا Escape بسته شوند
	function closeDropdowns(except) {
		$$('details[data-ab-dropdown][open]').forEach(function (x) { if (x !== except) x.removeAttribute('open'); });
	}
	d.addEventListener('click', function (e) { closeDropdowns(e.target.closest && e.target.closest('details[data-ab-dropdown]')); });
	d.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') return;
		var o = $('details[data-ab-dropdown][open]');
		if (o) { closeDropdowns(); var s = $('summary', o); if (s) s.focus(); }
	});
	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { close(); closeMega(); closeSearch(); }
		if (e.key === 'Tab' && openPanel) {
			var f = $$('a[href], button:not([disabled]), input:not([type=hidden]), select, textarea', openPanel).filter(function (x) { return x.offsetParent !== null; });
			if (!f.length) return;
			if (e.shiftKey && d.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
			else if (!e.shiftKey && d.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
		}
	});

	/* ------------------------------------------------------ هدر و مگامنو */
	var header = $('[data-ab-header]');
	if (header) {
		var ticking = false;
		window.addEventListener('scroll', function () {
			if (ticking) return;
			ticking = true;
			requestAnimationFrame(function () {
				header.classList.toggle('is-scrolled', window.scrollY > 8);
				ticking = false;
			});
		}, { passive: true });
	}

	var mega = $('[data-ab-mega]');
	var megaTimer;
	function openMega() {
		if (!mega) return;
		clearTimeout(megaTimer);
		$$('img[data-src]', mega).forEach(function (i) { i.src = i.getAttribute('data-src'); i.removeAttribute('data-src'); });
		mega.classList.add('is-open');
		mega.querySelector('button').setAttribute('aria-expanded', 'true');
	}
	function closeMega() { if (!mega) return; mega.classList.remove('is-open'); mega.querySelector('button').setAttribute('aria-expanded', 'false'); }
	if (mega) {
		var btn = mega.querySelector('.ab-mega__btn');
		btn.addEventListener('click', function () { mega.classList.contains('is-open') ? closeMega() : openMega(); });
		if (window.matchMedia('(hover: hover)').matches) {
			mega.addEventListener('mouseenter', function () { megaTimer = setTimeout(openMega, 120); });
			mega.addEventListener('mouseleave', function () { clearTimeout(megaTimer); megaTimer = setTimeout(closeMega, 200); });
		}
		d.addEventListener('click', function (e) { if (!mega.contains(e.target)) closeMega(); });
	}

	// تکثیر نوار اعلان برای حرکت پیوسته
	var tick = $('.ab-ticker__list');
	if (tick && window.matchMedia('(max-width: 1099px)').matches) {
		$$('li', tick).forEach(function (li) { var c = li.cloneNode(true); c.setAttribute('aria-hidden', 'true'); tick.appendChild(c); });
	}

	// دکمه تماس شناور
	var fl = $('[data-ab-float]');
	if (fl) {
		var fb = $('.ab-float__btn', fl);
		fb.addEventListener('click', function () {
			var o = fl.classList.toggle('is-open');
			fb.setAttribute('aria-expanded', o ? 'true' : 'false');
		});
		d.addEventListener('click', function (e) {
			// بیرون از منو، یا انتخاب یکی از گزینه‌ها (مثلاً باز شدن فرم مشاوره) ← منو بسته شود
			if (!fl.contains(e.target) || e.target.closest('.ab-float__menu a, .ab-float__menu button')) { fl.classList.remove('is-open'); fb.setAttribute('aria-expanded', 'false'); }
		});
	}

	/* ------------------------------------------------------------- تعداد */
	on(d, 'click', '.ab-qty__btn', function (e, b) {
		var box = b.closest('[data-ab-qty]');
		if (!box) return;
		var input = box.querySelector('input');
		var step = parseFloat(input.step) || 1;
		var min = input.min !== '' ? parseFloat(input.min) : 0;
		var max = input.max !== '' ? parseFloat(input.max) : Infinity;
		var v = parseFloat(input.value) || 0;
		v = v + step * parseInt(b.getAttribute('data-step'), 10);
		if (v < min) v = min;
		// بیشتر از سقف: پنجره «پیش‌فاکتور فوری» هسته عابد (اگر سقف موجودی روشن باشد).
		if (v > max && window.abedOverQty && window.abedOverQty(input, v, max)) return;
		if (v > max) v = max;
		input.value = v;
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.dispatchEvent(new Event('change', { bubbles: true }));
	});

	// عدد تایپ‌شده بیشتر از سقف
	on(d, 'change', '[data-ab-qty] input', function (e, input) {
		var max = input.max !== '' ? parseFloat(input.max) : Infinity;
		var v = parseFloat(String(input.value).replace(/[۰-۹]/g, function (c) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(c); })) || 0;
		if (v > max && window.abedOverQty && window.abedOverQty(input, v, max)) { input.value = max; }
	});

	/* ------------------------------------------- لیست خرید و قطعه‌ها */
	var FKEY = 'ab_frag_v1';
	function applyFragments(frags) {
		if (!frags) return;
		Object.keys(frags).forEach(function (sel) {
			$$(sel).forEach(function (el) {
				var tpl = d.createElement('template');
				tpl.innerHTML = frags[sel].trim();
				var n = tpl.content.firstElementChild;
				if (n) el.replaceWith(n.cloneNode(true));
			});
		});
		d.dispatchEvent(new CustomEvent('abed:cart-updated'));
	}
	function storeFragments(json) {
		try { sessionStorage.setItem(FKEY, JSON.stringify({ h: json.cart_hash || '', f: json.fragments })); } catch (e) { /* حالت خصوصی */ }
	}
	function handleCart(json) {
		if (json && json.fragments) {
			applyFragments(json.fragments);
			storeFragments(json);
		}
	}
	// صفحه ممکن است از کش آمده باشد؛ اگر کاربر سبد دارد، قطعه‌ها را تازه کن.
	(function syncCart() {
		if (!A.wcAjax) return;
		var has = cookie('woocommerce_items_in_cart');
		var hash = cookie('woocommerce_cart_hash');
		var cached = null;
		try { cached = JSON.parse(sessionStorage.getItem(FKEY) || 'null'); } catch (e) { cached = null; }
		if (!has) {
			if (cached) { try { sessionStorage.removeItem(FKEY); } catch (e) {} }
			return;
		}
		if (cached && cached.h && cached.h === hash) { applyFragments(cached.f); return; }
		post('get_refreshed_fragments', {}).then(function (r) { handleCart(r.json); }).catch(function () {});
	})();

	// افزودن به لیست خرید (کارت‌ها و صفحه محصول)
	on(d, 'submit', 'form[data-ab-add]', function (e, form) {
		if (!A.wcAjax) return;
		e.preventDefault();
		var btn = form.querySelector('[type=submit]');
		var fd = new FormData(form);
		var pid = fd.get('add-to-cart') || (btn && btn.value);
		if (!pid) { form.submit(); return; }
		var data = new FormData();
		fd.forEach(function (v, k) { if (k !== 'add-to-cart') data.append(k, v); });
		data.set('product_id', pid);
		data.set('quantity', fd.get('quantity') || 1);
		if (btn) btn.classList.add('is-loading');
		post('add_to_cart', data).then(function (r) {
			if (btn) btn.classList.remove('is-loading');
			if (!r.json || r.json.error) {
				// خطای اعتبارسنجی (مثلاً حداقل تعداد): ارسال عادی تا پیام ووکامرس نمایش داده شود.
				HTMLFormElement.prototype.submit.call(form);
				return;
			}
			handleCart(r.json);
			if (A.afterAdd === 'checkout' && A.checkoutUrl) { location.href = A.checkoutUrl; return; }
			var card = form.closest('.ab-card');
			if (card) { card.classList.add('is-added'); setTimeout(function () { card.classList.remove('is-added'); }, 1600); }
			if (form.hasAttribute('data-ab-single') && A.drawer && panel('cart')) { open('cart'); }
			else { toast(I18N.added || 'اضافه شد', { text: 'مشاهده', href: A.cartUrl, open: A.drawer ? 'cart' : '' }); }
		}).catch(function () {
			if (btn) btn.classList.remove('is-loading');
			toast(I18N.error || 'خطا', null, true);
		});
	});
	// تغییر تعداد و حذف در کشو
	var qtyTimers = {};
	function setQty(item, qty) {
		var key = item.getAttribute('data-key');
		item.classList.add('is-busy');
		post('abed_set_qty', { key: key, qty: qty }).then(function (r) {
			handleCart(r.json);
		}).catch(function () {
			item.classList.remove('is-busy');
			toast(I18N.error || 'خطا', null, true);
		});
	}
	on(d, 'change', '.ab-mini__item input', function (e, input) {
		var item = input.closest('.ab-mini__item');
		var key = item.getAttribute('data-key');
		clearTimeout(qtyTimers[key]);
		qtyTimers[key] = setTimeout(function () { setQty(item, Math.max(0, parseInt(input.value, 10) || 0)); }, 450);
	});
	on(d, 'click', '[data-ab-remove]', function (e, b) { setQty(b.closest('.ab-mini__item'), 0); });

	/* ------------------------------------------------------- جستجوی آنی */
	var index = null, indexLoading = null;
	function norm(s) {
		return String(s || '').toLowerCase()
			.replace(/[يى]/g, 'ی').replace(/ك/g, 'ک').replace(/[ةۀ]/g, 'ه').replace(/[أإآ]/g, 'ا').replace(/ؤ/g, 'و')
			.replace(/[\u200c‏ـً-ٟ]/g, '')
			.replace(/[۰-۹]/g, function (c) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(c); })
			.replace(/[٠-٩]/g, function (c) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(c); })
			.replace(/[()\[\]،,.\-_/\\|«»"']+/g, ' ').replace(/\s+/g, ' ').trim();
	}
	function loadIndex() {
		if (index) return Promise.resolve(index);
		if (indexLoading) return indexLoading;
		if (!A.searchIndex) return Promise.reject();
		indexLoading = fetch(A.searchIndex, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			j.p.forEach(function (p) { p._t = norm(p.t); p._h = norm(p.t + ' ' + p.s + ' ' + p.c + ' ' + (p.k || '')); });
			j.c.forEach(function (c) { c._t = norm(c.t); });
			index = j;
			return j;
		});
		return indexLoading;
	}
	function find(q) {
		var nq = norm(q);
		if (!nq) return { p: [], c: [] };
		var toks = nq.split(' ');
		var res = [];
		index.p.forEach(function (p) {
			for (var i = 0; i < toks.length; i++) if (p._h.indexOf(toks[i]) === -1) return;
			var s = 0;
			if (p.s && norm(p.s) === nq) s += 300;
			if (p._t.indexOf(nq) === 0) s += 120;
			else if (p._t.indexOf(nq) > -1) s += 60;
			toks.forEach(function (t) { if ((' ' + p._t).indexOf(' ' + t) > -1) s += 15; });
			if (p.x) s += 8;
			s -= p._t.length / 40;
			res.push({ p: p, s: s });
		});
		res.sort(function (a, b) { return b.s - a.s; });
		var cats = index.c.filter(function (c) { return toks.every(function (t) { return c._t.indexOf(t) > -1; }); }).slice(0, 4);
		return { p: res.slice(0, 8).map(function (r) { return r.p; }), c: cats, total: res.length };
	}
	function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
	function hl(text, q) {
		var out = esc(text);
		norm(q).split(' ').filter(function (t) { return t.length > 1; }).forEach(function (t) {
			try { out = out.replace(new RegExp('(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'g'), '<mark>$1</mark>'); } catch (e) {}
		});
		return out;
	}
	var activeSearch = null, searchState = false;
	function closeSearch(fromHistory) {
		if (!activeSearch) return;
		var box = activeSearch.querySelector('.ab-search__res');
		box.hidden = true;
		activeSearch.querySelector('input[name=s]').setAttribute('aria-expanded', 'false');
		activeSearch.classList.remove('is-open');
		activeSearch = null;
		// اگر با باز شدن نتایج یک مرحله به تاریخچه اضافه شده بود، آن را هم برگردان (دکمه بازگشت گوشی)
		if (searchState && !fromHistory) { searchState = false; try { history.back(); } catch (e) {} }
		searchState = false;
	}
	// فقط یک کادر جستجو در هر لحظه باز است: با رفتن به کادر دیگر (مثلاً از جستجوی بنر به هدر) قبلی بی‌صدا بسته می‌شود؛
	// مرحله تاریخچه موبایل (searchState) برای کادر جدید می‌ماند.
	function dropOtherSearch(form) {
		if (!activeSearch || activeSearch === form) return;
		activeSearch.querySelector('.ab-search__res').hidden = true;
		activeSearch.querySelector('input[name=s]').setAttribute('aria-expanded', 'false');
		activeSearch.classList.remove('is-open');
		activeSearch = null;
	}
	d.addEventListener('focusin', function (e) {
		if (!activeSearch || activeSearch.contains(e.target)) return;
		var f = e.target.closest && e.target.closest('[data-ab-search]');
		if (f) dropOtherSearch(f); else closeSearch();
	});
	var pendingNav = null;
	window.addEventListener('popstate', function () {
		if (pendingNav) { var u = pendingNav; pendingNav = null; location.href = u; return; }
		if (searchState) { searchState = false; closeSearch(true); }
	});
	// رفتن به یک نتیجه وقتی مرحله «نتایج باز» در تاریخچه هست: اول آن مرحله حذف شود تا دکمه بازگشت گوشی دوبار لازم نشود
	function leaveTo(url) {
		if (!searchState) { location.href = url; return; }
		pendingNav = url;
		searchState = false;
		try { history.back(); } catch (e) { location.href = url; }
		setTimeout(function () { if (pendingNav) { var u = pendingNav; pendingNav = null; location.href = u; } }, 400);
	}
	d.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('.ab-search__res a[href]');
		if (!a || !searchState || e.ctrlKey || e.metaKey) return;
		e.preventDefault();
		leaveTo(a.href);
	}, true);
	d.addEventListener('submit', function (e) {
		var f = e.target.closest && e.target.closest('[data-ab-search]');
		if (!f || !searchState) return;
		e.preventDefault();
		leaveTo(f.action + '?' + new URLSearchParams(new FormData(f)).toString());
	}, true);
	d.addEventListener('keydown', function (e) { if (e.key === 'Escape' && activeSearch) closeSearch(); });
	on(d, 'click', '[data-ab-search-close]', function (e) { e.preventDefault(); closeSearch(); });
	on(d, 'click', '[data-ab-search-clear]', function (e, b) {
		e.preventDefault();
		var form = b.closest('[data-ab-search]');
		var input = form.querySelector('input[name=s]');
		input.value = '';
		form.classList.remove('has-text');
		if (activeSearch === form) closeSearch();
		input.focus();
	});
	function renderSearch(form, q) {
		var box = form.querySelector('.ab-search__res');
		var input = form.querySelector('input[name=s]');
		if (norm(q).length < 2) { if (activeSearch === form) closeSearch(); else box.hidden = true; return; }
		var r = find(q);
		var home = index.home || '';
		var h = '';
		if (r.c.length) {
			h += '<div class="ab-sres__group">دسته\u200cها</div><div>';
			r.c.forEach(function (c) { h += '<a class="ab-chip ab-sres__cat" href="' + esc(home + c.u) + '">' + hl(c.t, q) + ' <span class="ab-chip__n">' + c.n + '</span></a>'; });
			h += '</div>';
		}
		if (r.p.length) {
			h += '<div class="ab-sres__group">محصولات</div>';
			r.p.forEach(function (p, i) {
				h += '<a class="ab-sres__item" role="option" id="ab-sr-' + i + '" href="' + esc(home + p.u) + '">' +
					(p.m ? '<img class="ab-sres__img" src="' + esc(home + p.m) + '" alt="" loading="lazy" width="48" height="48">' : '<span class="ab-sres__img"></span>') +
					'<span><span class="ab-sres__t">' + hl(p.t, q) + '</span><span class="ab-sres__m">' + esc([p.n, p.s ? 'کد ' + p.s : ''].filter(Boolean).join(' · ')) + '</span></span>' +
					(p.x ? '<span class="ab-sres__p">' + esc(p.p) + '</span>' : '<span class="ab-sres__p is-inq">' + esc(I18N.inquiry || 'استعلام') + '</span>') +
					'</a>';
			});
			h += '<a class="ab-sres__all" href="' + esc(form.action + '?s=' + encodeURIComponent(q) + '&post_type=product') + '">' + esc(I18N.allResult || 'همه نتایج') + ' (' + r.total + ')</a>';
		}
		if (!r.p.length && !r.c.length) h = '<div class="ab-sres__empty">' + esc(I18N.noResult || '') + '</div>';
		h = '<div class="ab-sres__head"><span>نتایج «' + esc(q.trim()) + '»' + (r.total ? ' — ' + r.total + ' کالا' : '') + '</span>' +
			'<button type="button" class="ab-sres__close" data-ab-search-close aria-label="بستن نتایج">بستن <svg class="ab-i" aria-hidden="true"><use href="#i-close"></use></svg></button></div>' + h;
		box.innerHTML = h;
		try { d.dispatchEvent(new CustomEvent('abed:search', { detail: { q: q.trim(), n: r.total || 0 } })); } catch (e) {}
		var wasOpen = !box.hidden;
		box.hidden = false;
		box.scrollTop = wasOpen ? box.scrollTop : 0;
		input.setAttribute('aria-expanded', 'true');
		dropOtherSearch(form);
		form.classList.add('is-open');
		activeSearch = form;
		if (!searchState && window.matchMedia('(max-width: 899px)').matches) {
			try { history.pushState({ abSearch: 1 }, ''); searchState = true; } catch (e) {}
		}
	}
	$$('[data-ab-search]').forEach(function (form) {
		var input = form.querySelector('input[name=s]');
		var timer;
		var sel = -1;
		function prime() { loadIndex().catch(function () {}); }
		input.addEventListener('focus', prime, { once: true });
		// جستجوی بنر پایین‌تر از هدر است: با فوکوس صفحه را بالا ببر تا نتایج تمام ارتفاع صفحه را داشته باشند
		if (form.classList.contains('ab-search--hero')) {
			input.addEventListener('focus', function () {
				var top = form.getBoundingClientRect().top;
				var hd = $('[data-ab-header]');
				var want = (hd ? hd.getBoundingClientRect().bottom : 0) + 12;
				if (top - want > 40 && top + 380 > window.innerHeight) window.scrollBy({ top: top - want, behavior: 'smooth' });
			});
		}
		input.addEventListener('input', function () {
			form.classList.toggle('has-text', input.value.length > 0);
			clearTimeout(timer);
			sel = -1;
			timer = setTimeout(function () {
				loadIndex().then(function () { renderSearch(form, input.value); }).catch(function () {});
			}, 60);
		});
		input.addEventListener('keydown', function (e) {
			var items = $$('.ab-sres__item', form);
			if (!items.length) return;
			if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
				e.preventDefault();
				sel = (sel + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
				items.forEach(function (it, i) { it.setAttribute('aria-selected', i === sel ? 'true' : 'false'); });
				input.setAttribute('aria-activedescendant', items[sel].id);
				items[sel].scrollIntoView({ block: 'nearest' });
			} else if (e.key === 'Enter' && sel > -1) {
				e.preventDefault();
				leaveTo(items[sel].href);
			}
		});
	});
	d.addEventListener('click', function (e) { if (activeSearch && !activeSearch.contains(e.target)) closeSearch(); });
	on(d, 'click', '[data-ab-focus-search]', function () {
		var i = $('.ab-header [data-ab-search] input[name=s]');
		if (!i) return;
		window.scrollTo({ top: 0, behavior: 'smooth' });
		setTimeout(function () { i.focus(); }, 250);
	});

	/* ------------------------------------------------ آرشیو بدون رفرش */
	function archiveRoot() { return $('[data-ab-archive]'); }
	function loadArchive(url, push) {
		var root = archiveRoot();
		if (!root) { location.href = url; return; }
		var main = $('[data-ab-results]', root);
		var wasOpen = panel('filters') && panel('filters').classList.contains('is-open');
		if (main) main.classList.add('is-loading');
		fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'abed-archive' } })
			.then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var fresh = doc.querySelector('[data-ab-archive]');
				if (!fresh) { location.href = url; return; }
				root.innerHTML = fresh.innerHTML;
				d.title = doc.title;
				if (push) history.pushState({ abArchive: 1 }, '', url);
				initArchive();
				if (wasOpen) { var p = panel('filters'); p.classList.add('is-open'); openPanel = p; }
				var top = $('[data-ab-results]', root);
				if (top && !wasOpen && top.getBoundingClientRect().top < 0) top.scrollIntoView({ behavior: 'smooth', block: 'start' });
			})
			.catch(function () { location.href = url; });
	}
	if (archiveRoot()) {
		history.replaceState({ abArchive: 1 }, '', location.href);
		window.addEventListener('popstate', function (e) { if (e.state && e.state.abArchive) loadArchive(location.href, false); });
		on(d, 'click', '[data-ab-archive] a[data-ab-filter], [data-ab-archive] a[data-ab-view], [data-ab-archive] .woocommerce-pagination a', function (e, a) {
			if (e.ctrlKey || e.metaKey || e.shiftKey) return;
			e.preventDefault();
			loadArchive(a.href, true);
		});
		on(d, 'change', '[data-ab-archive] select[data-ab-autosubmit]', function (e, s) {
			var url = new URL(location.href);
			url.searchParams.set(s.name, s.value);
			url.searchParams.delete('paged');
			url.pathname = url.pathname.replace(/\/page\/\d+\/?/, '/');
			loadArchive(url.toString(), true);
		});
	} else {
		on(d, 'change', 'select[data-ab-autosubmit]', function (e, s) { s.form.submit(); });
	}

	function initArchive() {
		// شمارنده فیلترهای فعال روی دکمه موبایل
		var p = panel('filters');
		var c = $('[data-ab-fcount]');
		if (p && c) { var n = parseInt(p.getAttribute('data-active'), 10) || 0; c.textContent = n; c.hidden = !n; }
		// توضیح بلند دسته (بعد از نمایش اول صفحه؛ خواندن ارتفاع باعث محاسبه چیدمان می‌شود)
		$$('[data-ab-collapse]').forEach(function (el) { whenVisible(el, function () {
			var b = el.parentNode.querySelector('[data-ab-expand]');
			if (el.scrollHeight > 300 && b) {
				el.classList.add('is-collapsed');
				b.hidden = false;
				b.onclick = function () { el.classList.remove('is-collapsed'); b.hidden = true; };
			}
		}); });
		initQuick();
	}

	// لیست قیمت دسته: فقط با کلیک بارگذاری می‌شود (سرعت صفحه دسته)
	on(d, 'click', '[data-ab-pricelist]', function (e, a) {
		e.preventDefault();
		var box = a.closest('.ab-pricelist');
		a.classList.add('is-loading');
		fetch(a.getAttribute('data-ab-pricelist'), { credentials: 'same-origin', headers: { 'X-Requested-With': 'abed-pricelist' } })
			.then(function (r) { return r.text(); })
			.then(function (html) { box.innerHTML = html; })
			.catch(function () { location.href = a.href; });
	});

	/* ------------------------------------------------------ سفارش سریع */
	function tierPrice(tiers, qty) {
		var price = tiers.length ? tiers[0][1] : 0;
		for (var i = 0; i < tiers.length; i++) if (qty >= tiers[i][0]) price = tiers[i][1];
		return price;
	}
	function initQuick() {
		var form = $('[data-ab-quick]');
		if (!form || form.__ab) return;
		form.__ab = 1;
		var btn = form.querySelector('[type=submit]');
		function recalc() {
			var count = 0, total = 0;
			$$('.ab-qrow', form).forEach(function (row) {
				var input = row.querySelector('input');
				var cell = row.querySelector('[data-ab-linesum]');
				if (!input) return;
				var q = parseInt(input.value, 10) || 0;
				var tiers = JSON.parse(row.getAttribute('data-tiers') || '[]');
				if (q > 0) {
					var line = q * tierPrice(tiers, q);
					count++; total += line;
					cell.textContent = fmt(line);
					row.classList.add('is-picked');
				} else {
					cell.textContent = '—';
					row.classList.remove('is-picked');
				}
			});
			$('[data-ab-quick-count]', form).textContent = count;
			$('[data-ab-quick-total]', form).textContent = fmt(total);
			btn.disabled = !count;
		}
		form.addEventListener('input', recalc);
		form.addEventListener('change', recalc);
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var data = new FormData();
			$$('.ab-qrow input', form).forEach(function (i) { if ((parseInt(i.value, 10) || 0) > 0) data.append(i.name, i.value); });
			btn.classList.add('is-loading');
			post('abed_bulk_add', data).then(function (r) {
				btn.classList.remove('is-loading');
				handleCart(r.json);
				var added = parseInt(r.res.headers.get('X-Abed-Added') || '0', 10);
				var failed = r.res.headers.get('X-Abed-Failed');
				if (added && A.afterAdd === 'checkout' && A.checkoutUrl && !failed) { location.href = A.checkoutUrl; return; }
				if (added) toast(added + ' قلم ' + (I18N.added || ''), { text: 'مشاهده', href: A.cartUrl, open: A.drawer ? 'cart' : '' });
				if (failed) setTimeout(function () { toast('افزوده نشد: ' + decodeURIComponent(failed), null, true); }, added ? 2500 : 0);
				$$('.ab-qrow input', form).forEach(function (i) { i.value = 0; });
				recalc();
			}).catch(function () { btn.classList.remove('is-loading'); toast(I18N.error || 'خطا', null, true); });
		});
		recalc();
	}

	/* ---------------------------------------------------------- صفحه محصول */
	var single = $('form[data-ab-single]');
	if (single) {
		var box = single.closest('[data-ab-buybox]');
		var table = box ? $('table[data-tiers]', box) : null;
		var tiers = table ? JSON.parse(table.getAttribute('data-tiers')) : null;
		var totalEl = $('[data-ab-linetotal]', single);
		var qtyInput = $('input.qty', single);
		var update = function () {
			if (!qtyInput) return;
			var q = Math.max(0, parseInt(qtyInput.value, 10) || 0);
			if (tiers && table) {
				var activeMin = tiers[0][0];
				tiers.forEach(function (t) { if (q >= t[0]) activeMin = t[0]; });
				$$('tbody tr', table).forEach(function (tr) { tr.classList.toggle('is-active', parseInt(tr.getAttribute('data-min'), 10) === activeMin); });
				var unit = tierPrice(tiers, q);
				var save = (tiers[0][1] - unit) * q;
				totalEl.hidden = q < 1;
				totalEl.querySelector('strong').textContent = fmt(unit * q) + ' ' + (A.currency || '');
				totalEl.querySelector('small').textContent = save > 0 ? ' · صرفه\u200cجویی ' + fmt(save) + ' ' + (A.currency || '') : '';
			}
		};
		if (qtyInput) { qtyInput.addEventListener('input', update); qtyInput.addEventListener('change', update); update(); }

		// نوار خرید چسبان در موبایل
		if ('IntersectionObserver' in window && window.matchMedia('(max-width: 899px)').matches && box) {
			var row = $('.ab-addform__row', single);
			var io = new IntersectionObserver(function (en) {
				var past = !en[0].isIntersecting && en[0].boundingClientRect.top < 0;
				if (past && !single.classList.contains('is-floating')) { single.style.minHeight = single.offsetHeight + 'px'; single.classList.add('is-floating'); }
				else if (!past && single.classList.contains('is-floating')) { single.classList.remove('is-floating'); single.style.minHeight = ''; }
			});
			if (row) io.observe(box);
		}
	}

	// گالری
	$$('[data-ab-gallery]').forEach(function (g) {
		var main = $('.ab-gallery__main', g);
		var thumbs = $$('.ab-gallery__thumb', g);
		thumbs.forEach(function (t) {
			t.addEventListener('click', function () {
				var i = parseInt(t.getAttribute('data-ab-slide'), 10);
				var rtl = getComputedStyle(main).direction === 'rtl';
				main.scrollTo({ left: (rtl ? -1 : 1) * i * main.clientWidth, behavior: 'smooth' });
				thumbs.forEach(function (x) { x.classList.toggle('is-active', x === t); });
			});
		});
		if (thumbs.length) {
			main.addEventListener('scroll', function () {
				var i = Math.round(Math.abs(main.scrollLeft) / main.clientWidth);
				thumbs.forEach(function (x, j) { x.classList.toggle('is-active', j === i); });
			}, { passive: true });
		}
		on(g, 'click', '[data-ab-zoom]', function (e, a) {
			if (typeof HTMLDialogElement !== 'function') return;
			e.preventDefault();
			var dlg = d.createElement('dialog');
			dlg.className = 'ab-lightbox';
			dlg.innerHTML = '<img src="' + esc(a.href) + '" alt=""><button type="button" class="ab-iconbtn" aria-label="بستن"><svg class="ab-i"><use href="#i-close"></use></svg></button>';
			d.body.appendChild(dlg);
			dlg.addEventListener('click', function () { dlg.close(); });
			dlg.addEventListener('close', function () { dlg.remove(); });
			dlg.showModal();
		});
	});

	/* -------------------------------------------------------- ردیف‌های افقی */
	$$('[data-ab-rail]').forEach(function (rail) {
		var list = $('.ab-rail__list', rail);
		var navs = $$('.ab-rail__nav', rail);
		function state() {
			var max = list.scrollWidth - list.clientWidth;
			var x = Math.abs(list.scrollLeft);
			navs.forEach(function (n) {
				var dir = parseInt(n.getAttribute('data-dir'), 10);
				n.disabled = dir > 0 ? x <= 4 : x >= max - 4;
			});
		}
		navs.forEach(function (n) {
			n.addEventListener('click', function () {
				list.scrollBy({ left: parseInt(n.getAttribute('data-dir'), 10) * list.clientWidth * 0.9, behavior: 'smooth' });
			});
		});
		var tick = false;
		list.addEventListener('scroll', function () {
			if (tick) return;
			tick = true;
			requestAnimationFrame(function () { tick = false; state(); });
		}, { passive: true });
		whenVisible(rail, state);
	});

	initArchive();

	/* ------------------------------------------ جستجوهای پرطرفدار */
	function renderPopular(form) {
		var list = A.popular || [];
		var box = form.querySelector('.ab-search__res');
		if (!list.length || !box) return false;
		var h = '<div class="ab-sres__head"><span>جستجوهای پرطرفدار</span>' +
			'<button type="button" class="ab-sres__close" data-ab-search-close aria-label="بستن">بستن <svg class="ab-i" aria-hidden="true"><use href="#i-close"></use></svg></button></div><div class="ab-sres__pop">';
		list.forEach(function (q) { h += '<button type="button" class="ab-chip" data-ab-pop="' + esc(q) + '">' + esc(q) + '</button>'; });
		box.innerHTML = h + '</div>';
		dropOtherSearch(form);
		box.hidden = false;
		form.classList.add('is-open');
		activeSearch = form;
		return true;
	}
	$$('[data-ab-search]').forEach(function (form) {
		var input = form.querySelector('input[name=s]');
		input.addEventListener('focus', function () { if (!input.value.trim()) renderPopular(form); });
		input.addEventListener('input', function () { if (!input.value.trim() && d.activeElement === input) setTimeout(function () { renderPopular(form); }, 80); });
	});
	on(d, 'mousedown', '[data-ab-pop]', function (e) { e.preventDefault(); });
	on(d, 'click', '[data-ab-pop]', function (e, b) {
		var form = b.closest('[data-ab-search]'), input = form.querySelector('input[name=s]');
		input.value = b.getAttribute('data-ab-pop');
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.focus();
	});

	/* ------------------------------------------ فوتر جمع‌شونده (موبایل) */
	on(d, 'click', '.ab-footer-collapse .ab-footer__col:not(.ab-footer__about) > .ab-footer__title', function (e, t) {
		if (window.matchMedia('(min-width: 700px)').matches) return;
		t.parentNode.classList.toggle('is-open');
	});

	/* ------------------------------------------ دکمه بازگشت به بالا */
	(function () {
		var b = $('[data-ab-totop]');
		if (!b) return;
		b.hidden = false;
		var tick = false;
		window.addEventListener('scroll', function () {
			if (tick) return;
			tick = true;
			requestAnimationFrame(function () { b.classList.toggle('is-on', window.scrollY > 900); tick = false; });
		}, { passive: true });
		b.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
	})();

	/* ------------------------------------------ برچسب فیلدهای فرم افزونه‌ها (صفحه‌خوان) */
	$$('.abed-consult-inline-form input[name=phone]:not([aria-label])').forEach(function (i) { i.setAttribute('aria-label', 'شماره تماس'); });

	/* ------------------------------------------ کپی لینک (اشتراک‌گذاری) */
	on(d, 'click', '[data-ab-copy]', function (e, b) {
		var url = b.getAttribute('data-ab-copy');
		(navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () {
			toast('لینک کپی شد');
			b.classList.add('is-copied');
			setTimeout(function () { b.classList.remove('is-copied'); }, 1600);
		}, function () { window.prompt('لینک:', url); });
	});
	/* نقشه برگه تماس: iframe فقط با لمس بارگذاری می‌شود (سرعت و حریم خصوصی) */
	on(d, 'click', '[data-ab-map] .ab-map__btn', function (e, b) {
		var box = b.closest('[data-ab-map]'), f = d.createElement('iframe');
		f.src = box.getAttribute('data-ab-map'); f.title = 'نقشه فروشگاه'; f.loading = 'lazy'; f.allowFullscreen = true;
		box.innerHTML = ''; box.appendChild(f);
	});
	/* اشتراک‌گذاری: «سایر برنامه‌ها» فقط وقتی دستگاه پشتیبانی کند؛ بستن با کلیک بیرون یا Esc */
	if (navigator.share) $$('[data-ab-native-share]').forEach(function (b) { b.hidden = false; });
	on(d, 'click', '[data-ab-native-share]', function (e, b) {
		navigator.share({ title: b.getAttribute('data-title') || d.title, url: b.getAttribute('data-ab-native-share') }).catch(function () {});
	});
	d.addEventListener('click', function (e) {
		$$('[data-ab-share][open]').forEach(function (s) { if (!s.contains(e.target) || e.target === s) s.removeAttribute('open'); });
	});
	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') $$('[data-ab-share][open]').forEach(function (s) { s.removeAttribute('open'); });
	});

	/* ------------------------------------------ پنجره تبلیغاتی */
	(function () {
		var p = $('[data-ab-popup]');
		if (!p || typeof p.showModal !== 'function') return;
		if (p.getAttribute('data-mobile') === '0' && window.matchMedia('(max-width: 899px)').matches) return;
		var key = 'ab-popup-' + p.getAttribute('data-ver'), days = parseInt(p.getAttribute('data-days'), 10) || 0, last = 0;
		try { last = parseInt(localStorage.getItem(key) || '0', 10); } catch (e) { last = 0; }
		if (last && (days === 0 || Date.now() - last < days * 864e5)) return;
		function show() {
			if (p.open || d.querySelector('dialog[open]') || d.body.classList.contains('ab-lock')) return false;
			p.showModal();
			try { localStorage.setItem(key, String(Date.now())); } catch (e) { /* */ }
			return true;
		}
		if (p.getAttribute('data-trigger') === 'scroll') {
			var need = (parseInt(p.getAttribute('data-scroll'), 10) || 40) / 100;
			var onScroll = function () {
				var h = d.documentElement.scrollHeight - window.innerHeight;
				if (h > 0 && window.scrollY / h >= need && show()) window.removeEventListener('scroll', onScroll);
			};
			window.addEventListener('scroll', onScroll, { passive: true });
		} else {
			setTimeout(show, (parseInt(p.getAttribute('data-delay'), 10) || 0) * 1000);
		}
		p.addEventListener('click', function (e) { if (e.target === p || e.target.closest('[data-ab-popup-close]')) p.close(); });
	})();

	/* ---------------------------------------------- محافظت از محتوا (تنظیمات قالب ← صفحه محصول) */
	(function () {
		var P = A.protect || {};
		if (P.src) {
			d.addEventListener('copy', function (e) {
				var sel = String(window.getSelection ? window.getSelection() : '');
				var t = e.target && e.target.nodeType === 1 ? e.target : (e.target && e.target.parentElement);
				if (sel.replace(/\s+/g, ' ').trim().length < 60 || !e.clipboardData || (t && t.closest && t.closest('input,textarea,[contenteditable],.ab-share,[data-ab-copy]'))) return;
				var url = location.href.split('#')[0];
				try { url = decodeURI(url); } catch (x) {}
				e.clipboardData.setData('text/plain', sel + '\n\nمنبع: ' + (P.name || document.title) + ' — ' + url);
				e.preventDefault();
			});
		}
		if (P.img) {
			d.documentElement.classList.add('ab-noimgsave');
			d.addEventListener('contextmenu', function (e) { if (e.target.closest && e.target.closest('img,picture,.ab-gallery,.ab-lightbox,.ab-card__media')) e.preventDefault(); });
			d.addEventListener('dragstart', function (e) { if (e.target.tagName === 'IMG') e.preventDefault(); });
		}
		if (P.nosel) d.documentElement.classList.add('ab-nosel');
	})();

	/* ---------------------------------------------- ویدیو: فقط با کلیک بارگذاری می‌شود */
	d.addEventListener('click', function (e) {
		var b = e.target.closest && e.target.closest('.ab-video__play');
		if (!b) return;
		var f = b.closest('.ab-video'), src = f.getAttribute('data-src'), el;
		if ('iframe' === f.getAttribute('data-ab-video')) {
			el = d.createElement('iframe');
			el.src = src; el.allow = 'autoplay; fullscreen'; el.allowFullscreen = true; el.title = b.getAttribute('aria-label') || '';
		} else {
			el = d.createElement('video');
			el.src = src; el.controls = true; el.autoplay = true; el.playsInline = true; el.preload = 'auto';
		}
		b.replaceWith(el);
		if (el.focus) el.focus();
	});

	/* ---------------------------------------------- توضیح کوتاه بلند: چند خط + «ادامه» */
	$$('[data-ab-clamp]').forEach(function (el) {
		el.classList.add('is-clamped');
		if (el.scrollHeight - el.clientHeight < 6) { el.classList.remove('is-clamped'); return; }
		var b = d.createElement('button');
		b.type = 'button'; b.className = 'ab-pshort__more'; b.textContent = 'ادامه توضیحات'; b.setAttribute('aria-expanded', 'false');
		b.addEventListener('click', function () {
			var open = el.classList.toggle('is-clamped') === false;
			b.setAttribute('aria-expanded', open ? 'true' : 'false');
			b.textContent = open ? 'بستن' : 'ادامه توضیحات';
		});
		el.insertAdjacentElement('afterend', b);
	});

	/* ---------------------------------------------- رابط عمومی لیست خرید */
	// برای بخش‌های دیگر (مثل لیست قیمت هسته عابد): افزودن گروهی با همان کشو و پیام‌های قالب.
	window.AbedCart = {
		ready: !!A.wcAjax,
		afterAdd: A.afterAdd,
		checkoutUrl: A.checkoutUrl,
		cartUrl: A.cartUrl,
		fmt: fmt,
		toast: toast,
		bulk: function (items) {
			var data = new FormData();
			Object.keys(items).forEach(function (id) { data.append('items[' + id + ']', items[id]); });
			return post('abed_bulk_add', data).then(function (r) {
				handleCart(r.json);
				var failed = r.res.headers.get('X-Abed-Failed');
				return { added: parseInt(r.res.headers.get('X-Abed-Added') || '0', 10), failed: failed ? decodeURIComponent(failed) : '' };
			});
		},
		open: function () {
			if (A.drawer && panel('cart')) { open('cart'); return true; }
			return false;
		}
	};
	d.dispatchEvent(new CustomEvent('abed:cart-api'));
})();
