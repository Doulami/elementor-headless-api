// assets/js/headless-sdk/utils.js

import { WP_BASE_URL } from './config';

/**
 * Logs a warning if the API base URL is missing or seems misconfigured
 */
export function warnIfInvalidBaseURL() {
  if (!WP_BASE_URL || !WP_BASE_URL.startsWith('http')) {
    console.warn(
      '[Elementor SDK] WP_BASE_URL is not set or invalid. ' +
      'Please define NEXT_PUBLIC_WP_API_BASE in your env vars or ensure your frontend origin points to the WordPress backend.'
    );
  }
}

/**
 * Helper to build a full API URL with optional query parameters
 */
export function buildAPIUrl(path, params = {}) {
  const url = new URL(`${WP_BASE_URL}${path}`);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      url.searchParams.append(key, value);
    }
  });
  return url.toString();
}
