// assets/js/headless-sdk/products.js

import { WP_BASE_URL } from './config';

/**
 * Fetch all WooCommerce products (JSON only)
 * @returns {Promise<Array>}
 */
export async function fetchAllWooProducts() {
  const url = `${WP_BASE_URL}/woo/products`;
  const resp = await fetch(url);
  if (!resp.ok) throw new Error('Failed to fetch WooCommerce products');
  return await resp.json();
}

/**
 * Fetch a single WooCommerce product by ID
 * @param {number} id
 * @param {Object} options
 * @param {boolean} [options.render=false] - If true, returns rendered HTML instead of JSON
 * @param {boolean} [options.nocache=false]
 * @param {boolean} [options.debug=false]
 * @returns {Promise<string|object>}
 */
export async function fetchWooProduct(id, options = {}) {
  const { render = false, nocache = false, debug = false } = options;
  let url = `${WP_BASE_URL}/woo/product/${id}`;
  const params = [];

  if (render) params.push('render=1');
  if (nocache) params.push('nocache=1');
  if (debug) params.push('debug=1');

  if (params.length) {
    url += '?' + params.join('&');
  }

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch Woo product ID: ${id}`);
  return render ? await resp.text() : await resp.json();
}

/**
 * Fetch a single WooCommerce product by slug
 * @param {string} slug
 * @param {Object} options
 * @param {boolean} [options.render=false]
 * @param {boolean} [options.nocache=false]
 * @param {boolean} [options.debug=false]
 * @returns {Promise<string|object>}
 */
export async function fetchWooProductBySlug(slug, options = {}) {
  const { render = false, nocache = false, debug = false } = options;
  let url = `${WP_BASE_URL}/woo/product/${slug}`;
  const params = [];

  if (render) params.push('render=1');
  if (nocache) params.push('nocache=1');
  if (debug) params.push('debug=1');

  if (params.length) {
    url += '?' + params.join('&');
  }

  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch Woo product slug: ${slug}`);
  return render ? await resp.text() : await resp.json();
}
