<?

add_action( 'wp_enqueue_scripts', 'mpc_load_script', 20 );

function mpc_load_script(){
    wp_enqueue_script( 'mpc_ajax_add_to_cart', plugin_dir_url( __FILE__ ).'../js/mpc-ajax.js',array('jquery'), '1.0', true );
    $i18n = array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'checkout_url' => get_permalink( wc_get_page_id( 'checkout' ) ) );
    wp_localize_script( 'mpc_ajax_add_to_cart', 'mpc_add_to_cart_params', $i18n );

}

add_action('wp_ajax_mpc_ajax', 'mpc_add_to_cart_callback');
add_action('wp_ajax_nopriv_mpc_ajax', 'mpc_add_to_cart_callback');


/**
 * AJAX add to cart.
 */
function mpc_add_to_cart_callback() {

			$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
            $quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
            $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

            if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) ) {

                do_action( 'woocommerce_ajax_added_to_cart', $product_id );

                WC_AJAX::get_refreshed_fragments();
            } else {
                $this->json_headers();

                // If there was an error adding to the cart, redirect to the product page to show any errors
                $data = array(
                    'error' => true,
                    'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
                );
                echo json_encode( $data );
            }
            die();
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
* Remember to remove this
* modify price in mini-cart
*/
add_filter('woocommerce_cart_item_price','mpc_modify_cart_product_price',90,3);

function mpc_modify_cart_product_price( $price, $cart_item, $cart_item_key){
    $id = $cart_item['product_id'];
    $my_current_lang = apply_filters( 'wpml_current_language', NULL );

    if( isset($cart_item['custom_price']) && $my_current_lang == 'da' && get_field('mpc_price_dkk',$id) ){
        $price = wc_price( get_field('mpc_price_dkk',$id));
    }
    elseif ( isset($cart_item['custom_price']) && $my_current_lang == 'en' && get_field('mpc_price_euro',$id) ) {
        $price = wc_price( get_field('mpc_price_euro',$id));
    }
    elseif ( isset($cart_item['custom_price']) && $my_current_lang == 'sv' && get_field('mpc_price_sek',$id) ) {
        $price = wc_price( get_field('mpc_price_sek',$id));
    }

    return $price;
}

//change total in mini-cart
add_filter( 'woocommerce_cart_contents_total', 'filter_woocommerce_cart_contents_total', 10, 1 );

function filter_woocommerce_cart_contents_total( $cart_contents_total ) {
        //$cart_contents_total += $cart_item['custom_price'] * $q ;

    return $cart_contents_total;
};



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
    $my_current_lang = apply_filters( 'wpml_current_language', NULL );
    if(( is_cart() || is_checkout() ) && mpc_get_user_role() == member_role){
        foreach ( WC()->cart->get_cart() as $key => $value ) {

            if(function_exists('get_field') && get_field('mpc_price_euro',$value['product_id']) && $my_current_lang == 'en' ){
                $price = get_field('mpc_price_euro', $value['data']->get_id() );
            }
            elseif(function_exists('get_field') && get_field('mpc_price_dkk',$value['product_id']) && $my_current_lang == 'da'){
               $price = get_field('mpc_price_dkk', $value['product_id'] );
            //        $price = intval($value['custom_price']);
            }
            elseif(function_exists('get_field') && get_field('mpc_price_sek',$value['product_id']) && $my_current_lang == 'sv'){
                $price = get_field('mpc_price_sek', $value['data']->get_id() );
            }

            ($price) ? $value['data']->set_price( floatval( $price) ) : $value['data']->set_price( floatval( $value['data']->get_price() ) ) ;
        }
    }
    elseif (is_checkout() && mpc_get_user_role() != member_role ) {
        foreach ( WC()->cart->get_cart() as $key => $value ) {
            if( $value['data']->is_type('simple') ){
                $value['data']->set_price( $value['data']->get_regular_price() ) ;
            }else{
                $value['data']->set_price( $value['data']->get_regular_price() );
              //  var_dump($value['data']->get_regular_price());
            }
        }
    }
}

// change price in cart based on special formula
add_filter( 'woocommerce_cart_item_subtotal', 'change_cart_item_subtotal', 10, 3 );
function change_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
 if(( is_cart() && mpc_get_user_role() != member_role )  || ( ( is_cart() || is_checkout() ) && mpc_get_user_role() == member_role ) ){
    $q = $cart_item['quantity'];
 //   var_dump($cart_item);
    if ( ( $q > 2 ) && ( isset( $cart_item['custom_price'] ) ) ) {
        $diff = $q - 2;
        $fee =  floatval($cart_item['custom_price']) * 0.10 ;
        $price = ( floatval($cart_item['custom_price']) * $q ) + ($fee * $diff);

       // $subtotal = '<span class="woocommerce-Price-amount amount">'. sprintf("%.2f", $price) . '<span class="woocommerce-Price-currencySymbol">'. get_woocommerce_currency(). '</span></span>';
        $subtotal = wc_price($price);
    }
}
    return $subtotal;
}
//woocommerce_after_calculate_totals
add_action( 'woocommerce_after_calculate_totals', 'action_cart_calculate_totals', 30, 0 );
function action_cart_calculate_totals() {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

if(( is_cart() && mpc_get_user_role() != member_role )  || ( ( is_cart() || is_checkout() ) && mpc_get_user_role() == member_role ) ){
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $q = $cart_item['quantity'];
        if ( ( $q > 2 ) && ( isset( $cart_item['custom_price'] ) ) ){
        ## Displayed subtotal
            $diff = $q - 2;
            $fee =  floatval($cart_item['custom_price']) * 0.10 ;

            WC()->cart->subtotal_ex_tax += floatval($fee * $diff);
            WC()->cart->total += ($fee * $diff);
    }

  }

}

}


 // Get user's role
function mpc_get_user_role( $user = null ) {
    $user = $user ? new WP_User( $user ) : wp_get_current_user();
    return $user->roles ? $user->roles[0] : false;
}