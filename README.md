# Elementor Headless API

Expose Elementor-rendered pages as clean, static HTML via REST API — ideal for headless frontends (e.g., Next.js, Astro, Nuxt, etc.).

---

## 🔧 Features

- ✅ REST API Endpoints:
  - `/wp-json/headless-elementor/v1/page/{id}`
  - `/wp-json/headless-elementor/v1/slug/{slug}`
  - `/wp-json/headless-elementor/v1/pages` (list all published Elementor pages/posts)
  - `/wp-json/headless-elementor/v1/status` (plugin health)

- ⚡ **Elementor Rendering**:
  - Uses `get_builder_content_for_display()`
  - Includes Elementor inline styles
  - Clean, stripped-down HTML (no WP headers/footers)

- 🧹 Clean HTML Output:
  - No admin bar, meta tags, or theme interference
  - Errors and notices stripped from output (ongoing improvement)

- 🧁 Caching:
  - HTML rendered output is cached via WordPress transients
  - Use `?nocache=1` to bypass cache
  - Cache TTL: 12 hours (default)

- 🧪 Debug Mode:
  - Use `?debug=1` for info on:
    - Elementor version
    - Render timestamp
    - Whether cache was used

- 🔐 Token-Based Preview:
  - Per-post preview token via meta box
  - Preview via: `/slug/post-slug?token=XYZ`
  - Expirable or revocable tokens (TBD)

- ⚙️ Admin Settings:
  - Enable specific post types for API rendering
  - UI under Settings → Elementor Headless

---

## 📦 Usage

### Get Structured JSON with Fields

Use `?format=json` to get structured API responses. Control fields with `?fields=`:

```
/wp-json/headless-elementor/v1/slug/home?format=json&fields=id,title,html,acf,meta,terms
```

Supported fields:
- `id`, `title`, `slug`, `type`
- `html` (Elementor-rendered)
- `acf` (if ACF plugin is active)
- `meta` (post meta fields)
- `terms` (taxonomy slugs)
- `debug` (optional, use `?debug=1`)

### Get Page by ID
```
/wp-json/headless-elementor/v1/page/123
```

### Get Page by Slug
```
/wp-json/headless-elementor/v1/slug/home
```

### Clear Cache for a Page
```
/wp-json/headless-elementor/v1/slug/home?nocache=1
```

### View Debug Output
```
/wp-json/headless-elementor/v1/slug/home?debug=1
```

### Token Preview
```
/wp-json/headless-elementor/v1/slug/sample-page?token=abc123
```

---

## 🧩 Coming Soon

- 🌍 Global styles (Elementor Site Settings)
- 🛒 WooCommerce rendering support
- 🧱 Header/Footer injection templates
- 🔐 JWT-based secure preview access
- 📊 Logs/metrics for preview link usage
- 📦 Frontend SDK for data fetching (JS/TS)

---

### ✅ Already Implemented:
- 🧠 ACF fields integration (via `?fields=acf`)
- 🧬 JSON fallback support (`?format=json`)
- 🎯 Field filtering via `fields=html,meta,...`)
---

## 📁 Folder Structure

```
elementor-headless-api/
├── elementor-headless-api.php         # Main plugin loader
├── README.md                          # Plugin documentation
├── admin/
│   └── admin-settings.php             # Admin settings UI
├── includes/
│   ├── class-api-routes.php           # REST API endpoints
│   ├── class-renderer.php             # Elementor rendering
│   ├── class-preview-tokens.php       # Token preview logic
│   ├── class-settings.php             # Post type config
│   └── class-utils.php                # Helpers
├── templates/
│   └── dynamic-placeholder.php        # Future injections
```

---

## 👥 Maintained by
**Wappdev** – [wappdev.co.uk](https://wappdev.co.uk)

---

## 🪪 License
GPLv2 or later. Built for open headless WordPress development.

---

## 📦 JSON Output (Structured)

You can fetch clean JSON responses by appending `?format=json` to any endpoint.

### Example:
```
/wp-json/headless-elementor/v1/slug/home?format=json
```

### Optional: Limit returned fields using `fields=...`:
```
/wp-json/headless-elementor/v1/slug/home?format=json&fields=id,title,html,acf
```

### Supported Fields:
- `id` — Post ID
- `title` — Post title
- `slug` — Post slug
- `type` — Post type
- `html` — Rendered Elementor HTML
- `acf` — ACF fields (if ACF is active)
- `meta` — Post meta key/values
- `terms` — Assigned taxonomy terms (slugs)
- `debug` — Debug metadata (if `?debug=1`)

### Combined Example:
```
/wp-json/headless-elementor/v1/slug/about?format=json&fields=title,acf,meta,terms&debug=1
```

---

This allows full control over how much content your frontend needs to fetch — similar to GraphQL, but over REST.


---

## 🛒 WooCommerce Integration

Expose WooCommerce product data via REST for headless storefronts.

- **Endpoints:**
  - `/wp-json/headless-elementor/v1/woo/products` – List all products
  - `/wp-json/headless-elementor/v1/woo/product/{id}` – Get product by ID
  - `/wp-json/headless-elementor/v1/woo/product/{slug}` – Get product by slug

- **Returns:**
  - JSON response with fields like:
    - `id`, `name`, `price`, `regular_price`, `sale_price`, `slug`, `permalink`, etc.
  - Easily extendable to return full Elementor-rendered HTML product pages.

- **Frontend SDK:**
  - SDK includes:
    - `fetchAllWooProducts()`
    - `fetchWooProduct(id)`
    - `fetchWooProductBySlug(slug)`
  - Useful for React, Next.js, Astro, or any frontend framework.

