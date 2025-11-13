# REST API Meta Field Utility

A REST API Meta Field Utility for WordPress to register a custom read/write meta field to the REST API. This module is designed to be included in a theme and provides a simple way for JavaScript-based applications (like React or Vue) to get and set post meta data.

This utility does **not** create any admin UI for the meta field. It is purely for REST API exposure.

## Features

* Registers a `hussainas_custom_meta` field to the `post` post type endpoint.
* Provides both `get` (read) and `update` (write) capabilities.
* Includes permission checks (`current_user_can`) before updating data.
* Includes data sanitization (`sanitize_text_field`) on update.
* Uses a prefixed, "hidden" meta key (`_hussainas_custom_meta`) in the database.

---

## 1. Installation

1.  Place the entire `rest-api-meta-fields` directory into your active WordPress theme's folder. The recommended structure is:

    ```
    /wp-content/themes/your-theme/
    |-- ... (other theme files)
    |-- /rest-api-meta-fields/
    |   |-- load.php
    |   |-- includes/
    |   |   |-- rest-api-extensions.php
    |   |-- README.md
    |-- functions.php
    ```

2.  Open your theme's `functions.php` file and add the following PHP line to include the utility:

    ```php
    // Load the REST API meta field utility.
    require get_template_directory() . '/rest-api-meta-fields/load.php';
    ```

3.  Save the file. The REST API field will be active immediately.

---

## 2. How to Use

Once installed, the `hussainas_custom_meta` field will be available in your REST API responses for posts.

**Endpoint:** `GET /wp-json/wp/v2/posts/<id>`

### Example: Reading Data (GET)

A `GET` request to `/wp-json/wp/v2/posts/123` will now include the field in its response:

**Response:**
```json
{
  "id": 123,
  "title": { "rendered": "Hello World" },
  // ... other post data
  "hussainas_custom_meta": "This is the saved meta value"
}
```

### Example: Writing Data (POST)

You can update the value by sending a `POST` request to the same endpoint. You must be **authenticated** (e.g., using a Nonce, JWT, or Application Password) and have the user role/capability to edit the post.

**Request:** `POST /wp-json/wp/v2/posts/123`
**Body (JSON):**

```json
{
  "hussainas_custom_meta": "This is the new updated value."
}
```

The API will respond with the updated post object, including the newly saved value, if successful. If permission is denied, it will return a 403 error.

---

## 3. Customization

If you need to change the post type or the meta field name, you can edit the `includes/rest-api-extensions.php` file.

### Change Post Type

To register this field for a `page` or a custom post type (e.g., `product`), change the first parameter in `register_rest_field`:

```php
// Inside hussainas_register_api_hooks()

register_rest_field(
	'product', // Changed from 'post' to 'product'
	'hussainas_custom_meta',
	array(
		// ... callbacks and schema
	)
);
```

You can also register it for multiple post types by passing an array:

```php
register_rest_field(
	array( 'post', 'page' ), // Registered for both posts and pages
	'hussainas_custom_meta',
	array(
		// ... callbacks and schema
	)
);
```
