<?php
/**
 * Plugin Name: Plugin for remove edit or delete user role according to higher level
 * Plugin URI: https://github.com/jakubkaderavek/wpj-remove-edit-role-with-higher-level
 * Version: 1.0
 * Author: Jakub Kadeřávek
 * Author URI: https://www.wpjakub.cz/
 */


/**
 * Remove bulk action from user admin page for users /wp-admin/users.php
 */

add_filter( 'editable_roles', function ( $all_roles ) {
	return wpj_get_roles_with_lower_or_same_level( $all_roles );
} );

/**
 * Remove cap for edit or delete user with highter level  /wp-admin/users.php
 */
add_filter( 'map_meta_cap', function ( $caps, $cap, $user_ID, $args ) {
	if ( ( $cap === 'edit_user' || $cap === 'delete_user' ) && $args ) {
		$the_user = get_userdata( $user_ID ); // The user performing the task
		$user     = get_userdata( $args[0] ); // The user being edited/deleted

		if ( $the_user && $user ) {
			$allowed = array_keys( wpj_get_roles_with_lower_or_same_level( $GLOBALS['wp_roles']->roles ) );;
			if ( array_diff( $user->roles, $allowed ) ) {
				// Target user has roles outside of our limits
				$caps[] = 'not_allowed';
			}
		}
	}

	return $caps;
}, 10, 4 );


/**
 * @param $all_roles
 *
 * @return array - roles with lower or same level
 */
function wpj_get_roles_with_lower_or_same_level( $all_roles ) {
	$user       = wp_get_current_user();
	$next_level = 'level_' . ( $user->user_level + 1 );


	foreach ( $all_roles as $name => $role ) {
		if ( isset( $role['capabilities'][ $next_level ] ) ) {
			unset( $all_roles[ $name ] );
		}
	}

	return $all_roles;
}
