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
    if(function_exists('get_field') && get_field('mpc_price_euro',$id) && get_woocommerce_currency() == 'EUR' ){
        ?>
        <div id="mpc-separator">OR</div><form class="cart" method="post" enctype="multipart/form-data">
            <?
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
            ) );
            echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_euro',$id).'" />';
            ?>
            <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_euro',$id)) ); ?>
            </button>
        </form><?php
    }
    elseif(function_exists('get_field') && get_field('mpc_price_dkk') && get_woocommerce_currency() == 'DKK'){
        ?>
        <div id="mpc-separator">OR</div><form class="cart" method="post" enctype="multipart/form-data">
            <?
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
            ) );
            echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_dkk',$id).'" />';
            ?>
            <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_dkk',$id)) ); ?>
            </button>
        </form><?php
    }
    elseif(function_exists('get_field') && get_field('mpc_price_sek') && get_woocommerce_currency() == 'SEK'){
        ?>
        <div id="mpc-separator">OR</div><form class="cart" method="post" enctype="multipart/form-data">
            <?
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
            ) );
            echo '<input type="hidden" name="custom_price" value="'.get_field('mpc_price_sek',$id).'" />';
            ?>
            <button type="submit" name="add-to-cart" value="<? echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt mcp_special_price-product-page">
                <? echo __('Member price: ','mpc').strip_tags( wc_price( get_field('mpc_price_sek',$id)) ); ?>
            </button>
        </form><?php
    }
}

//save custom prie in cart item data
add_filter( 'woocommerce_add_cart_item_data', 'mpc_product_custom_price', 99, 2 );

function mpc_product_custom_price( $cart_item_data, $product_id ) {

    if( isset( $_POST['custom_price'] ) && !empty($_POST['custom_price'])) {
        $cart_item_data[ "custom_price" ] = $_POST['custom_price'];
    }

    return $cart_item_data;

}

/*
*Remember to remove this
* modify price in mini-cart
*/
add_filter('woocommerce_cart_item_price','mpc_modify_cart_product_price',90,3);

function mpc_modify_cart_product_price( $price, $cart_item, $cart_item_key){
    if( isset($cart_item['custom_price']) ){
        $price = sprintf( '%s', wc_price($cart_item["custom_price"]) ) ;
    }

    return $price;
}

//set custom price in cart page
add_action( 'woocommerce_before_calculate_totals', 'mpc_apply_custom_price_to_cart_item', 10 );

function mpc_apply_custom_price_to_cart_item( $cart_object ) {
    if( !WC()->session->__isset( "reload_checkout" )) {
        foreach ( WC()->cart->get_cart() as $key => $value ) {
            if( isset( $value["custom_price"] ) ) {
                $value['data']->set_price(  floatval($value["custom_price"]));
            }
        }
    }
}

//check role and set price
add_action('woocommerce_before_calculate_totals','mpc_check_role',21);

function mpc_check_role(){
    if(is_checkout() && mpc_get_user_role() == member_role){
        foreach ( WC()->cart->get_cart() as $key => $value ) {
            if(function_exists('get_field') && get_field('mpc_price_euro',$value['data']->get_id()) && get_woocommerce_currency() == 'EUR' ){
                $price = get_field('mpc_price_euro', $value['data']->get_id() );
            }
            elseif(function_exists('get_field') && get_field('mpc_price_dkk',$value['data']->get_id()) && get_woocommerce_currency() == 'DKK'){
                $price = get_field('mpc_price_dkk', $value['data']->get_id() );
            }
            elseif(function_exists('get_field') && get_field('mpc_price_sek',$value['data']->get_id()) && get_woocommerce_currency() == 'SEK'){
                $price = get_field('mpc_price_sek', $value['data']->get_id() );
            }

            ($price) ? $value['data']->set_price( floatval( $price) ) : $value['data']->set_price( floatval( $value['data']->get_price() ) ) ;
        }
    }
    elseif (is_checkout() && mpc_get_user_role() != member_role ) {
        foreach ( WC()->cart->get_cart() as $key => $value ) {
            $value['data']->set_price( $value['data']->get_regular_price() ) ;

        }
    }
}

 // Get user's role
function mpc_get_user_role( $user = null ) {
    $user = $user ? new WP_User( $user ) : wp_get_current_user();
    return $user->roles ? $user->roles[0] : false;
}

add_action('admin_head', 'my_custom_css');

function my_custom_css(){
        ?>

        <style type="text/css">
            p.form-field._sale_price_field {
                display: none;
            }

            .currency_blck:first-of-type p:nth-child(3){
                display:none;
            }

            .currency_blck:nth-of-type(2) p:nth-child(3){
                display:none;
            }

        </style>

        <?
}