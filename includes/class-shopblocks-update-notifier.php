<?php
/**
 * ShopBlocks GitHub release update notifier.
 *
 * Notification only: this class intentionally does not provide a package URL,
 * so WordPress can surface an available update without attempting to install it.
 *
 * @package ShopBlocksWP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ShopBlocks_Update_Notifier {

	const RELEASE_API     = 'https://api.github.com/repos/SitesByYogi/shopblocks-wp/releases/latest';
	const CACHE_KEY       = 'shopblocks_latest_github_release';
	const CACHE_TTL       = 21600; // 6 hours.
	const MANUAL_ACTION   = 'shopblocks_check_for_updates';

	/**
	 * Bootstrap hooks.
	 */
	public static function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'inject_update' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( SHOPBLOCKS_PLUGIN_FILE ),
			array( __CLASS__, 'add_action_link' )
		);

		add_action(
			'admin_post_' . self::MANUAL_ACTION,
			array( __CLASS__, 'manual_check' )
		);

		add_action( 'admin_notices', array( __CLASS__, 'manual_check_notice' ) );
	}

	/**
	 * Get the latest published GitHub Release.
	 *
	 * @param bool $force Whether to bypass the six-hour cache.
	 * @return array|WP_Error
	 */
	private static function get_latest_release( $force = false ) {
		if ( ! $force ) {
			$cached = get_site_transient( self::CACHE_KEY );
			if ( is_array( $cached ) && ! empty( $cached['tag_name'] ) ) {
				return $cached;
			}
		}

		$response = wp_remote_get(
			self::RELEASE_API,
			array(
				'timeout'     => 8,
				'redirection' => 3,
				'headers'     => array(
					'Accept'               => 'application/vnd.github+json',
					'User-Agent'           => 'ShopBlocks-WP/' . SHOPBLOCKS_PLUGIN_VERSION,
					'X-GitHub-Api-Version' => '2022-11-28',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status ) {
			return new WP_Error(
				'shopblocks_github_status',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'GitHub returned HTTP %d while checking ShopBlocks updates.', 'shopblocks-wp' ),
					$status
				)
			);
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );

		if (
			! is_array( $release ) ||
			empty( $release['tag_name'] ) ||
			! empty( $release['draft'] ) ||
			! empty( $release['prerelease'] )
		) {
			return new WP_Error(
				'shopblocks_invalid_release',
				__( 'ShopBlocks could not read a valid published GitHub release.', 'shopblocks-wp' )
			);
		}

		$normalized = array(
			'tag_name'     => sanitize_text_field( (string) $release['tag_name'] ),
			'html_url'     => ! empty( $release['html_url'] ) ? esc_url_raw( $release['html_url'] ) : '',
			'published_at' => ! empty( $release['published_at'] ) ? sanitize_text_field( (string) $release['published_at'] ) : '',
		);

		set_site_transient( self::CACHE_KEY, $normalized, self::CACHE_TTL );

		return $normalized;
	}

	/**
	 * Add ShopBlocks to WordPress' update transient when a newer release exists.
	 *
	 * No package URL is supplied. This is intentionally notification-only.
	 *
	 * @param object $transient WordPress plugin update transient.
	 * @return object
	 */
	public static function inject_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $transient;
		}

		$plugin_file = plugin_basename( SHOPBLOCKS_PLUGIN_FILE );

		// Only participate once WordPress knows the installed ShopBlocks version.
		if ( empty( $transient->checked[ $plugin_file ] ) ) {
			return $transient;
		}

		$release = self::get_latest_release();

		if ( is_wp_error( $release ) || empty( $release['tag_name'] ) ) {
			return $transient;
		}

		$latest_version = ltrim( (string) $release['tag_name'], "vV \t\n\r\0\x0B" );

		if (
			'' === $latest_version ||
			! preg_match( '/^\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?$/', $latest_version )
		) {
			return $transient;
		}

		if ( version_compare( $latest_version, SHOPBLOCKS_PLUGIN_VERSION, '>' ) ) {
			if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}

			$transient->response[ $plugin_file ] = (object) array(
				'id'          => 'https://github.com/SitesByYogi/shopblocks-wp',
				'slug'        => 'shopblocks-wp',
				'plugin'      => $plugin_file,
				'new_version' => $latest_version,
				'url'         => ! empty( $release['html_url'] )
					? $release['html_url']
					: 'https://github.com/SitesByYogi/shopblocks-wp/releases',
				'package'     => '',
				'requires'    => '6.3',
				'requires_php'=> '7.4',
			);
		} elseif ( isset( $transient->response[ $plugin_file ] ) ) {
			unset( $transient->response[ $plugin_file ] );
		}

		return $transient;
	}

	/**
	 * Add a manual "Check for updates" link to the Plugins screen.
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public static function add_action_link( $links ) {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return $links;
		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=' . self::MANUAL_ACTION ),
			self::MANUAL_ACTION
		);

		$links[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Check for updates', 'shopblocks-wp' )
		);

		return $links;
	}

	/**
	 * Force a fresh GitHub check and rebuild WordPress' plugin update transient.
	 */
	public static function manual_check() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to check plugin updates.', 'shopblocks-wp' ) );
		}

		check_admin_referer( self::MANUAL_ACTION );

		delete_site_transient( self::CACHE_KEY );
		delete_site_transient( 'update_plugins' );

		$release = self::get_latest_release( true );

		// Let WordPress rebuild update_plugins while the fresh release is cached.
		if ( function_exists( 'wp_update_plugins' ) ) {
			wp_update_plugins();
		}

		$status = is_wp_error( $release ) ? 'error' : 'success';

		wp_safe_redirect(
			add_query_arg(
				array(
					'shopblocks-update-check' => $status,
				),
				admin_url( 'plugins.php' )
			)
		);
		exit;
	}

	/**
	 * Admin feedback after a manual check.
	 */
	public static function manual_check_notice() {
		if (
			empty( $_GET['shopblocks-update-check'] ) ||
			! current_user_can( 'update_plugins' )
		) {
			return;
		}

		$status = sanitize_key( wp_unslash( $_GET['shopblocks-update-check'] ) );

		if ( 'success' === $status ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'ShopBlocks checked GitHub for the latest published release. WordPress update notices have been refreshed.', 'shopblocks-wp' ); ?></p>
			</div>
			<?php
		} elseif ( 'error' === $status ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'ShopBlocks could not reach GitHub. The existing update status was left unchanged.', 'shopblocks-wp' ); ?></p>
			</div>
			<?php
		}
	}
}

ShopBlocks_Update_Notifier::init();
