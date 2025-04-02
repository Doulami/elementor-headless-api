// assets/js/headless-sdk/pages.js

import { WP_BASE_URL } from './config';

/**
 * Fetch a page/post rendered via Elementor by its ID
 * @param {number} id
 * @param {Object} options
 * @param {boolean} [options.nocache=false]
 * @param {boolean} [options.debug=false]
 * @returns {Promise<string|object>} HTML string or JSON object
 */
export async function fetchPageById(id, options = {}) {
  const { nocache = false, debug = false, json = false } = options;
  let url = `${WP_BASE_URL}/page/${id}`;
  const params = [];

  if (nocache) params.push('nocache=1');
  if (debug) params.push('debug=1');
  if (json) params.push('format=json');

  if (params.length) {
    url += '?' + params.join('&');
  }

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch page ID: ${id}`);
  return json ? await resp.json() : await resp.text();
}

/**
 * Fetch a page/post rendered via Elementor by its slug
 * @param {string} slug
 * @param {Object} options
 * @param {boolean} [options.nocache=false]
 * @param {boolean} [options.debug=false]
 * @param {boolean} [options.json=false]
 * @returns {Promise<string|object>}
 */
export async function fetchPageBySlug(slug, options = {}) {
  const { nocache = false, debug = false, json = false } = options;
  let url = `${WP_BASE_URL}/slug/${slug}`;
  const params = [];

  if (nocache) params.push('nocache=1');
  if (debug) params.push('debug=1');
  if (json) params.push('format=json');

  if (params.length) {
    url += '?' + params.join('&');
  }

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch page slug: ${slug}`);
  return json ? await resp.json() : await resp.text();
}

/**
 * Fetch all public pages/posts
 * @returns {Promise<Array<{ id: number, title: string, slug: string }>>}
 */
export async function fetchAllPages() {
  const url = `${WP_BASE_URL}/pages`;
  const resp = await fetch(url);
  if (!resp.ok) throw new Error('Failed to fetch pages list');
  return await resp.json();
}
