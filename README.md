
# Elementor Headless API

The **Elementor Headless API** plugin provides a headless solution for rendering Elementor templates via REST API endpoints. This plugin is designed to integrate Elementor with a headless WordPress setup, allowing you to serve Elementor-rendered pages to a frontend that doesn't rely on WordPress' traditional theme system. It supports WooCommerce integration and provides flexible customization options.

## Features

- **Headless Elementor Rendering**: Render Elementor templates and content using REST API routes.
- **WooCommerce Support**: Fetch WooCommerce products and product pages via the REST API.
- **Token-Based Preview Access**: Secure token-based access for private previews of pages.
- **Custom Post Type Support**: Render any custom post types created in WordPress via the API.
- **Template Injection**: Dynamic header/footer injections for Elementor templates.
- **Advanced JSON Output**: JSON-based output with support for filtering fields and custom formats.
- **Selective Post Type Control**: Admin settings page to configure allowed post types for rendering.
- **AJAX-Powered Admin UI**: Easily select Elementor templates via dynamic dropdowns in the admin interface.

## Installation

1. Upload the plugin folder to `wp-content/plugins/` on your WordPress site.
2. Activate the plugin through the WordPress admin interface.
3. Configure the settings through the **Admin Settings** page (`Elementor Headless API` → `Settings`).

## Plugin Files and Structure

### Main Plugin File
- **elementor-headless-api.php**: The main plugin file that handles the plugin's core functionality.

### Includes Directory
- **class-api-routes.php**: Defines REST API routes to fetch Elementor-rendered pages and WooCommerce products.
- **class-renderer.php**: Contains the logic for rendering Elementor templates.
- **class-utils.php**: Helper functions used across the plugin.
- **class-preview-tokens.php**: Manages token-based preview functionality.
- **class-settings.php**: Handles settings for allowed post types and configuration.
- **class-woocommerce-api-routes.php**: Contains additional routes for fetching WooCommerce product information.
- **class-template-suggestions-api.php**: Provides API endpoints for template suggestions.

### Admin Directory
- **admin-settings.php**: Provides the user interface for configuring the plugin’s settings, including allowed post types and WooCommerce template IDs.

### Assets Directory
- **js/**: Contains JavaScript files for the frontend SDK, including AJAX interactions for template selectors and other dynamic content loading.

### Templates Directory
- **dynamic-placeholder.php**: A template file for dynamic content placeholders.

## Usage

Once the plugin is activated and configured, you can use the following API endpoints:

- **Page Rendering**: `GET /headless-elementor/v1/page/{slug}` – Fetch a rendered page by slug.
- **WooCommerce Product Rendering**: `GET /headless-elementor/v1/product/{slug}` – Fetch a WooCommerce product page.
- **Token-Based Preview**: `GET /headless-elementor/v1/preview/{token}` – Preview a page with a token-based access control.
- **Template Suggestions**: `GET /headless-elementor/v1/template-suggestions` – Get suggestions for Elementor templates to use in your headless setup.

### Example Request:

```bash
GET /wp-json/headless-elementor/v1/page/my-page
```

### Example Response:

```json
{
  "content": "<div>...</div>",
  "status": "success"
}
```

## Admin Settings

The plugin includes an admin settings page where you can manage the following:

- **Allowed Post Types**: Choose which post types are allowed for rendering.
- **WooCommerce Product Template ID**: Set a fallback template for WooCommerce products.
- **Inject Header/Footer**: Define which Elementor templates to inject as headers and footers.

### Token-Based Preview Access
To access a preview of any page, you need to generate a preview token using the admin interface. This token can be used to view a page via a private URL.

## Frontend SDK

The plugin comes with a modular **Frontend SDK** to help you easily fetch content via the REST API. The SDK includes functions like:

- `fetchPageBySlug`: Fetch a page by its slug.
- `fetchWooProduct`: Fetch a WooCommerce product by its slug.
- `fetchAllWooProducts`: Fetch all WooCommerce products.
- `fetchWooProductBySlug`: Fetch a WooCommerce product by its slug.

### Example Usage of SDK:

```javascript
import { fetchPageBySlug } from 'headless-elementor-sdk';

const page = await fetchPageBySlug('my-page');
console.log(page.content);
```

## Roadmap

- Add support for ACF (Advanced Custom Fields) rendering.
- Improve the JSON output structure with selective field rendering.
- Add caching strategies to enhance performance.
- Provide more granular control over Elementor widget rendering.

## Contributing

Feel free to fork the repository and submit pull requests. We welcome contributions, bug fixes, and feature suggestions!
