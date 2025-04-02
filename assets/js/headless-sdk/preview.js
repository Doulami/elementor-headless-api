// assets/js/headless-sdk/preview.js

import { WP_BASE_URL } from './config';

/**
 * Fetch a preview of a post/page by ID with a token
 * @param {number|string} postId
 * @param {string} token
 * @param {Object} options
 * @param {boolean} [options.render=true] - If true, returns HTML. If false, returns JSON.
 * @param {boolean} [options.debug=false]
 * @returns {Promise<string|object>}
 */
export async function fetchPreviewById(postId, token, options = {}) {
  const { render = true, debug = false } = options;
  let url = `${WP_BASE_URL}/page/${postId}?token=${encodeURIComponent(token)}`;

  if (!render) url += '&format=json';
  if (debug) url += '&debug=1';

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch preview for post ID: ${postId}`);
  return render ? await resp.text() : await resp.json();
}

/**
 * Fetch a preview by slug (if your API supports it)
 * @param {string} slug
 * @param {string} token
 * @param {Object} options
 * @param {boolean} [options.render=true]
 * @param {boolean} [options.debug=false]
 * @returns {Promise<string|object>}
 */
export async function fetchPreviewBySlug(slug, token, options = {}) {
  const { render = true, debug = false } = options;
  let url = `${WP_BASE_URL}/slug/${slug}?token=${encodeURIComponent(token)}`;

  if (!render) url += '&format=json';
  if (debug) url += '&debug=1';

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch preview for slug: ${slug}`);
  return render ? await resp.text() : await resp.json();
}
