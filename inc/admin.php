<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cheatin\' uh?' );
}

/*------------------------------------------------------------------------------------------------*/
/* !ACTIVATION, DEACTIVATION, UNINSTALL ========================================================= */
/*------------------------------------------------------------------------------------------------*/

// ACTIVATION: set a transient for displaying a help message, flush rewrite rules with a possible existing author base.

function sf_auc_activation() {
	update_option( 'sf_auc_first_message', 1 );
	sf_auc_author_base();
	flush_rewrite_rules();
}

register_activation_hook( SF_AUC_FILE, 'sf_auc_activation' );


// DEACTIVATION: flush rewrite rules with "author" as author_base.

function sf_auc_deactivation() {
	global $wp_rewrite;

	if ( $wp_rewrite && is_object( $wp_rewrite ) && 'author' !== $wp_rewrite->author_base ) {
		$wp_rewrite->author_base = 'author';
		flush_rewrite_rules();
	}
}

register_deactivation_hook( SF_AUC_FILE, 'sf_auc_deactivation' );


// UNINSTALL: delete author base option.

function sf_auc_uninstaller() {
	delete_option( 'author_base' );
	sf_auc_deactivation();
}

register_uninstall_hook( SF_AUC_FILE, 'sf_auc_uninstaller' );


/*------------------------------------------------------------------------------------------------*/
/* !I18N ======================================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( 'init', 'sf_auc_lang_init' );

function sf_auc_lang_init() {
	load_plugin_textdomain( 'sf-author-url-control', false, basename( dirname( SF_AUC_FILE ) ) . '/languages' );
}


/*------------------------------------------------------------------------------------------------*/
/* !LINKS TO THE RELEVANT PAGES ================================================================= */
/*------------------------------------------------------------------------------------------------*/

// Add a "settings link".

add_filter( 'plugin_action_links_' . SF_AUC_BASENAME,               'sf_auc_settings_action_links', 10, 2 );
add_filter( 'network_admin_plugin_action_links_' . SF_AUC_BASENAME, 'sf_auc_settings_action_links', 10, 2 );

function sf_auc_settings_action_links( $links, $file ) {
	$links['settings'] = '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '#author_base">' . __( 'Permalinks' ) . '</a>';
	return $links;
}


// Activation message.

add_action( 'admin_notices', 'sf_auc_activation_message' );

function sf_auc_activation_message() {
	global $pagenow;

	if ( 'plugins.php' !== $pagenow || ! get_option( 'sf_auc_first_message' ) ) {
		return;
	}

	echo "<div class=\"updated\">\n";
		echo '<p>';
			printf(
				__( '<strong>SF Author Url Control</strong>: Now you can go to Settings &#8250; %1$sPermalinks</a> to change the authors base url. Also, go to %2$sUsers</a> and chose a user profile, %3$slike your own</a>, for the user&#8217;s slug.', 'sf-author-url-control' ),
				'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '#author_base">',
				'<a href="' . esc_url( self_admin_url( 'users.php' ) ) . '">',
				'<a href="' . esc_url( self_admin_url( 'profile.php' ) ) . ( is_network_admin() ? '?wp_http_referer=' . urlencode( network_admin_url( 'users.php' ) ) : '' ) . '#user_nicename">'
			);
		echo "</p>\n";
	echo "</div>\n";

	delete_option( 'sf_auc_first_message' );
}


/*------------------------------------------------------------------------------------------------*/
/* !COLUMNS IN USERS LIST ======================================================================= */
/*------------------------------------------------------------------------------------------------*/

// Column title.

add_filter( 'manage_users_columns', 'sf_auc_manage_users_columns' );

function sf_auc_manage_users_columns( $defaults ) {
	$defaults['user-nicename'] = __( 'URL slug', 'sf-author-url-control' );
	return $defaults;
}


// Column content.

add_filter( 'manage_users_custom_column', 'sf_auc_manage_users_custom_column', 10, 3 );

function sf_auc_manage_users_custom_column( $default, $column_name, $user_id ) {
	if ( 'user-nicename' !== $column_name ) {
		return $default;
	}

	$userdata = get_userdata( (int) $user_id );
	$userdata->user_nicename = ! empty( $userdata->user_nicename ) ? sanitize_title( $userdata->user_nicename ) : '';

	if ( ! $userdata->user_nicename ) {
		$userdata->user_nicename = __( 'Empty slug!', 'sf-author-url-control' );
		return '<span style="color:red;font-weight:bold">' . $userdata->user_nicename . '</span>';
	}

	if ( sf_auc_user_can_edit_user_slug( $user_id ) && sanitize_title( $userdata->user_login ) !== $userdata->user_nicename ) {
		return '<span style="color:green">' . $userdata->user_nicename . '</span>';
	}

	return $userdata->user_nicename;
}


// Some CSS

add_action( 'admin_print_scripts-users.php', 'sf_auc_manage_users_column_css' );

function sf_auc_manage_users_column_css() {
	echo '<style type="text/css">.manage-column.column-user-nicename{width:12em}</style>' . "\n";
}


/*------------------------------------------------------------------------------------------------*/
/* !AUTHOR BASE: FIELD IN THE PERMALINKS PAGE =================================================== */
/*------------------------------------------------------------------------------------------------*/

// Add the field.

add_action( 'load-options-permalink.php', 'sf_auc_register_setting' );

function sf_auc_register_setting() {
	add_settings_field( 'author_base', __( 'Authors page base', 'sf-author-url-control' ), 'sf_auc_author_base_field', 'permalink', 'optional', array( 'label_for' => 'author_base' ) );

	wp_enqueue_script( 'plugin-install' );
	add_thickbox();
}


// The field.

function sf_auc_author_base_field( $args ) {
	$blog_prefix = '';
	$author_base = sf_auc_get_author_base();
	$author_base = 'author' !== $author_base ? $author_base : '';

	if ( is_multisite() && ! is_subdomain_install() && is_main_site() ) {
		$blog_prefix = '/blog';
		$author_base = $author_base ? '/' . $author_base : $author_base;
	}

	echo $blog_prefix . ' <input name="author_base" id="author_base" type="text" value="' . $author_base . '" class="regular-text code"/> <span class="description">(' . __( 'Leave empty for default value: author', 'sf-author-url-control' ) . ')</span>';

	if ( realpath( path_join( WP_PLUGIN_DIR, 'user-name-security' ) ) ) {
		return;
	}

	if ( defined( 'NAH_LEAVE_ME_ALONE' ) && NAH_LEAVE_ME_ALONE ) {
		return;
	}

	$plugin_name = 'SX User Name Security';
	$url         = esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=user-name-security&TB_iframe=true&width=600&height=550' ) );
	echo '<p>';
		printf(
			__( 'If you also want to remove the display of your login in the CSS classes from the <code>&lt;body&gt;</code> tag of your site, and force your members to change their display name, I advise you to install %s.', 'sf-author-url-control' ),
			'<a class="thickbox open-plugin-details-modal" href="' . $url . '" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $plugin_name ) ) . '" data-title="' . $plugin_name . '">' . $plugin_name . '</a>'
		);
	echo "</p>\n";
}


// Save the author base and display error notices.

add_action( 'load-options-permalink.php', 'sf_auc_save_author_base' );

function sf_auc_save_author_base() {
	global $wp_rewrite;

	if ( ! isset( $_POST['submit'], $_POST['author_base'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'update-permalink' );

	$author_base = sanitize_title( $_POST['author_base'] );

	// Check for identical slug
	if ( ! $author_base || 'author' === $author_base ) {
		sf_auc_set_author_base();
		return;
	}

	$message       = false;
	$is_first_blog = is_multisite() && ! is_subdomain_install() && is_main_site();

	// Get all the available slugs
	$bases = array();	// slug => what

	// The "obvious" ones
	$bases['blog']                         = 'blog';
	$bases['date']                         = 'date';
	$bases[ $wp_rewrite->search_base ]     = 'search_base';
	$bases[ $wp_rewrite->comments_base ]   = 'comments_base';
	$bases[ $wp_rewrite->pagination_base ] = 'pagination_base';
	$bases[ $wp_rewrite->feed_base ]       = 'feed_base';

	// RSS
	if ( $wp_rewrite->feeds ) {
		foreach ( $wp_rewrite->feeds as $item ) {
			$bases[ $item  ] = $item;
		}
	}

	// Post types and taxos
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	$taxos      = get_taxonomies( array( 'public' => true ), 'objects' );
	$whatever   = array_merge( $taxos, $post_types );

	if ( $whatever ) {
		foreach ( $whatever as $what ) {
			// Singular
			if ( ! empty( $what->rewrite['slug'] ) ) {
				$bases[ $what->rewrite['slug'] ] = $what->name;
			} else {
				$bases[ $what->name ] = $what->name;
			}
			// Archive
			if ( ! empty( $what->has_archive ) && true !== $what->has_archive ) {
				$bases[ $what->has_archive ] = $what->name;
			}
		}
	}

	if ( ! empty( $bases[ $author_base ] ) ) {
		// Oops!
		if ( taxonomy_exists( $bases[ $author_base ] ) ) {
			// Taxos.
			$message = __( ' (for a taxonomy)', 'sf-author-url-control' );
		} elseif ( post_type_exists( $bases[ $author_base ] ) ) {
			// Post type.
			$message = __( ' (for a custom post type)', 'sf-author-url-control' );
		} else {
			$message = '';
		}
	} elseif ( get_page_by_path( $author_base ) ) {
		// Page
		$message = __( ' (for a page)', 'sf-author-url-control' );

	} elseif ( trim( get_option( 'permalink_structure' ), '/' ) === trim( $wp_rewrite->front . '%postname%', '/' ) && get_page_by_path( $author_base, 'OBJECT', 'post' ) ) {
		// Post
		$message = __( ' (for a post)', 'sf-author-url-control' );
	}

	if ( false !== $message ) {
		add_settings_error( 'permalink', 'wrong_author_base', sprintf( __( '<strong>ERROR</strong>: This authors page base is already used somewhere else%s. Please choose another one.', 'sf-author-url-control' ), $message ) );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		return;
	}

	sf_auc_set_author_base( $author_base );
}


/*------------------------------------------------------------------------------------------------*/
/* !USER PROFILE ================================================================================ */
/*------------------------------------------------------------------------------------------------*/

// Add the field.

add_action( 'show_user_profile', 'sf_auc_edit_user_options' );		// Own profile
add_action( 'edit_user_profile', 'sf_auc_edit_user_options' );		// Others

function sf_auc_edit_user_options() {
	global $user_id;

	$user_id = isset( $user_id ) ? (int) $user_id : 0;

	if ( ! ( $userdata = get_userdata( $user_id ) ) ) {
		return;
	}

	if ( ! sf_auc_user_can_edit_user_slug() ) {
		return;
	}

	$def_user_nicename = sanitize_title( $userdata->user_login );
	$blog_prefix       = is_multisite() && ! is_subdomain_install() && is_main_site() ? '/blog/' : '/';
	$author_base       = $GLOBALS['wp_rewrite']->author_base;
	$link              = current_user_can( 'manage_options' ) && 'author' === $author_base ? '<a title="' . esc_attr__( 'Do you know you can change this part too?', 'sf-author-url-control' ) . '" href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '#author_base">' : '';

	echo "<table class=\"form-table\">\n";
		echo "<tr>\n";
			echo '<th><label for="user_nicename">' . __( 'Profile URL slug', 'sf-author-url-control' ) . "</label></th>\n";
			echo '<td>';
				echo $blog_prefix . $link . $author_base . ( $link ? '</a>' : '' ) . '/';
				echo '<input id="user_nicename" name="user_nicename" class="regular-text code" type="text" value="' . sanitize_title( $userdata->user_nicename, $def_user_nicename ) . '"/> ';
				echo '<span class="description">(' . sprintf( __( 'Leave empty for default value: %s', 'sf-author-url-control' ), $def_user_nicename ) . ')</span> ';
				echo '<a href="' . esc_url( get_author_posts_url( $user_id ) ) . '">' . __( 'Your Profile' ) . '</a> ';
			echo "</td>\n";
		echo "</tr>\n";
	echo "</table>\n";
}


// Save the user nicename and display error notices.

add_action( 'personal_options_update',  'sf_auc_save_user_options' );	// Own profile
add_action( 'edit_user_profile_update', 'sf_auc_save_user_options' );	// Others

function sf_auc_save_user_options() {
	if ( empty( $_POST['user_id'] ) || ! isset( $_POST['user_nicename'] ) || ! sf_auc_user_can_edit_user_slug() ) {
		return;
	}

	$user_id = (int) $_POST['user_id'];

	if ( ! ( $userdata = get_userdata( $user_id ) ) ) {
		return;
	}

	check_admin_referer( 'update-user_' . $user_id );

	$def_user_nicename = sanitize_title( $userdata->user_login );
	$new_nicename      = sanitize_title( $_POST['user_nicename'], $def_user_nicename );

	if ( $new_nicename === $userdata->user_nicename ) {
		return;
	}

	if ( ! get_user_by( 'slug', $new_nicename ) ) {
		$updated = wp_update_user( array(
			'ID'            => $user_id,
			'user_nicename' => $new_nicename,
		) );

		if ( ! $updated ) {
			add_action( 'user_profile_update_errors', 'sf_auc_user_profile_slug_generic_error', 10, 3 );
		}
	} else {
		add_action( 'user_profile_update_errors', 'sf_auc_user_profile_slug_error', 10, 3 );
	}
}


// Notices

function sf_auc_user_profile_slug_generic_error( $errors, $update, $user ) {
	$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: There was an error updating the author slug. Please try again.', 'sf-author-url-control' ) );
}


function sf_auc_user_profile_slug_error( $errors, $update, $user ) {
	$errors->add( 'user_nicename', __( '<strong>ERROR</strong>: This profile URL slug is already registered. Please choose another one.', 'sf-author-url-control' ) );
}


/*------------------------------------------------------------------------------------------------*/
/* !TOOLS ======================================================================================= */
/*------------------------------------------------------------------------------------------------*/

// Set the new author base, update/delete the option, init rewrite

function sf_auc_set_author_base( $author_base = '' ) {
	global $wp_rewrite;

	if ( get_option( 'author_base' ) === $author_base ) {
		return;
	}

	if ( $author_base && 'author' !== $author_base ) {
		update_option( 'author_base', $author_base );
		$wp_rewrite->author_base = $author_base;
	} else {
		delete_option( 'author_base' );
		$wp_rewrite->author_base = 'author';
	}

	$wp_rewrite->init();
}


// Remove "/" or "/blog/" at the beginning of an uri.

function sf_auc_remove_blog_slug( $uri ) {
	global $wp_rewrite;

	$front = ! empty( $wp_rewrite ) ? trim( $wp_rewrite->front, '/' ) . '/' : 'blog/';
	$uri   = trim( $uri, '/' );
	// Compat old version of the plugin.
	$uri   = strpos( $uri, $front ) === 0 ? substr( $uri, 0, strlen( $front ) ) : $uri;

	return $uri;
}


/*
 * Return true if the current user can edit the user slug.
 *
 * Example to allow editors to edit their own profile slug:
 *
 * add_filter( 'sf_auc_user_can_edit_user_slug', 'allow_editors_to_edit_slug' );
 * function allow_editors_to_edit_slug() {
 *     return current_user_can( 'edit_pages' );
 * }
 */

function sf_auc_user_can_edit_user_slug( $user_id = false ) {
	return current_user_can( 'edit_users' ) || ( ( ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE) || ( $user_id && get_current_user_id() === $user_id ) ) && apply_filters( 'sf_auc_user_can_edit_user_slug', false ) );
}
