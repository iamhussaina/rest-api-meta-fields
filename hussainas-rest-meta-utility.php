<?php
/**
 * Hussainas REST Meta Utility
 *
 * This utility registers a custom meta field to the WordPress REST API for specified post types.
 * It provides read (get), write (update), and permission callbacks for secure
 * interaction with JavaScript-based applications.
 *
 * @package     Hussainas\RESTMetaUtility
 * @version     1.0.0
 * @author      Hussain Ahmed Shrabon
 * @license     GPL v2
 * @link        https://github.com/iamhussaina
 * @textdomain  hussainas
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the custom REST API fields.
 *
 * This function is hooked into 'rest_api_init' and is the main entry point
 * for registering one or more REST fields.
 */
function hussainas_register_rest_fields() {

	/**
	 * An array of post types to register the meta field for.
	 * Add any custom post types (e.g., 'product', 'event') to this array.
	 *
	 * @var array
	 */
	$object_types = [ 'post' ];

	/**
	 * The name of the meta field to register.
	 * This will be the key in the REST API response.
	 *
	 * @var string
	 */
	$field_name = 'hussainas_custom_meta';

	$args = [
		'get_callback'        => 'hussainas_get_custom_meta_callback',
		'update_callback'     => 'hussainas_update_custom_meta_callback',
		'permission_callback' => 'hussainas_custom_meta_permission_check',
		'schema'              => hussainas_get_custom_meta_schema(),
	];

	/**
	 * Loop through all specified object types and register the field for each.
	 */
	foreach ( $object_types as $object_type ) {
		register_rest_field(
			$object_type,
			$field_name,
			$args
		);
	}
}
add_action( 'rest_api_init', 'hussainas_register_rest_fields' );

/**
 * Get Callback: Retrieves the meta field value.
 *
 * @param array           $object     The details of the object the field is registered to.
 * @param string          $field_name The name of the field.
 * @param WP_REST_Request $request    The current request object.
 * @return mixed The meta field value.
 */
function hussainas_get_custom_meta_callback( $object, $field_name, $request ) {
	// $object['id'] provides the post ID.
	return get_post_meta( $object['id'], $field_name, true );
}

/**
 * Update Callback: Updates the meta field value.
 *
 * @param mixed           $value      The new value being submitted.
 * @param WP_Post         $object     The post object.
 * @param string          $field_name The name of the field.
 * @return bool|WP_Error True on success, or WP_Error on failure.
 */
function hussainas_update_custom_meta_callback( $value, $object, $field_name ) {
	// Sanitize the incoming value before saving it to the database.
	$sanitized_value = sanitize_text_field( $value );

	// $object->ID provides the post ID.
	$result = update_post_meta( $object->ID, $field_name, $sanitized_value );

	if ( false === $result ) {
		// Return an error if the update failed.
		return new WP_Error(
			'hussainas_rest_update_failed',
			esc_html__( 'Failed to update custom meta field.', 'hussainas' ),
			[ 'status' => 500 ]
		);
	}

	// Return true on success.
	return true;
}

/**
 * Permission Callback: Checks if the user has permission to access the field.
 *
 * This check runs for both GET (read) and POST/PUT (write) requests.
 * We restrict access to only users who can edit the specific post.
 *
 * @param WP_REST_Request $request The current request object.
 * @return bool|WP_Error True if the user has permission, false or WP_Error otherwise.
 */
function hussainas_custom_meta_permission_check( $request ) {
	// Get the post ID from the request parameters.
	$post_id = $request->get_param( 'id' );

	if ( ! $post_id || ! is_numeric( $post_id ) ) {
		return new WP_Error(
			'hussainas_rest_invalid_post_id',
			esc_html__( 'Invalid post ID provided.', 'hussainas' ),
			[ 'status' => 400 ]
		);
	}

	// Check if the current user has the 'edit_post' capability for this post.
	if ( ! current_user_can( 'edit_post', (int) $post_id ) ) {
		return new WP_Error(
			'hussainas_rest_forbidden',
			esc_html__( 'You do not have permission to access this field.', 'hussainas' ),
			[ 'status' => 403 ]
		);
	}

	// User has permission.
	return true;
}

/**
 * Defines the schema for the custom meta field.
 *
 * Providing a schema is a best practice as it informs API consumers
 * about the data type and context.
 *
 * @return array The schema definition.
 */
function hussainas_get_custom_meta_schema() {
	return [
		'description' => esc_html__( 'A custom meta field for demonstration.', 'hussainas' ),
		'type'        => 'string',
		'context'     => [ 'view', 'edit' ], // Available in both 'view' (GET) and 'edit' (POST/PUT) contexts.
		'readonly'    => false,
	];
}
