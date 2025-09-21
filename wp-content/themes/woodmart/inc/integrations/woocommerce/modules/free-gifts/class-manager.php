<?php
/**
 * Manager class file.
 *
 * @package Woodmart
 */

namespace XTS\Modules\Free_Gifts;

use WC_Product;
use WP_Post;
use XTS\Singleton;

/**
 * Manager class.
 */
class Manager extends Singleton {
	/**
	 * Default ist of meta box arguments for rendering template.
	 *
	 * @var array $meta_boxes_fields List of meta box arguments for rendering template.
	 */
	private $meta_boxes_fields_keys = array();

	/**
	 * Transient name for 'All free gifts post ids'.
	 *
	 * @var string $wd_transient_free_gifts_ids .
	 */
	public $wd_transient_free_gifts_ids = 'wd_transient_free_gifts_ids';

	/**
	 * Transient name for 'Single free gifts rule'. Has a dynamic part at the end of the name '_${free_gifts_post_id}'.
	 *
	 * @var string $wd_transient_free_gifts_rule .
	 */
	public $wd_transient_free_gifts_rule = 'wd_transient_free_gifts_rule';

	/**
	 * Transient name for 'All gifts rules'.
	 *
	 * @var string $wd_transient_free_gifts_all_rules .
	 */
	public $wd_transient_free_gifts_all_rules = 'wd_transient_free_gifts_all_rules';

	/**
	 * List of notices.
	 *
	 * @var array List of notices.
	 */
	public $notices = array();

	/**
	 * Constructor.
	 */
	public function init() {
		$this->set_notices();
	}

	/**
	 * Set default list of meta box arguments for rendering template.
	 *
	 * @param array $meta_boxes_fields_keys List of default meta boxes fields.
	 *
	 * @return void
	 */
	public function set_meta_boxes_fields_keys( $meta_boxes_fields_keys ) {
		$this->meta_boxes_fields_keys = $meta_boxes_fields_keys;
	}

	/**
	 * Get default list of meta box arguments for rendering template.
	 *
	 * @return array List of meta box arguments for rendering template.
	 */
	public function get_meta_boxes_fields_keys() {
		return $this->meta_boxes_fields_keys;
	}

	/**
	 * Get list of meta box arguments for single discounts post from database.
	 *
	 * @param int|string $id Discounts post id.
	 *
	 * @return array List of meta box arguments.
	 */
	public function get_single_post_rules( $id = '' ) {
		if ( ! $id ) {
			$id = get_the_ID();
		}

		$cache = get_transient( $this->wd_transient_free_gifts_rule . '_' . $id );

		if ( $cache ) {
			return $cache;
		}

		$meta_boxes_keys    = $this->get_meta_boxes_fields_keys();
		$current_meta_boxes = array();

		foreach ( $meta_boxes_keys as $meta_box_id ) {
			$meta_box_value = maybe_unserialize( get_post_meta( $id, $meta_box_id, true ) );

			if ( 'free_gifts' === $meta_box_id && ! empty( $meta_box_value ) ) {
				$meta_box_value = array_values(
					array_filter(
						$meta_box_value,
						function ( $gift_id ) {
							$gift_product = wc_get_product( $gift_id );

							return $gift_product instanceof WC_Product && $gift_product->is_in_stock();
						}
					)
				);
			}

			$current_meta_boxes[ $meta_box_id ] = $meta_box_value;
		}

		set_transient( $this->wd_transient_free_gifts_rule . '_' . $id, $current_meta_boxes );

		return $current_meta_boxes;
	}

	/**
	 * Get list of free gifts post ids.
	 *
	 * @return int[]
	 */
	public function get_all_rule_posts_ids() {
		$cache = get_transient( $this->wd_transient_free_gifts_ids );

		if ( $cache ) {
			return $cache;
		}

		$all_free_gifts_post_ids = get_posts(
			array(
				'fields'         => 'ids',
				'posts_per_page' => apply_filters( 'woodmart_free_gifts_rule_limit', 100 ),
				'post_type'      => 'wd_woo_free_gifts',
				'post_status'    => 'publish',
			)
		);

		set_transient( $this->wd_transient_free_gifts_ids, $all_free_gifts_post_ids );

		return $all_free_gifts_post_ids;
	}

	/**
	 * Take a filtered list according to the type of gift rules.
	 *
	 * @param array  $all_rules List off all rules.
	 * @param string $type The type of product added - 'all', 'manual' or 'automatic'.
	 *
	 * @return array
	 */
	public function get_rules_by_type( $all_rules, $type = 'all' ) {
		if ( 'all' === $type ) {
			return $all_rules;
		} else {
			return array_filter(
				$all_rules,
				function ( $rule ) use ( $type ) {
					return $type === $rule['free_gifts_rule_type'];
				}
			);
		}
	}

	/**
	 * Get list of meta box arguments for all discounts posts from database.
	 *
	 * @param string $type The type of product added - 'all', 'manual' or 'automatic'
	 *
	 * @return array List of meta box arguments.
	 */
	public function get_rules( $type = 'all' ) {
		$all_rules = get_transient( $this->wd_transient_free_gifts_all_rules );

		if ( ! empty( $all_rules ) ) {
			return $this->get_rules_by_type( $all_rules, $type );
		}

		$all_rules = array();
		$ids       = $this->get_all_rule_posts_ids();

		if ( empty( $ids ) ) {
			return array();
		}

		foreach ( $ids as $id ) {
			$all_rules[ $id ] = $this->get_single_post_rules( $id );
		}

		set_transient( $this->wd_transient_free_gifts_all_rules, $all_rules );

		return $this->get_rules_by_type( $all_rules, $type );
	}

	/**
	 * Check condition before apply discount.
	 *
	 * @param array      $gift_rule List of meta box arguments.
	 * @param WC_Product $product The product object for which you need to check discount rules.
	 *
	 * @return bool
	 */
	public function check_free_gifts_condition( $gift_rule, $product ) {
		$conditions = $gift_rule['free_gifts_condition'];
		$is_active  = false;
		$is_exclude = false;

		// @codeCoverageIgnoreStart
		if ( 'variation' === $product->get_type() ) {
			$product = wc_get_product( $product->get_parent_id() );
		}
		// @codeCoverageIgnoreEnds

		foreach ( $conditions as $id => $condition ) {
			$conditions[ $id ]['condition_priority'] = $this->get_condition_priority( $condition['type'] );
		}

		uasort( $conditions, array( $this, 'sort_by_priority' ) );

		foreach ( $conditions as $condition ) {
			switch ( $condition['type'] ) {
				case 'all':
					$is_active = 'include' === $condition['comparison'];

					if ( 'exclude' === $condition['comparison'] ) {
						$is_exclude = true;
					}
					break;
				case 'product':
					$is_needed_product = (int) $product->get_id() === (int) $condition['query'];

					if ( $is_needed_product ) {
						if ( 'exclude' === $condition['comparison'] ) {
							$is_active  = false;
							$is_exclude = true;
						} else {
							$is_active = true;
						}
					}

					break;
				case 'product_type':
					$is_needed_type = $product->get_type() === $condition['product-type-query'];

					if ( $is_needed_type ) {
						if ( 'exclude' === $condition['comparison'] ) {
							$is_active  = false;
							$is_exclude = true;
						} else {
							$is_active = true;
						}
					}
					break;
				case 'product_cat':
				case 'product_tag':
				case 'product_attr_term':
					$terms = wp_get_post_terms( $product->get_id(), get_taxonomies(), array( 'fields' => 'ids' ) );

					if ( $terms ) {
						$is_needed_term = in_array( (int) $condition['query'], $terms, true );

						if ( $is_needed_term ) {
							if ( 'exclude' === $condition['comparison'] ) {
								$is_active  = false;
								$is_exclude = true;
							} else {
								$is_active = true;
							}
						}
					}
					break;
				case 'product_cat_children':
					$terms         = wp_get_post_terms( $product->get_id(), get_taxonomies(), array( 'fields' => 'ids' ) );
					$term_children = get_term_children( $condition['query'], 'product_cat' );

					if ( $terms ) {
						$is_needed_cat_children = count( array_diff( $terms, $term_children ) ) !== count( $terms );

						if ( $is_needed_cat_children ) {
							if ( 'exclude' === $condition['comparison'] ) {
								$is_active  = false;
								$is_exclude = true;
							} else {
								$is_active = true;
							}
						}
					}
					break;
			}

			if ( $is_exclude || $is_active ) {
				break;
			}
		}

		return $is_active;
	}

	/**
	 * Renurn true if the price in the cart within the rules.
	 *
	 * @param array     $gift_rule List of meta box arguments.
	 * @param int|false $total_price Total cart price.
	 *
	 * @return bool
	 */
	public function check_free_gifts_totals( $gift_rule, $total_price = false ) {
		if ( false === $total_price ) {
			$totals      = WC()->cart->get_totals();
			$total_price = $totals['total'];
		}

		$condition = $total_price >= $gift_rule['free_gifts_cart_total_min'];

		if ( ! empty( $gift_rule['free_gifts_cart_total_max'] ) ) {
			$condition = $condition && $total_price <= $gift_rule['free_gifts_cart_total_max'];
		}

		return $condition;
	}

	/**
	 * Get condition priority;
	 *
	 * @param string $type Condition type.
	 *
	 * @return int
	 */
	public function get_condition_priority( $type ) {
		$priority = 50;

		switch ( $type ) {
			case 'all':
				$priority = 10;
				break;
			case 'product_cat_children':
				$priority = 20;
				break;
			case 'product_type':
			case 'product_cat':
			case 'product_tag':
			case 'product_attr_term':
				$priority = 30;
				break;
			case 'product':
				$priority = 40;
				break;
		}

		return apply_filters( 'woodmart_condition_priority', $priority, $type );
	}

	/**
	 * Sort the conditions rule by priority DESC.
	 *
	 * @param array $a The first array to compare.
	 * @param array $b The first array to compare.
	 *
	 * @return int
	 */
	public function sort_by_priority( $a, $b ) {
		return $b['condition_priority'] <=> $a['condition_priority'];
	}

	/**
	 * Check is gift in cart.
	 *
	 * @param int     $product_id Product id.
	 * @param WC_Cart $cart_object WC_Cart instance.
	 *
	 * @return bool
	 */
	public function check_is_gift_in_cart( $product_id, $cart_object = '' ) {
		if ( empty( $cart_object ) ) {
			$cart_object = WC()->cart;
		}

		$product              = wc_get_product( $product_id );
		$variation_id         = 0;
		$variation_attributes = array();
		$check_product_id     = $product_id;

		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		if ( 'variation' === $product->get_type() ) {
			$variation_id         = $product_id;
			$check_product_id     = $product->get_parent_id();
			$variation_attributes = $product->get_variation_attributes();
		}

		$product_cart_id_manual    = $cart_object->generate_cart_id( $check_product_id, $variation_id, $variation_attributes, array( 'wd_is_free_gift' => true ) );
		$product_cart_id_automatic = $cart_object->generate_cart_id(
			$check_product_id,
			$variation_id,
			$variation_attributes,
			array(
				'wd_is_free_gift'           => true,
				'wd_is_free_gift_automatic' => true,
			)
		);

		return $cart_object->find_product_in_cart( $product_cart_id_manual ) || $cart_object->find_product_in_cart( $product_cart_id_automatic );
	}

	/**
	 * Set list of notices.
	 *
	 * @return void
	 */
	public function set_notices() {
		$this->notices = apply_filters(
			'woodmart_free_gifts_notices',
			array(
				'added_successfully' => esc_html__( 'Gift product added successfully.', 'woodmart' ),
				'already_added'      => esc_html__( 'The gift has already been added to the cart.', 'woodmart' ),
				'free_gifts_limit'   => esc_html__( 'There is already a maximum number of gifts in the cart.', 'woodmart' ),
			)
		);
	}

	/**
	 * Get notice.
	 *
	 * @param string $key Notice key.
	 *
	 * @return string
	 */
	public function get_notices( $key ) {
		if ( ! empty( $key ) && isset( $this->notices[ $key ] ) ) {
			return $this->notices[ $key ];
		}

		return $this->notices;
	}
}

Manager::get_instance();
