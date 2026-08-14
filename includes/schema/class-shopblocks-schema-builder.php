<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ShopBlocks_Schema_Builder {
	public function build_collection_page( array $context, array $items ) {
		$url = isset( $context['url'] ) ? (string) $context['url'] : '';
		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'CollectionPage',
			'@id'      => trailingslashit( $url ) . '#collection',
			'name'     => isset( $context['name'] ) ? (string) $context['name'] : '',
			'url'      => $url,
			'mainEntity' => array(
				'@type' => 'ItemList',
				'itemListElement' => array_values( $items ),
			),
		);
		if ( ! empty( $context['description'] ) ) {
			$schema['description'] = (string) $context['description'];
		}
		return $schema;
	}

	public function build_product_list_item( $product, $position ) {
		$product_id = $product->get_id();
		$url = get_permalink( $product_id );
		$item = array(
			'@type' => 'Product',
			'name'  => $product->get_name(),
			'url'   => $url,
		);
		$image_id = $product->get_image_id();
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
		if ( $image_url ) { $item['image'] = $image_url; }
		if ( $product->get_sku() ) { $item['sku'] = $product->get_sku(); }
		if ( '' !== $product->get_price() ) {
			$item['offers'] = array(
				'@type' => 'Offer',
				'price' => $product->get_price(),
				'priceCurrency' => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD',
				'availability' => 'https://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
				'url' => $url,
			);
		}
		if ( $product->get_average_rating() && $product->get_review_count() ) {
			$item['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => (string) $product->get_average_rating(),
				'reviewCount' => (int) $product->get_review_count(),
			);
		}
		return array( '@type' => 'ListItem', 'position' => (int) $position, 'item' => $item );
	}

	public function build_basic_product_list_item( $product, $position ) {
		return array(
			'@type' => 'ListItem',
			'position' => (int) $position,
			'url' => get_permalink( $product->get_id() ),
			'name' => $product->get_name(),
		);
	}
}
