(function($){
    "use strict";
    $(document).ready(function() {

$("body").on("click",".mpc_shop_button:not(.product_type_variable)",function(e){

    e.preventDefault();  // Prevent the click from going to the link

    var seff = $(this);
    seff.removeClass('added');
    seff.addClass('loading');
    var product_id = $(this).attr("data-product_id");
    var custom_price = $(this).attr("data-product_custom_price");
     jQuery.ajax({
                type : "post",
                url : mpc_add_to_cart_params.ajax_url,
                data: {
                    action: "mpc_ajax",
                    product_id: product_id,
                    custom_price: custom_price
                },
               success: function(data){
                    seff.removeClass('loading');
                    var cart_content = data.fragments['div.widget_shopping_cart_content'];

                    $('.mini-cart-content').html(cart_content);

                    var count_item = cart_content.split("product-mini-cart").length;
                    var cart_item_count = $('.cart-item-count').html();

                    $('.mini-cart-link .mb-count-ajax').html(count_item-1);
                    var price = $('.mini-cart-content').find('.mini-cart-total').find('.woocommerce-Price-amount').html();
                    $('.mini-cart-link .mb-price').html(price);
                    seff.addClass('added');

                   // console.log(data);
                },
                error: function(MLHttpRequest, textStatus, errorThrown){
                    console.log(errorThrown);
                }
            })

  });
});

})(jQuery);