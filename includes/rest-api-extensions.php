<?php
/**
 * Core functions for extending the WP REST API with custom meta fields.
 *
 * @package   HussainasRestMetaUtility
 * @textdomain hussainas
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the custom meta field with the REST API for the 'post' post type.
 *
 * This function is hooked into 'rest_api_init' to ensure it runs
 * at the correct time.
 */
function hussainas_register_api_hooks() {

	// Define the meta key we will use in the database.
	// The underscore prefix makes it a "hidden" meta field in the WP admin UI.
	$meta_key = '_hussainas_custom_meta';

	// Register the meta field.
	register_rest_field(
		'post', // Target post type. Change to 'page' or a custom post type as needed.
		'hussainas_custom_meta', // The public name of the field in the REST API response.
		array(
			'get_callback'    => 'hussainas_get_custom_meta_callback',
			'update_callback' => 'hussainas_update_custom_meta_callback',
			'schema'          => array(
				'description' => 'A custom meta field for JavaScript applications.',
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ), // Make it available in both read (view) and write (edit) contexts.
			),
			// We pass our database meta key to the callbacks for cleaner handling.
			'args'            => array(
				'meta_key' => $meta_key,
			),
		)
	);
}
add_action( 'rest_api_init', 'hussainas_register_api_hooks' );

/**
 * GET Callback: Retrieves the value of the custom meta field.
 *
 * @param array           $object     The response object (post data).
 * @param string          $field_name The name of the field (e.g., 'hussainas_custom_meta').
 * @param WP_REST_Request $request    The current request object.
 * @param array           $args       Additional arguments from the 'args' key in register_rest_field.
 * @return mixed The meta value.
 */
function hussainas_get_custom_meta_callback( $object, $field_name, $request, $args ) {
	$meta_key = isset( $args['meta_key'] ) ? $args['meta_key'] : '_hussainas_custom_meta';
	$post_id  = $object['id'];

	// Get the meta value. 'true' returns a single value.
	return get_post_meta( $post_id, $meta_key, true );
}

/**
 * UPDATE Callback: Updates the value of the custom meta field.
 *
 * This function includes critical security checks to ensure the user
 * has permission to edit the post, and it sanitizes the input value.
 *
 * @param mixed      $value      The new value from the request payload.
 * @param WP_Post    $object     The post object (note: this is a WP_Post object, not an array).
 * @param string     $field_name The name of the field.
 * @param WP_REST_Request $request The current request object.
 * @param array      $args      Additional arguments from the 'args' key.
 * @return bool|WP_Error True on success, or WP_Error on failure/permission denial.
 */
function hussainas_update_custom_meta_callback( $value, $object, $field_name, $request, $args ) {
	$post_id  = $object->ID;
	$meta_key = isset( $args['meta_key'] ) ? $args['meta_key'] : '_hussainas_custom_meta';

	// Security Check: Ensure the user has permission to edit this specific post.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error(
			'hussainas_rest_forbidden',
			__( 'You do not have permission to edit this post.', 'hussainas' ),
			array( 'status' => 403 ) // 403 Forbidden
		);
	}

	// Sanitize the input value.
	// We use sanitize_text_field as our schema type is 'string'.
	// Use sanitize_email, absint, etc., as needed for other data types.
	$sanitized_value = sanitize_text_field( $value );

	// Update the post meta.
	$result = update_post_meta( $post_id, $meta_key, $sanitized_value );

	if ( false === $result ) {
		// This could happen if the update failed for an unknown reason.
		return new WP_Error(
			'hussainas_rest_update_failed',
			__( 'Failed to update the custom meta field.', 'hussainas' ),
			array( 'status' => 500 ) // 500 Internal Server Error
		);
	}

	// update_post_meta can also return true if the value is unchanged.
	// This is successful behavior.
	return true;
}
