(function () {
	'use strict';

	var config = window.idbCoreBlog || {};
	var attributesCache = new WeakMap();
	var pendingScrollFrame = 0;

	if (!config.ajaxUrl || !config.action || !config.nonce) {
		return;
	}

	function getBlog(element) {
		return element ? element.closest('[data-idb-blog]') : null;
	}

	function getAjaxLink(element) {
		return element ? element.closest('.idb-blog__pagination a, .idb-blog__filter-link') : null;
	}

	function getAjaxForm(element) {
		return element ? element.closest('.idb-blog__search') : null;
	}

	function setLoading(blog, isLoading) {
		var controls = blog.querySelectorAll(
			'.idb-blog__pagination a, .idb-blog__filter-link, .idb-blog__search-input, .idb-blog__search-button'
		);

		blog.classList.toggle('idb-blog--is-loading', isLoading);
		blog.setAttribute('aria-busy', isLoading ? 'true' : 'false');

		if (isLoading) {
			blog.dataset.idbBlogLoading = '1';
		} else {
			delete blog.dataset.idbBlogLoading;
		}

		controls.forEach(function (control) {
			control.setAttribute('aria-disabled', isLoading ? 'true' : 'false');

			if ('disabled' in control) {
				control.disabled = isLoading;
			}
		});
	}

	function getAttributes(blog) {
		if (attributesCache.has(blog)) {
			return attributesCache.get(blog);
		}

		var attributes = {};

		try {
			attributes = JSON.parse(blog.getAttribute('data-idb-blog-atts') || '{}');
		} catch (error) {
			attributes = {};
		}

		attributesCache.set(blog, attributes);

		return attributes;
	}

	function replaceBlog(blog, html) {
		var template = document.createElement('template');

		template.innerHTML = html.trim();

		var nextBlog = template.content.querySelector('[data-idb-blog]');

		if (!nextBlog) {
			return null;
		}

		blog.replaceWith(nextBlog);

		return nextBlog;
	}

	function getFixedHeaderOffset() {
		var offset = 0;
		var headers = document.querySelectorAll('#wpadminbar, .site-header, .elementor-location-header, header');

		headers.forEach(function (header) {
			var styles = window.getComputedStyle(header);
			var rect = header.getBoundingClientRect();

			if (
				rect.height <= 0 ||
				rect.bottom <= offset ||
				rect.top > offset + 1 ||
				(styles.position !== 'fixed' && styles.position !== 'sticky')
			) {
				return;
			}

			offset = Math.max(offset, rect.bottom);
		});

		return Math.ceil(offset) + 20;
	}

	function isBlogVisible(blog, offset) {
		var rect = blog.getBoundingClientRect();

		return rect.top >= offset && rect.bottom <= window.innerHeight;
	}

	function scrollToBlog(blog) {
		if (!blog || !blog.isConnected) {
			return;
		}

		var offset = getFixedHeaderOffset();
		var rect = blog.getBoundingClientRect();
		var top = Math.max(window.pageYOffset + rect.top - offset, 0);

		if (isBlogVisible(blog, offset) || Math.abs(window.pageYOffset - top) < 2) {
			return;
		}

		window.scrollTo({
			behavior: 'smooth',
			top: top,
		});
	}

	function scheduleBlogScroll(blog) {
		if (pendingScrollFrame) {
			window.cancelAnimationFrame(pendingScrollFrame);
		}

		pendingScrollFrame = window.requestAnimationFrame(function () {
			pendingScrollFrame = 0;
			scrollToBlog(blog);
		});
	}

	function getFormUrl(form) {
		var url = new URL(form.action || window.location.href, window.location.href);
		var data = new FormData(form);

		url.searchParams.delete('paged');
		url.searchParams.delete('page');

		data.forEach(function (value, key) {
			var nextValue = String(value).trim();

			if (nextValue) {
				url.searchParams.set(key, nextValue);
			} else {
				url.searchParams.delete(key);
			}
		});

		return url.toString();
	}

	function loadBlog(blog, url, shouldPushState, shouldScroll) {
		if (!blog || blog.dataset.idbBlogLoading === '1') {
			return Promise.resolve(null);
		}

		var body = new URLSearchParams();

		body.append('action', config.action);
		body.append('nonce', config.nonce);
		body.append('url', url);
		body.append('atts', JSON.stringify(getAttributes(blog)));

		setLoading(blog, true);

		return fetch(config.ajaxUrl, {
			body: body,
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			method: 'POST',
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Blog request failed.');
				}

				return response.json();
			})
			.then(function (payload) {
				if (!payload.success || !payload.data || !payload.data.html) {
					throw new Error('Blog response was invalid.');
				}

				var nextBlog = replaceBlog(blog, payload.data.html);

				if (nextBlog && shouldScroll) {
					scheduleBlogScroll(nextBlog);
				}

				if (nextBlog && shouldPushState) {
					window.history.pushState({ idbBlog: true }, '', url);
				}

				return nextBlog;
			})
			.catch(function () {
				if (shouldPushState) {
					window.location.href = url;
				}
			})
			.finally(function () {
				if (blog.isConnected) {
					setLoading(blog, false);
				}
			});
	}

	document.addEventListener('click', function (event) {
		var link = getAjaxLink(event.target);
		var blog = getBlog(link);

		if (!link || !blog || !link.href) {
			return;
		}

		event.preventDefault();

		if (blog.dataset.idbBlogLoading === '1') {
			return;
		}

		loadBlog(blog, link.href, true, true);
	});

	document.addEventListener('submit', function (event) {
		var form = getAjaxForm(event.target);
		var blog = getBlog(form);

		if (!form || !blog) {
			return;
		}

		event.preventDefault();

		if (blog.dataset.idbBlogLoading === '1') {
			return;
		}

		loadBlog(blog, getFormUrl(form), true, true);
	});

	window.addEventListener('popstate', function () {
		document.querySelectorAll('[data-idb-blog]').forEach(function (blog) {
			loadBlog(blog, window.location.href, false, false);
		});
	});
})();
