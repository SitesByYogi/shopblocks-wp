<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/class-shopblocks-schema-builder.php';

class ShopBlocks_Schema {
	protected $builder;
	public function __construct() { $this->builder = new ShopBlocks_Schema_Builder(); }

	public function render() {
		if ( ! get_option( 'shopblocks_enable_schema', 1 ) ) { return; }
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
		if ( ! shopblocks_has_woocommerce() ) { return; }
		/* Avoid duplicate JSON-LD while the legacy Schema Rich Snippets plugin remains active during migration. */
		if ( defined( 'SRS_PLUGIN_VERSION' ) || class_exists( 'SRS_Plugin' ) ) { return; }
		$schema = $this->resolve_schema();
		if ( empty( $schema ) ) { return; }
		echo "\n<script type=\"application/ld+json\">";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		echo "</script>\n";
	}

	protected function resolve_schema() {
		if ( is_singular( 'collection' ) ) {
			$post_id = get_queried_object_id();
			$ids = shopblocks_schema_collection_product_ids( $post_id );
			return $this->build_for_ids( shopblocks_schema_post_context( $post_id ), $ids, true );
		}
		if ( is_singular( array( 'collections', 'shopblocks_blog' ) ) ) {
			$post_id = get_queried_object_id();
			$ids = shopblocks_schema_extract_product_ids_from_content( get_post_field( 'post_content', $post_id ) );
			return $this->build_for_ids( shopblocks_schema_post_context( $post_id ), $ids, true );
		}
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$term = get_queried_object();
			if ( ! $term || empty( $term->term_id ) ) { return null; }
			$limit = (int) apply_filters( 'shopblocks/schema/woo_category_product_limit', 24, $term );
			$products = wc_get_products( array(
				'status' => 'publish', 'limit' => max( 1, $limit ),
				'category' => array( sanitize_title( $term->slug ) ),
			) );
			$items = array(); $position = 1;
			foreach ( $products as $product ) {
				if ( $product instanceof WC_Product ) { $items[] = $this->builder->build_basic_product_list_item( $product, $position++ ); }
			}
			if ( ! $items ) { return null; }
			$context = array(
				'name' => single_term_title( '', false ),
				'url' => get_term_link( $term ),
				'description' => wp_strip_all_tags( term_description( $term ) ),
			);
			return $this->builder->build_collection_page( $context, $items );
		}
		return null;
	}

	protected function build_for_ids( array $context, array $ids, $rich ) {
		if ( empty( $context ) || empty( $ids ) ) { return null; }
		$items = array(); $position = 1;
		foreach ( $ids as $id ) {
			$product = wc_get_product( absint( $id ) );
			if ( ! $product instanceof WC_Product ) { continue; }
			$items[] = $rich ? $this->builder->build_product_list_item( $product, $position ) : $this->builder->build_basic_product_list_item( $product, $position );
			$position++;
		}
		return $items ? $this->builder->build_collection_page( $context, $items ) : null;
	}
}

function shopblocks_schema_post_context( $post_id ) {
	if ( ! $post_id ) { return array(); }
	return array(
		'name' => get_the_title( $post_id ),
		'url' => get_permalink( $post_id ),
		'description' => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
	);
}

function shopblocks_schema_collection_product_ids( $post_id ) {
	$raw = get_post_meta( $post_id, '_shopblocks_product_ids', true );
	$ids = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $raw ) ) ) ) );
	if ( $ids ) { return $ids; }
	return shopblocks_schema_extract_product_ids_from_content( get_post_field( 'post_content', $post_id ) );
}

function shopblocks_schema_extract_product_ids_from_content( $content ) {
	$content = (string) $content;
	if ( '' === $content || ! shopblocks_has_woocommerce() ) { return array(); }
	$ids = array();
	if ( preg_match_all( '/\[shoppable_product_top[^\]]*\bid\s*=\s*["\'](\d+)["\'][^\]]*\]/i', $content, $matches ) ) {
		$ids = array_merge( $ids, array_map( 'absint', $matches[1] ) );
	}
	if ( preg_match_all( '/\[shoppable_product_top[^\]]*\bslug\s*=\s*["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches ) ) {
		foreach ( $matches[1] as $slug ) {
			$product_post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'product' );
			if ( $product_post ) { $ids[] = absint( $product_post->ID ); }
		}
	}
	if ( preg_match_all( '/\[(?:add_products|shopblocks_products)[^\]]*\bids\s*=\s*["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches ) ) {
		foreach ( $matches[1] as $csv ) { $ids = array_merge( $ids, array_map( 'absint', preg_split( '/\s*,\s*/', $csv ) ) ); }
	}
	if ( preg_match_all( '/\[(?:add_products|shopblocks_products)[^\]]*\bslugs\s*=\s*["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches ) ) {
		foreach ( $matches[1] as $csv ) {
			foreach ( preg_split( '/\s*,\s*/', $csv ) as $slug ) {
				$product_post = get_page_by_path( sanitize_title( $slug ), OBJECT, 'product' );
				if ( $product_post ) { $ids[] = absint( $product_post->ID ); }
			}
		}
	}
	if ( preg_match_all( '/\[(?:add_products|shopblocks_products)[^\]]*\bcategory\s*=\s*["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches ) ) {
		foreach ( $matches[1] as $category_slug ) {
			$products = wc_get_products( array( 'status' => 'publish', 'limit' => 24, 'category' => array( sanitize_title( $category_slug ) ) ) );
			foreach ( $products as $product ) { if ( $product instanceof WC_Product ) { $ids[] = $product->get_id(); } }
		}
	}
	return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

function shopblocks_bootstrap_schema() {
	$schema = new ShopBlocks_Schema();
	add_action( 'wp_head', array( $schema, 'render' ), 20 );
}
add_action( 'plugins_loaded', 'shopblocks_bootstrap_schema', 30 );
