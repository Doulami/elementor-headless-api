/**
 * Minimal "SDK" for fetching Elementor Headless + WooCommerce data
 * Usage:
 *   import { fetchPageBySlug, fetchAllWooProducts, fetchWooProduct } from 'headless-elementor-sdk';
 */

// Replace this with your actual WordPress site domain
const WP_BASE_URL = 'https://your-site.com/wp-json/headless-elementor/v1';

/**
 * Fetch an Elementor-rendered page by slug
 * @param {string} slug
 * @param {boolean} [nocache=false]
 * @param {boolean} [debug=false]
 */
export async function fetchPageBySlug(slug, nocache = false, debug = false) {
  let url = `${WP_BASE_URL}/slug/${slug}`;
  if (nocache) url += '?nocache=1';
  if (debug) url += (nocache ? '&' : '?') + 'debug=1';

  const resp = await fetch(url);
  if (!resp.ok) {
    throw new Error(`Failed fetching page: ${slug}`);
  }
  return resp.json();
}

/**
 * Fetch the list of WooCommerce products (JSON data)
 */
export async function fetchAllWooProducts() {
  const url = `${WP_BASE_URL}/woo/products`;
  const resp = await fetch(url);
  if (!resp.ok) {
    throw new Error(`Failed fetching Woo products`);
  }
  return resp.json();
}

/**
 * Fetch a single product by ID
 * @param {number} productId
 */
export async function fetchWooProduct(productId) {
  const url = `${WP_BASE_URL}/woo/product/${productId}`;
  const resp = await fetch(url);
  if (!resp.ok) {
    throw new Error(`Failed fetching product ID: ${productId}`);
  }
  return resp.json();
}

/**
 * Fetch a single product by slug
 * @param {string} productSlug
 */
export async function fetchWooProductBySlug(productSlug) {
  const url = `${WP_BASE_URL}/woo/product/${productSlug}`;
  const resp = await fetch(url);
  if (!resp.ok) {
    throw new Error(`Failed fetching product slug: ${productSlug}`);
  }
  return resp.json();
}
