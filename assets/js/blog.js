(function () {
	'use strict';

	var config = window.idbCoreBlog || {};
	var attributesCache = new WeakMap();
	var infiniteObservers = new WeakMap();

	if (!config.ajaxUrl || !config.action || !config.nonce) {
		return;
	}

	function getBlog(element) {
		return element ? element.closest('[data-idb-blog]') : null;
	}

	function getAjaxLink(element) {
		return element ? element.closest('.idb-blog__pagination a, .idb-blog__filter-link') : null;
	}

	function getLoadMoreLink(element) {
		return element ? element.closest('[data-idb-blog-load-more], [data-idb-blog-infinite-link]') : null;
	}

	function getInfiniteLink(blog) {
		return blog ? blog.querySelector('[data-idb-blog-infinite-link]') : null;
	}

	function getAjaxForm(element) {
		return element ? element.closest('.idb-blog__search') : null;
	}

	function setLoading(blog, isLoading, isAppending) {
		var controls = blog.querySelectorAll(
			'.idb-blog__pagination a, .idb-blog__filter-link, .idb-blog__search-input, .idb-blog__search-button, [data-idb-blog-load-more], [data-idb-blog-infinite-link]'
		);

		blog.classList.toggle('idb-blog--is-loading', isLoading);
		blog.classList.toggle('idb-blog--is-appending', isLoading && !!isAppending);
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

	function removeSkeleton(blog) {
		blog.querySelectorAll('[data-idb-blog-skeleton]').forEach(function (skeleton) {
			skeleton.remove();
		});
	}

	function getGrid(blog) {
		return blog ? blog.querySelector('[data-idb-blog-grid]') : null;
	}

	function getGridColumnClass(grid) {
		if (!grid) {
			return '';
		}

		var match = String(grid.className).match(/idb-blog__grid--columns-[1-4]/);

		return match ? match[0] : '';
	}

	function addSkeleton(blog, isAppending) {
		var grid = getGrid(blog);
		var skeleton = document.createElement('div');
		var count = isAppending ? 2 : 4;

		removeSkeleton(blog);

		skeleton.className = 'idb-blog__skeleton idb-blog__grid ' + getGridColumnClass(grid);
		skeleton.setAttribute('data-idb-blog-skeleton', '1');
		skeleton.setAttribute('aria-hidden', 'true');

		for (var index = 0; index < count; index += 1) {
			var card = document.createElement('div');

			card.className = 'idb-blog-skeleton-card';
			card.innerHTML = '<span class="idb-blog-skeleton-card__media"></span><span class="idb-blog-skeleton-card__line idb-blog-skeleton-card__line--short"></span><span class="idb-blog-skeleton-card__line"></span><span class="idb-blog-skeleton-card__line"></span>';
			skeleton.appendChild(card);
		}

		if (isAppending && grid) {
			grid.after(skeleton);
			return;
		}

		if (grid) {
			grid.before(skeleton);
			return;
		}

		blog.appendChild(skeleton);
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

	function appendBlog(blog, html) {
		var template = document.createElement('template');
		var grid = getGrid(blog);

		template.innerHTML = html.trim();

		var nextBlog = template.content.querySelector('[data-idb-blog]');
		var nextGrid = getGrid(nextBlog);

		if (!grid || !nextBlog || !nextGrid) {
			return null;
		}

		Array.prototype.slice.call(nextGrid.children).forEach(function (card) {
			grid.appendChild(card);
		});

		blog.querySelectorAll('.idb-blog__load-more, .idb-blog__infinite').forEach(function (control) {
			control.remove();
		});

		Array.prototype.slice.call(nextBlog.querySelectorAll('.idb-blog__load-more, .idb-blog__infinite')).forEach(function (control) {
			grid.after(control);
		});

		blog.setAttribute('data-idb-blog-atts', nextBlog.getAttribute('data-idb-blog-atts') || '{}');
		blog.setAttribute('data-idb-blog-pagination-mode', nextBlog.getAttribute('data-idb-blog-pagination-mode') || 'pagination');
		attributesCache.delete(blog);

		return blog;
	}

	function getFixedHeaderOffset() {
		var selectors = ['#wpadminbar', '.site-header', '.elementor-location-header', 'header'];
		var offset = 20;

		selectors.forEach(function (selector) {
			var element = document.querySelector(selector);

			if (!element) {
				return;
			}

			var styles = window.getComputedStyle(element);
			var rect = element.getBoundingClientRect();

			if ((styles.position === 'fixed' || styles.position === 'sticky') && rect.top <= 1) {
				offset = Math.max(offset, rect.height + 20);
			}
		});

		return offset;
	}

	function isFullyVisible(element) {
		var rect = element.getBoundingClientRect();
		var offset = getFixedHeaderOffset();

		return rect.top >= offset && rect.bottom <= window.innerHeight;
	}

	function smartScrollToBlog(blog) {
		if (!blog || isFullyVisible(blog)) {
			return;
		}

		window.requestAnimationFrame(function () {
			var top = blog.getBoundingClientRect().top + window.pageYOffset - getFixedHeaderOffset();

			window.scrollTo({
				behavior: 'smooth',
				top: Math.max(0, top),
			});
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

	function loadBlog(blog, url, shouldPushState, mode) {
		if (!blog || blog.dataset.idbBlogLoading === '1') {
			return Promise.resolve(null);
		}

		var body = new URLSearchParams();
		var isAppending = mode === 'append';
		var loadedBlog = null;

		body.append('action', config.action);
		body.append('nonce', config.nonce);
		body.append('url', url);
		body.append('atts', JSON.stringify(getAttributes(blog)));

		setLoading(blog, true, isAppending);
		addSkeleton(blog, isAppending);

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

				var nextBlog = isAppending ? appendBlog(blog, payload.data.html) : replaceBlog(blog, payload.data.html);
				loadedBlog = nextBlog;

				if (nextBlog && shouldPushState) {
					window.history.pushState({ idbBlog: true }, '', url);
				}

				if (nextBlog && !isAppending) {
					attributesCache.delete(nextBlog);
					initInfinite(nextBlog);
					smartScrollToBlog(nextBlog);
				}

				return nextBlog;
			})
			.catch(function () {
				if (shouldPushState) {
					window.location.href = url;
				}
			})
			.finally(function () {
				removeSkeleton(blog);

				if (blog.isConnected) {
					setLoading(blog, false);
				}

				if (loadedBlog && isAppending) {
					initInfinite(loadedBlog);
				}
			});
	}

	function loadNextPage(blog) {
		var link = getLoadMoreLink(blog) || getInfiniteLink(blog);

		if (!link || !link.href || blog.dataset.idbBlogLoading === '1') {
			return Promise.resolve(null);
		}

		return loadBlog(blog, link.href, false, 'append');
	}

	function initInfinite(blog) {
		var previousObserver = infiniteObservers.get(blog);
		var trigger = blog.querySelector('[data-idb-blog-infinite]');

		if (previousObserver && previousObserver.disconnect) {
			previousObserver.disconnect();
		}

		if (!trigger || blog.getAttribute('data-idb-blog-pagination-mode') !== 'infinite_scroll') {
			return;
		}

		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function (entries) {
				if (entries.some(function (entry) { return entry.isIntersecting; })) {
					loadNextPage(blog);
				}
			}, {
				rootMargin: '360px 0px',
			});

			observer.observe(trigger);
			infiniteObservers.set(blog, observer);
			return;
		}

		var ticking = false;
		var onScroll = function () {
			if (ticking) {
				return;
			}

			ticking = true;

			window.requestAnimationFrame(function () {
				ticking = false;

				if (!blog.isConnected) {
					window.removeEventListener('scroll', onScroll);
					return;
				}

				if (trigger.getBoundingClientRect().top < window.innerHeight + 360) {
					loadNextPage(blog);
				}
			});
		};

		window.addEventListener('scroll', onScroll, { passive: true });
		infiniteObservers.set(blog, {
			disconnect: function () {
				window.removeEventListener('scroll', onScroll);
			},
		});
		onScroll();
	}

	document.addEventListener('click', function (event) {
		var loadMoreLink = getLoadMoreLink(event.target);
		var loadMoreBlog = getBlog(loadMoreLink);

		if (loadMoreLink && loadMoreBlog && loadMoreLink.href) {
			event.preventDefault();
			loadNextPage(loadMoreBlog);
			return;
		}

		var link = getAjaxLink(event.target);
		var blog = getBlog(link);

		if (!link || !blog || !link.href) {
			return;
		}

		event.preventDefault();

		if (blog.dataset.idbBlogLoading === '1') {
			return;
		}

		loadBlog(blog, link.href, true, 'replace');
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

		loadBlog(blog, getFormUrl(form), true, 'replace');
	});

	window.addEventListener('popstate', function () {
		document.querySelectorAll('[data-idb-blog]').forEach(function (blog) {
			loadBlog(blog, window.location.href, false, 'replace');
		});
	});

	document.querySelectorAll('[data-idb-blog]').forEach(initInfinite);
})();
