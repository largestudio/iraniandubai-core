(function () {
	'use strict';

	var config = window.idbCoreBlog || {};

	if (!config.ajaxUrl || !config.action || !config.nonce) {
		return;
	}

	function getBlog(element) {
		return element ? element.closest('[data-idb-blog]') : null;
	}

	function getAjaxLink(element) {
		return element ? element.closest('.idb-blog__pagination a, .idb-blog__filter-link') : null;
	}

	function setLoading(blog, isLoading) {
		var links = blog.querySelectorAll('.idb-blog__pagination a, .idb-blog__filter-link');

		blog.classList.toggle('idb-blog--is-loading', isLoading);
		blog.setAttribute('aria-busy', isLoading ? 'true' : 'false');
		blog.dataset.idbBlogLoading = isLoading ? '1' : '';

		links.forEach(function (link) {
			link.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
		});
	}

	function getAttributes(blog) {
		try {
			return JSON.parse(blog.getAttribute('data-idb-blog-atts') || '{}');
		} catch (error) {
			return {};
		}
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

	function loadBlog(blog, url, shouldPushState) {
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

		loadBlog(blog, link.href, true);
	});

	window.addEventListener('popstate', function () {
		document.querySelectorAll('[data-idb-blog]').forEach(function (blog) {
			loadBlog(blog, window.location.href, false);
		});
	});
})();
