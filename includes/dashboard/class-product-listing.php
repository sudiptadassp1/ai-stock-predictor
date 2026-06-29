<?php
if ( ! defined( 'WPINC' ) ) {
	die; // Absolute security gate
}

class Product_listing{
    public function __construct(){
        $this->before_get_woo_product();
        $this->get_woo_product();
    }

    /**
     * Dashbord before list text
     */

    public function before_get_woo_product(){
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Welcome to the AI Stock Predictor Dashboard.', 'ai-stock-predictor' ); ?></p>
        </div>
        <?php
    }


    /**
     * Get woocommerce products with pagination. Fetch 20 products at a time
     */
    public function get_woo_product(){
        // Get the current page number safely
        $current_page = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
        $posts_per_page = 20;

        // Set up the WooCommerce product query arguments
        $args = [
            'limit'    => $posts_per_page,
            'page'     => $current_page,
            'paginate' => true, // Crucial: This returns an object containing both products and total pages
            'status'   => 'publish',
        ];

        // Execute the query
        $results = wc_get_products( $args );
        $products = $results->products;
        $total_pages = $results->max_num_pages;

       

        // Output the products loop
        if ( ! empty( $products ) ) {
            
            echo '<table class="wp-list-table widefat fixed striped products-table">';
            echo '  <thead>';
            echo '    <tr>';
            echo '      <th scope="col" class="manage-column" style="width:50px;"><strong>' . esc_html__( '#', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Image', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Product Name', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Product SKU', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Price', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Current Stock', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="manage-column"><strong>' . esc_html__( 'Prediction', 'ai-stock-predictor' ) . '</strong></th>';
            echo '    </tr>';
            echo '  </thead>';
            echo '  <tbody>';

            foreach ( $products as $key=>$product ) {
                $is_in_stock = $product->is_in_stock();
                $status_label = $is_in_stock ? __( 'In Stock', 'ai-stock-predictor' ) : __( 'Out of Stock', 'ai-stock-predictor' );
                $status_class = $is_in_stock ? 'instock' : 'outofstock';
                
                // Fallback if SKU is blank
                $sku = $product->get_sku() ? $product->get_sku() : '-';
                ?>
                <tr>
                    <td class="product-index-column">
                        <strong><?php echo esc_html( $key ); ?></strong>
                    </td>
                    <td class="product-image-column">
                        <?php 
                        echo wp_kses_post( $product->get_image( 'thumbnail', [ 'style' => 'max-width: 60px; height: auto; display: block;' ] ) ); 
                        ?>
                    </td>
                    <td class="product-name-column">
                        <strong><?php echo esc_html( $product->get_name() ); ?></strong>
                    </td>
                    <td class="product-sku-column">
                        <?php echo esc_html( $sku ); ?>
                    </td>
                    <td class="product-price-column">
                        <?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </td>
                    <td class="product-status-column">
                        <span class="status-badge <?php echo esc_attr( $status_class ); ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                        
                        <?php 
                        if ( $product->managing_stock() ) {
                            $stock_qty = $product->get_stock_quantity();
                            ?>
                            <div class="stock-count" style="font-size: 11px; color: #666; margin-top: 4px;">
                                <?php 
                                printf( esc_html__( '(%s available)', 'ai-stock-predictor' ), esc_html( $stock_qty ) ); 
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </td>
                    <td class="product-prediction-column">
                        <?php echo ""; ?>
                    </td>
                </tr>
                <?php
            }

            echo '  </tbody>';
            echo '</table>';

            // Output Pagination Links
            if ( $total_pages > 1 ) {
                echo '<div class="woocommerce-pagination">';
                echo paginate_links( [
                    'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                    'format'    => '?paged=%#%',
                    'current'   => $current_page,
                    'total'     => $total_pages,
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                    'type'      => 'list',
                ] );
                echo '</div>';
            }
        } else {
            echo '<p>' . esc_html__( 'No products found.', 'ai-stock-predictor' ) . '</p>';
        }
    }
}

