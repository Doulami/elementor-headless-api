// assets/js/headless-sdk/config.js

/**
 * Tries to auto-detect the WordPress REST API base from the frontend origin
 * e.g. https://frontend.com → https://frontend.com/wp-json/headless-elementor/v1
 *
 * Can be overridden via NEXT_PUBLIC_WP_API_BASE (Netlify/Vercel-friendly)
 */

export const WP_BASE_URL =
  process.env.NEXT_PUBLIC_WP_API_BASE ||
  (() => {
    try {
      const origin = typeof window !== 'undefined' ? window.location.origin : '';
      return origin ? `${origin}/wp-json/headless-elementor/v1` : '';
    } catch {
      return '';
    }
  })();
