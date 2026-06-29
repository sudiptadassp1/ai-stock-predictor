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
        <div class="aisp-wrap">
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
        $posts_per_page = 6;

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
            
            echo '<table class="aisp-wp-list-table widefat fixed striped products-table">';
            echo '  <thead>';
            echo '    <tr>';
            echo '      <th scope="col" class="aisp-manage-column" style="width:50px;"><strong>' . esc_html__( '#', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Image', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Product Name', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Product SKU', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Price', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Stock Status', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Prediction', 'ai-stock-predictor' ) . '</strong></th>';
            echo '    </tr>';
            echo '  </thead>';
            echo '  <tbody>';

            foreach ( $products as $key=>$product ) {
                $is_in_stock = $product->is_in_stock();
                $status_label = $is_in_stock ? __( 'In Stock', 'ai-stock-predictor' ) : __( 'Out of Stock', 'ai-stock-predictor' );
                $status_class = $is_in_stock ? 'instock' : 'outofstock';
                $stock_amount = $product->get_stock_quantity();
                
                // Fallback if SKU is blank
                $sku = $product->get_sku() ? $product->get_sku() : '-';
                ?>
                <tr>
                    <td class="aisp-product-index-column">
                        <strong><?php echo esc_html( $key+1 ); ?></strong>
                    </td>
                    <td class="aisp-product-image-column">
                        <?php 
                        echo wp_kses_post( $product->get_image( 'thumbnail', [ 'style' => 'max-width: 60px; height: auto; display: block;' ] ) ); 
                        ?>
                    </td>
                    <td class="aisp-product-name-column">
                        <strong><?php echo esc_html( $product->get_name() ); ?></strong>
                    </td>
                    <td class="aisp-product-sku-column">
                        <?php echo esc_html( $sku ); ?>
                    </td>
                    <td class="aisp-product-price-column">
                        <?php echo wp_kses_post( $product->get_price_html() ); ?>
                    </td>
                    <td class="aisp-product-status-column">
                        <span class="aisp-status-badge <?php echo esc_attr( $status_class ); ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                        <span class="aisp-stock-amount">
                            <?php 
                            if ( $is_in_stock && $stock_amount !== null ) {
                                echo esc_html( sprintf( __( ' (%d available)', 'ai-stock-predictor' ), $stock_amount ) );
                            }
                            ?>
                    </td>
                    <td class="aisp-product-prediction-column">
                        ##
                    </td>
                </tr>
                <?php
            }

            echo '  </tbody>';
            echo '</table>';

            // Output Pagination Links
            if ( $total_pages > 1 ) {
                echo '<div class="aisp-woocommerce-pagination">';
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

