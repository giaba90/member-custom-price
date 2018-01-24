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
