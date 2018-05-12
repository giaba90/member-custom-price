<?php
/*
Plugin Name: Membership custom price
Plugin URI: http://github.com/giaba90
Description: A plugin for display a custom price on page product
Version: 1.2
Author: Gianluca Barranca
Author URI: http://www.gianlucabarranca.it
License: GPL2
*/

define("member_role", "s2member_level1");//member special

require_once(plugin_dir_path( __FILE__ ).'includes/custom-price.php');

/*
* Override core function woocommerce_template_loop_add_to_cart
* add new button add to cart below
*
*/
if (!function_exists('woocommerce_template_loop_add_to_cart')) {
function woocommerce_template_loop_add_to_cart($args = array()) {
    global $product;

    if ( $product ) {
        $defaults = array(
            'quantity' => 1,
            'class'    => implode( ' ', array_filter( array(
                    'button',
                    'product_type_' . $product->get_type(),
                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                    $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
            ) ) ),
        );

        $args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

        wc_get_template( 'loop/add-to-cart.php', $args );

        $id  = $product->get_id(); //button product_type_simple add_to_cart_button s7up-ajax_add_to_cart
        if(function_exists('get_field') && get_field('mpc_price_euro') && get_woocommerce_currency() == 'EUR'){
            echo '<a rel="nofollow" href="'.get_permalink( wc_get_page_id( 'shop' ) ).'?add-to-cart='.esc_attr($id).'" data-quantity="1" data-product_id="'.esc_attr($id).'"data-product_custom_price="'.get_field('mpc_price_euro',$id).'" data-product_sku="" class="mpc_shop_button button add_to_cart_button">Member price: '. strip_tags( wc_price( get_field('mpc_price_euro')) ).'</a>';
        }
        elseif(function_exists('get_field') && get_field('mpc_price_dkk') && get_woocommerce_currency() == 'DKK'){
             echo '<a rel="nofollow" href="'.get_permalink( wc_get_page_id( 'shop' ) ).'?add-to-cart='.esc_attr($id).'" data-quantity="1" data-product_id="'.esc_attr($id).'"data-product_custom_price="'.get_field('mpc_price_dkk',$id).'" data-product_sku="" class="mpc_shop_button button add_to_cart_button">Member price: '. strip_tags( wc_price( get_field('mpc_price_dkk')) ).'</a>';
        }
        elseif(function_exists('get_field') && get_field('mpc_price_sek') && get_woocommerce_currency() == 'SEK'){
             echo '<a rel="nofollow" href="'.get_permalink( wc_get_page_id( 'shop' ) ).'?add-to-cart='.esc_attr($id).'" data-quantity="1" data-product_id="'.esc_attr($id).'"data-product_custom_price="'.get_field('mpc_price_sek',$id).'" data-product_sku="" class="mpc_shop_button button add_to_cart_button">Member price: '. strip_tags( wc_price( get_field('mpc_price_sek')) ).'</a>';
        }
    }
}
}

// Display "members price" on page product
add_filter( 'woocommerce_get_price_html', "mpc_display_member_price", 10,2 );

function mpc_display_member_price($price_html,$product){

    $price_html ='<p class="price">'. __('Market Price: ','mpc') .wc_price($product->get_price()).' </p>' ;
    if(function_exists('get_field') && get_field('mpc_price_euro') && get_woocommerce_currency() == 'EUR'){
        $price_html .= '<p class="mpc-members-price product-page">'. __('Members Price: ','mpc') .wc_price(get_field('mpc_price_euro')).' </p>' ;
    }
    elseif(function_exists('get_field') && get_field('mpc_price_dkk') && get_woocommerce_currency() == 'DKK'){
        $price_html .= '<p class="mpc-members-price product-page">'. __('Members Price: ','mpc') .wc_price(get_field('mpc_price_dkk')).' </p>' ;
    }
    elseif(function_exists('get_field') && get_field('mpc_price_sek') && get_woocommerce_currency() == 'SEK'){
        $price_html .= '<p class="mpc-members-price product-page">'. __('Members Price: ','mpc') .wc_price(get_field('mpc_price_sek')).' </p>' ;
    }
    return $price_html;
}

//change add to cart text on shop page
add_filter( 'woocommerce_product_add_to_cart_text', 'mpc_filter_woocommerce_product_add_to_cart_text', 10, 2 );

function mpc_filter_woocommerce_product_add_to_cart_text( $var,$product ) {
    $var = __('Market Price: ','mpc').strip_tags(wc_price( $product->get_price() ) );
    return $var;
}

//change add to cart text for basic price
add_filter( 'woocommerce_product_single_add_to_cart_text', 'mpc_custom_cart_button_text',10 , 2 );

function mpc_custom_cart_button_text($text,$product){
    $text = __('Market Price: ','mpc').strip_tags( wc_price( $product->get_price() ) );
    return $text;
}

// add new button for member price
add_action( 'woocommerce_after_add_to_cart_form', 'mpc_show_content_after_add_to_cart',10,1 );

function mpc_show_content_after_add_to_cart($product_id) {
    global $product;
    $id  = $product_id;

    if( $product->is_type( 'simple' ) ){
    // a simple product

        if(function_exists('get_field') && get_field('mpc_price_euro',$id) && get_woocommerce_currency() == 'EUR' ){
        ?>
            <form class="cart" method="post" enctype="multipart/form-data">
                <?
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
                ) );
                echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_euro',$id).'" />';
                echo '<input type="hidden" name="product_id" value="'. esc_attr( $product->get_id() ).'" />';
                ?>
                <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" data-product_id="<? echo esc_attr( $product->get_id() ); ?>" data-custom_price="<?php echo get_field('mpc_price_euro',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_euro',$id)) ); ?>
                </button>
            </form>
            <?php
        }
        elseif(function_exists('get_field') && get_field('mpc_price_dkk') && get_woocommerce_currency() == 'DKK'){
            ?>
            <form class="cart" method="post" enctype="multipart/form-data">
                <?
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
                ) );
                echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_dkk',$id).'" />';
                echo '<input type="hidden" name="product_id" value="'. esc_attr( $product->get_id() ).'" />';
                ?>
                <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" data-product_id="<? echo esc_attr( $product->get_id() ); ?>" data-custom_price="<?php echo get_field('mpc_price_dkk',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_dkk',$id)) ); ?>
                </button>
            </form>
            <?php
        }
        elseif(function_exists('get_field') && get_field('mpc_price_sek') && get_woocommerce_currency() == 'SEK'){
            ?>
            <form class="cart" method="post" enctype="multipart/form-data">
                <?
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
                ) );
                echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_sek',$id).'" />';
                echo '<input type="hidden" name="product_id" value="'. esc_attr( $product->get_id() ).'" />';
                ?>
                <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" data-product_id="<? echo esc_attr( $product->get_id() ); ?>" data-custom_price="<?php echo get_field('mpc_price_sek',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_sek',$id)) ); ?>
                </button>
            </form><?php
        }

    }
}

//add new button for member price in variable product
add_action('woocommerce_after_variations_form','mpc_show_content_after_variations_form',10,0);

function mpc_show_content_after_variations_form(){
    global $product;
    $id = $product->get_id();

    if(function_exists('get_field') && get_field('mpc_price_euro',$id) && get_woocommerce_currency() == 'EUR' ){
        ?>
            <div class="woocommerce-variation-add-to-cart variations_button">
                <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

                <?php
                do_action( 'woocommerce_before_add_to_cart_quantity' );
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                ) );
                do_action( 'woocommerce_after_add_to_cart_quantity' );
               // echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_euro',$id).'" />';
                ?>

                <button type="submit" data-custom_price="<?php echo get_field('mpc_price_euro',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_euro',$id)) ); ?>
                </button>

                <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>
        <?
    }
    elseif(function_exists('get_field') && get_field('mpc_price_dkk',$id) && get_woocommerce_currency() == 'DKK'){
        ?>
            <div class="woocommerce-variation-add-to-cart variations_button">
                <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

                <?php
                do_action( 'woocommerce_before_add_to_cart_quantity' );
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                ) );
                do_action( 'woocommerce_after_add_to_cart_quantity' );
              //  echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_dkk',$id).'" />';
                ?>

                <button type="submit" data-custom_price="<?php echo get_field('mpc_price_dkk',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_dkk',$id)) ); ?>
                </button>

                <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>
        <?
    }
    elseif(function_exists('get_field') && get_field('mpc_price_sek',$id) && get_woocommerce_currency() == 'SEK'){
        ?>
            <div class="woocommerce-variation-add-to-cart variations_button">
                <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

                <?php
                do_action( 'woocommerce_before_add_to_cart_quantity' );
                woocommerce_quantity_input( array(
                    'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                    'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                    'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                ) );
                do_action( 'woocommerce_after_add_to_cart_quantity' );
               // echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_sek',$id).'" />';
                ?>

                <button type="submit" data-custom_price="<?php echo get_field('mpc_price_sek',$id) ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                    <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_sek',$id)) ); ?>
                </button>

                <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

                <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>
        <?

    }


}


// Remove "Product Type/Product Data" Dropdown Options - WooCommerce
add_filter( 'product_type_selector', 'remove_product_types' );

function remove_product_types( $types ){
    unset( $types['grouped'] );
    unset( $types['external'] );
//    unset( $types['variable'] );

    return $types;
}

//remove product panel tabs from admin panel
add_filter('woocommerce_product_data_tabs', 'remove_linked_products', 10, 1);

function remove_linked_products($tabs){

                unset($tabs['shipping']);
                unset($tabs['advanced']);

                return($tabs);

}


add_action('admin_head', 'my_custom_css');

function my_custom_css(){
        ?>

        <style type="text/css">

            /* nascondere sale price in wpml  variation */
            .wcml_custom_prices_block .wcml_custom_prices_manually_block .currency_blck p:nth-child(3) {
        /*        display: none;
          */  }

            p.form-field._sale_price_field {
                display: none;
            }

            p[class^='_custom_sale_price'], p[class*=' _custom_sale_price']{
                display: none;
            }

            /* nascondere la voce "Set Dates" */
            label[for^='wcml_schedule_manually'], label[for*='wcml_schedule_manually']{
                visibility: hidden;
            }

            /* nascondere voce "connect with translation */
            #icl_document_connect_translations_dropdown{
                display: none;
            }

            /* nascondere pulsante "copy from Danish" */
            input#icl_cfo {
                display: none;
            }

            .icl_pop_info_but {
                display: none;
            }

            /* nascondere pulsante "overwrite with Danish" */
            input#icl_set_duplicate{
                display: none;
            }

        /*  li.attribute_options.attribute_tab,li.advanced_options.advanced_tab {
                display: none !important;
            }
        */

        <? if(ICL_LANGUAGE_CODE == 'en' or ICL_LANGUAGE_CODE == 'sv'): ?>
            .options_group.pricing.show_if_simple.show_if_external.hidden{
                visibility: hidden;
            }
        <? endif;?>

        </style>

        <?
}