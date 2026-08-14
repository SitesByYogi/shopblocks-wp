<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * ShopBlocks migration helpers.
 *
 * 1. Early prototype `collections` records -> modern `collection` CPT.
 * 2. Native WordPress Posts -> ShopBlocks Blogs, preserving IDs/slugs/content/meta.
 *
 * The post migration is intentionally in-place. Because Happy Hemp historically
 * used /blogs/%postname%/ for native Posts and ShopBlocks Blogs own /blogs/, the
 * public article URL can remain unchanged after the site's global permalink
 * structure is returned to /%postname%/.
 */

/* =========================================================
 * LEGACY COLLECTION MIGRATION
 * ========================================================= */

/** Counts raw legacy collection records whether or not the old CPT is registered. */
function shopblocks_legacy_collection_count() {
	global $wpdb;
	return (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'collections' AND post_status NOT IN ('auto-draft')" );
}

/**
 * Convert old `collections` records to the modern `collection` CPT in-place.
 * IDs, slugs, content, metadata, featured images, and dates remain unchanged.
 */
function shopblocks_migrate_legacy_collections() {
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'collections' AND post_status NOT IN ('auto-draft') ORDER BY ID ASC" );
	$migrated = 0;
	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( ! $id ) { continue; }
		update_post_meta( $id, '_shopblocks_legacy_post_type', 'collections' );
		update_post_meta( $id, '_shopblocks_legacy_template_compat', '1' );
		$result = wp_update_post( array( 'ID' => $id, 'post_type' => 'collection' ), true );
		if ( ! is_wp_error( $result ) ) { $migrated++; }
	}
	if ( $migrated ) {
		update_option( 'shopblocks_legacy_collections_migrated_at', current_time( 'mysql' ), false );
		flush_rewrite_rules();
	}
	return $migrated;
}


/* =========================================================
 * WORDPRESS POSTS -> SHOPBLOCKS BLOGS
 * ========================================================= */

/** Statuses that are safe to migrate when "all" is selected. */
function shopblocks_post_migration_statuses( $mode = 'published' ) {
	if ( 'all' === $mode ) {
		return array( 'publish', 'future', 'draft', 'pending', 'private' );
	}
	return array( 'publish' );
}

/** Return native Post counts used by the migration screen. */
function shopblocks_post_migration_counts() {
	$counts = wp_count_posts( 'post' );
	$out    = array();
	foreach ( array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ) as $status ) {
		$out[ $status ] = isset( $counts->$status ) ? (int) $counts->$status : 0;
	}
	$out['eligible_all'] = $out['publish'] + $out['future'] + $out['draft'] + $out['pending'] + $out['private'];
	return $out;
}

/** Count posts still waiting for a migration mode. */
function shopblocks_post_migration_remaining( $mode = 'published' ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => shopblocks_post_migration_statuses( $mode ),
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => false,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => '_shopblocks_migration_collision',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	return (int) $query->found_posts;
}

/** Number of ShopBlocks Blogs created by this migration layer. */
function shopblocks_post_migration_rollback_count() {
	$query = new WP_Query(
		array(
			'post_type'              => 'shopblocks_blog',
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'meta_key'               => '_shopblocks_original_post_type',
			'meta_value'             => 'post',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	return (int) $query->found_posts;
}

/**
 * Find URL collisions where a native Post and an existing ShopBlocks Blog
 * already share the same slug. Those records are deliberately skipped.
 */
function shopblocks_post_migration_collision_count( $mode = 'all' ) {
	global $wpdb;
	$statuses = shopblocks_post_migration_statuses( $mode );
	$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
	$sql = "
		SELECT COUNT(DISTINCT p.ID)
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->posts} b
			ON b.post_name = p.post_name
			AND b.post_type = 'shopblocks_blog'
			AND b.post_status NOT IN ('trash','auto-draft')
		WHERE p.post_type = 'post'
			AND p.post_status IN ($placeholders)
			AND p.post_name <> ''
	";
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return (int) $wpdb->get_var( $wpdb->prepare( $sql, $statuses ) );
}

/** Determine whether this individual native Post collides with an existing Blog slug. */
function shopblocks_post_migration_has_collision( $post ) {
	if ( ! $post || ! $post->post_name ) { return false; }
	$existing = get_page_by_path( $post->post_name, OBJECT, 'shopblocks_blog' );
	return $existing && (int) $existing->ID !== (int) $post->ID;
}

/**
 * Convert one batch of native WordPress Posts into ShopBlocks Blogs in-place.
 * The record ID never changes, which preserves content, featured images,
 * comments, revisions, taxonomies, SEO metadata, Elementor metadata and links
 * that reference the post ID.
 */
function shopblocks_migrate_posts_batch( $mode = 'published', $limit = 25 ) {
	$mode  = 'all' === $mode ? 'all' : 'published';
	$limit = max( 1, min( 100, absint( $limit ) ) );

	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => shopblocks_post_migration_statuses( $mode ),
			'fields'                 => 'ids',
			'posts_per_page'         => $limit,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => '_shopblocks_migration_collision',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	$result = array(
		'migrated'   => 0,
		'skipped'    => 0,
		'errors'     => array(),
		'collisions' => array(),
	);

	wp_defer_term_counting( true );

	foreach ( $query->posts as $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'post' !== $post->post_type ) { continue; }

		if ( shopblocks_post_migration_has_collision( $post ) ) {
			$result['skipped']++;
			$result['collisions'][] = array(
				'id'    => (int) $post->ID,
				'title' => get_the_title( $post ),
				'slug'  => $post->post_name,
			);
			// Mark collision so repeated batches can skip it without stalling forever.
			update_post_meta( $post->ID, '_shopblocks_migration_collision', '1' );
			// Move this collision out of the next batch by excluding it below is not
			// possible with the first-page strategy, so convert no further here.
			continue;
		}

		update_post_meta( $post->ID, '_shopblocks_original_post_type', 'post' );
		update_post_meta( $post->ID, '_shopblocks_migration_version', SHOPBLOCKS_PLUGIN_VERSION );
		update_post_meta( $post->ID, '_shopblocks_migrated_at', current_time( 'mysql' ) );
		update_post_meta( $post->ID, '_shopblocks_original_post_slug', $post->post_name );
		delete_post_meta( $post->ID, '_shopblocks_migration_collision' );

		// Existing WordPress posts should use the standard Blog shell, not the
		// lead-generation Article layout, unless an editor explicitly changes it.
		if ( '' === get_post_meta( $post->ID, '_shopblocks_content_layout', true ) ) {
			update_post_meta( $post->ID, '_shopblocks_content_layout', 'blog' );
		}

		$updated = wp_update_post(
			array(
				'ID'        => $post->ID,
				'post_type' => 'shopblocks_blog',
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			$result['errors'][] = array(
				'id'      => (int) $post->ID,
				'message' => $updated->get_error_message(),
			);
			continue;
		}

		clean_post_cache( $post->ID );
		$result['migrated']++;
	}

	wp_defer_term_counting( false );

	if ( $result['migrated'] ) {
		update_option( 'shopblocks_posts_migrated_at', current_time( 'mysql' ), false );
	}

	$result['remaining'] = shopblocks_post_migration_remaining( $mode );
	$result['rollback']  = shopblocks_post_migration_rollback_count();
	return $result;
}

/**
 * Roll back a batch previously migrated by ShopBlocks. This intentionally only
 * touches records carrying our migration marker.
 */
function shopblocks_rollback_posts_batch( $limit = 25 ) {
	$limit = max( 1, min( 100, absint( $limit ) ) );
	$query = new WP_Query(
		array(
			'post_type'              => 'shopblocks_blog',
			'post_status'            => array( 'publish', 'future', 'draft', 'pending', 'private' ),
			'meta_key'               => '_shopblocks_original_post_type',
			'meta_value'             => 'post',
			'fields'                 => 'ids',
			'posts_per_page'         => $limit,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$rolled_back = 0;
	$errors      = array();
	wp_defer_term_counting( true );

	foreach ( $query->posts as $post_id ) {
		$updated = wp_update_post( array( 'ID' => $post_id, 'post_type' => 'post' ), true );
		if ( is_wp_error( $updated ) ) {
			$errors[] = array( 'id' => (int) $post_id, 'message' => $updated->get_error_message() );
			continue;
		}
		delete_post_meta( $post_id, '_shopblocks_original_post_type' );
		delete_post_meta( $post_id, '_shopblocks_migration_version' );
		delete_post_meta( $post_id, '_shopblocks_migrated_at' );
		delete_post_meta( $post_id, '_shopblocks_original_post_slug' );
		delete_post_meta( $post_id, '_shopblocks_migration_collision' );
		clean_post_cache( $post_id );
		$rolled_back++;
	}

	wp_defer_term_counting( false );
	return array(
		'rolled_back' => $rolled_back,
		'remaining'   => shopblocks_post_migration_rollback_count(),
		'errors'      => $errors,
	);
}

/** AJAX batch migration endpoint. */
function shopblocks_ajax_migrate_posts_batch() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to run this migration.', 'shopblocks-wp' ) ), 403 );
	}
	check_ajax_referer( 'shopblocks_post_migration', 'nonce' );
	$mode  = isset( $_POST['mode'] ) && 'all' === sanitize_key( wp_unslash( $_POST['mode'] ) ) ? 'all' : 'published';
	$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 25;
	wp_send_json_success( shopblocks_migrate_posts_batch( $mode, $limit ) );
}
add_action( 'wp_ajax_shopblocks_migrate_posts_batch', 'shopblocks_ajax_migrate_posts_batch' );

/** AJAX rollback endpoint. */
function shopblocks_ajax_rollback_posts_batch() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to run this rollback.', 'shopblocks-wp' ) ), 403 );
	}
	check_ajax_referer( 'shopblocks_post_migration', 'nonce' );
	$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 25;
	wp_send_json_success( shopblocks_rollback_posts_batch( $limit ) );
}
add_action( 'wp_ajax_shopblocks_rollback_posts_batch', 'shopblocks_ajax_rollback_posts_batch' );


/* =========================================================
 * ARCHIVES / DISCOVERY AFTER POST MIGRATION
 * ========================================================= */

/**
 * Keep migrated Blogs visible in author archives and the main site feed.
 * Category/tag archives already know about ShopBlocks Blogs because the CPT is
 * registered against those native taxonomies.
 */
function shopblocks_include_blogs_in_core_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) { return; }

	if ( $query->is_author() || $query->is_feed() ) {
		$current = $query->get( 'post_type' );
		if ( empty( $current ) || 'post' === $current ) {
			$query->set( 'post_type', array( 'post', 'shopblocks_blog' ) );
		} elseif ( is_array( $current ) && ! in_array( 'shopblocks_blog', $current, true ) ) {
			$current[] = 'shopblocks_blog';
			$query->set( 'post_type', array_values( array_unique( $current ) ) );
		}
	}
}
add_action( 'pre_get_posts', 'shopblocks_include_blogs_in_core_queries', 20 );


/* =========================================================
 * MIGRATION ADMIN SCREEN
 * ========================================================= */

function shopblocks_legacy_migration_menu() {
	add_submenu_page(
		'shopblocks-settings',
		__( 'Migration', 'shopblocks-wp' ),
		__( 'Migration', 'shopblocks-wp' ),
		'manage_options',
		'shopblocks-legacy-migration',
		'shopblocks_legacy_migration_page'
	);
}
add_action( 'admin_menu', 'shopblocks_legacy_migration_menu', 30 );

function shopblocks_legacy_migration_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	$collection_migrated = null;
	if ( isset( $_POST['shopblocks_run_legacy_migration'] ) ) {
		check_admin_referer( 'shopblocks_run_legacy_migration' );
		$collection_migrated = shopblocks_migrate_legacy_collections();
	}

	$collection_count = shopblocks_legacy_collection_count();
	$post_counts      = shopblocks_post_migration_counts();
	$rollback_count   = shopblocks_post_migration_rollback_count();
	$collisions       = shopblocks_post_migration_collision_count( 'all' );
	$nonce            = wp_create_nonce( 'shopblocks_post_migration' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ShopBlocks Migration', 'shopblocks-wp' ); ?></h1>
		<p><?php esc_html_e( 'Migration changes post types in-place. IDs, slugs, editor content, featured images, authors, dates, comments, revisions, taxonomies, SEO metadata and other custom fields remain attached to the same database record.', 'shopblocks-wp' ); ?></p>

		<?php if ( null !== $collection_migrated ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( sprintf( __( 'Migrated %d legacy Collections.', 'shopblocks-wp' ), $collection_migrated ) ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:1000px;padding:22px;margin-top:22px">
			<h2 style="margin-top:0"><?php esc_html_e( 'WordPress Posts → ShopBlocks Blogs', 'shopblocks-wp' ); ?></h2>
			<p><?php esc_html_e( 'Use this for sites whose existing editorial library should become ShopBlocks Blogs. Published posts can be migrated first for a controlled production rollout, or all editorial statuses can be migrated after testing.', 'shopblocks-wp' ); ?></p>

			<table class="widefat striped" style="max-width:850px;margin:18px 0">
				<tbody>
				<tr><th><?php esc_html_e( 'Published Posts', 'shopblocks-wp' ); ?></th><td id="sb-count-publish"><?php echo esc_html( $post_counts['publish'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Drafts', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $post_counts['draft'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Scheduled', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $post_counts['future'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Pending', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $post_counts['pending'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Private', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $post_counts['private'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Total eligible Posts', 'shopblocks-wp' ); ?></th><td id="sb-count-all"><strong><?php echo esc_html( $post_counts['eligible_all'] ); ?></strong></td></tr>
				<tr><th><?php esc_html_e( 'Already migrated by ShopBlocks', 'shopblocks-wp' ); ?></th><td id="sb-count-rollback"><?php echo esc_html( $rollback_count ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Slug collisions requiring manual review', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $collisions ); ?></td></tr>
				</tbody>
			</table>

			<?php if ( $collisions > 0 ) : ?>
				<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Collision warning:', 'shopblocks-wp' ); ?></strong> <?php esc_html_e( 'One or more native Posts share a slug with an existing ShopBlocks Blog. Those records will be skipped rather than overwrite an existing URL.', 'shopblocks-wp' ); ?></p></div>
			<?php endif; ?>

			<p><strong><?php esc_html_e( 'Before migrating:', 'shopblocks-wp' ); ?></strong> <?php esc_html_e( 'take a database backup or staging snapshot and verify that Settings → Permalinks uses /%postname%/. ShopBlocks Blogs own the /blogs/ rewrite base.', 'shopblocks-wp' ); ?></p>

			<div style="display:flex;gap:10px;flex-wrap:wrap;margin:20px 0">
				<button type="button" class="button button-primary sb-post-migrate" data-mode="published" data-total="<?php echo esc_attr( $post_counts['publish'] ); ?>"><?php esc_html_e( 'Migrate Published Posts', 'shopblocks-wp' ); ?></button>
				<button type="button" class="button sb-post-migrate" data-mode="all" data-total="<?php echo esc_attr( $post_counts['eligible_all'] ); ?>"><?php esc_html_e( 'Migrate All Editorial Posts', 'shopblocks-wp' ); ?></button>
				<?php if ( $rollback_count > 0 ) : ?><button type="button" class="button sb-post-rollback" data-total="<?php echo esc_attr( $rollback_count ); ?>"><?php esc_html_e( 'Rollback ShopBlocks-Migrated Posts', 'shopblocks-wp' ); ?></button><?php endif; ?>
			</div>

			<div id="sb-post-migration-progress" style="display:none;max-width:850px">
				<div style="height:18px;background:#e5e5e5;border-radius:9px;overflow:hidden"><div id="sb-post-migration-bar" style="width:0;height:100%;background:#2271b1;transition:width .2s"></div></div>
				<p id="sb-post-migration-status" style="margin-top:10px"></p>
			</div>
		</div>

		<div class="card" style="max-width:1000px;padding:22px;margin-top:22px">
			<h2 style="margin-top:0"><?php esc_html_e( 'Legacy Collection Migration', 'shopblocks-wp' ); ?></h2>
			<p><?php esc_html_e( 'Only for early prototype sites that stored Collections under the old plural `collections` post type. Migrated Collections keep their original theme/Elementor template until Legacy Template Compatibility is disabled on that Collection.', 'shopblocks-wp' ); ?></p>
			<table class="widefat striped" style="max-width:850px"><tbody><tr><th><?php esc_html_e( 'Legacy Collections waiting for migration', 'shopblocks-wp' ); ?></th><td><?php echo esc_html( $collection_count ); ?></td></tr></tbody></table>
			<?php if ( $collection_count > 0 ) : ?>
			<form method="post" style="margin-top:18px">
				<?php wp_nonce_field( 'shopblocks_run_legacy_migration' ); ?>
				<input type="hidden" name="shopblocks_run_legacy_migration" value="1">
				<?php submit_button( __( 'Migrate Legacy Collections', 'shopblocks-wp' ), 'secondary', 'submit', false ); ?>
			</form>
			<?php else : ?><p><strong><?php esc_html_e( 'No legacy Collections are waiting for migration.', 'shopblocks-wp' ); ?></strong></p><?php endif; ?>
		</div>
	</div>

	<script>
	(function () {
		'use strict';
		const ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		const nonce = <?php echo wp_json_encode( $nonce ); ?>;
		const wrap = document.getElementById('sb-post-migration-progress');
		const bar = document.getElementById('sb-post-migration-bar');
		const status = document.getElementById('sb-post-migration-status');
		const buttons = Array.from(document.querySelectorAll('.sb-post-migrate, .sb-post-rollback'));

		function setDisabled(disabled) {
			buttons.forEach(btn => { btn.disabled = disabled; });
		}
		function setProgress(done, total, message) {
			wrap.style.display = 'block';
			const pct = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 100;
			bar.style.width = pct + '%';
			status.textContent = message;
		}
		async function request(action, extra) {
			const body = new URLSearchParams(Object.assign({ action, nonce, limit: 25 }, extra || {}));
			const response = await fetch(ajaxUrl, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
				credentials: 'same-origin',
				body: body.toString()
			});
			return response.json();
		}

		document.querySelectorAll('.sb-post-migrate').forEach(button => {
			button.addEventListener('click', async function () {
				const mode = this.dataset.mode;
				const total = parseInt(this.dataset.total || '0', 10);
				if (!total) { alert('No eligible WordPress Posts are waiting for this migration.'); return; }
				if (!confirm('This will change native WordPress Posts into ShopBlocks Blogs in-place. Make sure you have a current backup. Continue?')) return;
				setDisabled(true);
				let done = 0, skipped = 0;
				setProgress(0, total, 'Starting migration…');
				try {
					while (true) {
						const json = await request('shopblocks_migrate_posts_batch', {mode});
						if (!json.success) throw new Error((json.data && json.data.message) || 'Migration request failed.');
						const data = json.data;
						done += parseInt(data.migrated || 0, 10);
						skipped += parseInt(data.skipped || 0, 10);
						setProgress(done + skipped, total, 'Migrated ' + done + ' of ' + total + (skipped ? ' • skipped ' + skipped + ' collision(s)' : '') + ' • remaining ' + data.remaining);
						if (data.remaining <= 0 || (data.migrated <= 0 && data.skipped <= 0)) break;
						// If every record in the first page is a collision, stop to avoid looping forever.
						if (data.migrated <= 0 && data.skipped > 0) break;
					}
					setProgress(total, total, 'Migration pass complete. Migrated ' + done + (skipped ? '; skipped ' + skipped + ' slug collision(s).' : '.'));
					setTimeout(() => window.location.reload(), 1200);
				} catch (error) {
					status.textContent = 'Migration stopped: ' + error.message;
					setDisabled(false);
				}
			});
		});

		const rollback = document.querySelector('.sb-post-rollback');
		if (rollback) rollback.addEventListener('click', async function () {
			const total = parseInt(this.dataset.total || '0', 10);
			if (!confirm('Rollback only posts migrated by ShopBlocks. Their URLs will follow the CURRENT native Post permalink structure. Continue?')) return;
			setDisabled(true);
			let done = 0;
			setProgress(0, total, 'Starting rollback…');
			try {
				while (true) {
					const json = await request('shopblocks_rollback_posts_batch');
					if (!json.success) throw new Error((json.data && json.data.message) || 'Rollback request failed.');
					const data = json.data;
					done += parseInt(data.rolled_back || 0, 10);
					setProgress(done, total, 'Rolled back ' + done + ' of ' + total + ' • remaining ' + data.remaining);
					if (data.remaining <= 0 || data.rolled_back <= 0) break;
				}
				setProgress(total, total, 'Rollback complete.');
				setTimeout(() => window.location.reload(), 1200);
			} catch (error) {
				status.textContent = 'Rollback stopped: ' + error.message;
				setDisabled(false);
			}
		});
	})();
	</script>
	<?php
}


/* =========================================================
 * LEGACY COLLECTION TEMPLATE COMPATIBILITY
 * ========================================================= */

/** Keep migrated legacy Collections on the site's original theme/Elementor single template by default. */
function shopblocks_is_legacy_collection_compat( $post_id ) {
	return 'collections' === get_post_meta( $post_id, '_shopblocks_legacy_post_type', true )
		&& '0' !== get_post_meta( $post_id, '_shopblocks_legacy_template_compat', true );
}

function shopblocks_legacy_collection_template_meta_box() {
	global $post;
	if ( ! $post || 'collection' !== $post->post_type || 'collections' !== get_post_meta( $post->ID, '_shopblocks_legacy_post_type', true ) ) { return; }
	add_meta_box( 'shopblocks_legacy_template_compat', __( 'Legacy Template Compatibility', 'shopblocks-wp' ), 'shopblocks_legacy_template_compat_box', 'collection', 'side', 'high' );
}
add_action( 'add_meta_boxes_collection', 'shopblocks_legacy_collection_template_meta_box' );

function shopblocks_legacy_template_compat_box( $post ) {
	wp_nonce_field( 'shopblocks_save_legacy_template_compat', 'shopblocks_legacy_template_compat_nonce' );
	$enabled = shopblocks_is_legacy_collection_compat( $post->ID );
	?>
	<label><input type="checkbox" name="shopblocks_legacy_template_compat" value="1" <?php checked( $enabled ); ?>> <?php esc_html_e( 'Use the original theme/Elementor single template for this migrated Collection.', 'shopblocks-wp' ); ?></label>
	<p class="description"><?php esc_html_e( 'Leave enabled until this page has been rebuilt for the modern ShopBlocks Collection template.', 'shopblocks-wp' ); ?></p>
	<?php
}

function shopblocks_save_legacy_template_compat( $post_id ) {
	if ( ! isset( $_POST['shopblocks_legacy_template_compat_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['shopblocks_legacy_template_compat_nonce'] ) ), 'shopblocks_save_legacy_template_compat' ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	update_post_meta( $post_id, '_shopblocks_legacy_template_compat', isset( $_POST['shopblocks_legacy_template_compat'] ) ? '1' : '0' );
}
add_action( 'save_post_collection', 'shopblocks_save_legacy_template_compat', 50 );
