<?php
if ( ! defined( 'WPINC' ) ) {
	die; // Absolute security gate
}

class Product_listing{
    private $sales_window_days = 15;
    private $lead_time_days = 15;
    private $safety_stock_days = 7;
    private $recent_product_sales_quantities = null;

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
     * Get woocommerce products with pagination.
     */
    public function get_woo_product(){
        $current_page = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;
        $current_page = max( 1, $current_page );
        $posts_per_page = 20;

        // Set up the WooCommerce product query arguments
        $args = [
            'limit'    => $posts_per_page,
            'page'     => $current_page,
            'paginate' => true, 
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
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Estimated Stockout Date', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Risk Level', 'ai-stock-predictor' ) . '</strong></th>';
            echo '      <th scope="col" class="aisp-manage-column"><strong>' . esc_html__( 'Suggested Reorder Quantity', 'ai-stock-predictor' ) . '</strong></th>';
            echo '    </tr>';
            echo '  </thead>';
            echo '  <tbody>';

            foreach ( $products as $key=>$product ) {
                $is_in_stock = $product->is_in_stock();
                $status_label = $is_in_stock ? __( 'In Stock', 'ai-stock-predictor' ) : __( 'Out of Stock', 'ai-stock-predictor' );
                $status_class = $is_in_stock ? 'instock' : 'outofstock';
                $stock_amount = $product->get_stock_quantity();
                $product_index = ( ( $current_page - 1 ) * $posts_per_page ) + $key + 1;
                $prediction = $this->get_stockout_prediction( $product );
                
                // Fallback if SKU is blank
                $sku = $product->get_sku() ? $product->get_sku() : '-';
                ?>
                <tr>
                    <td class="aisp-product-index-column">
                        <strong><?php echo esc_html( $product_index ); ?></strong>
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
                        </span>
                    </td>
                    <td class="aisp-product-stockout-column">
                        <?php echo esc_html( $prediction['stockout_date'] ); ?>
                    </td>
                    <td class="aisp-product-risk-column">
                        <span class="aisp-risk-badge <?php echo esc_attr( $prediction['risk_class'] ); ?>">
                            <?php echo esc_html( $prediction['risk_level'] ); ?>
                        </span>
                    </td>
                    <td class="aisp-product-reorder-column">
                        <?php echo esc_html( $prediction['reorder_quantity'] ); ?>
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
                    'base'      => admin_url( 'admin.php?page=ai-stock-predictor&paged=%#%' ),
                    'format'    => '',
                    'current'   => $current_page,
                    'total'     => $total_pages,
                    'prev_text' => 'Prev',
                    'next_text' => 'Next',
                    'type'      => 'list',
                ] );
                echo '</div>';
            }
        } else {
            echo '<p>' . esc_html__( 'No products found.', 'ai-stock-predictor' ) . '</p>';
        }
    }

    private function get_stockout_prediction( $product ) {
        $stock_quantity = $product->get_stock_quantity();
        
        if ( null === $stock_quantity ) {
            return [
                'stockout_date'    => __( 'Not tracked', 'ai-stock-predictor' ),
                'risk_level'       => __( 'Unknown', 'ai-stock-predictor' ),
                'risk_class'       => 'unknown',
                'reorder_quantity' => '-',
            ];
        }

        $total_sold = $this->get_recent_product_sales_quantity( $product );
        $average_daily_sales = $total_sold / $this->sales_window_days;

        if ( $average_daily_sales <= 0 ) {
            return [
                'stockout_date'    => __( 'No recent sales', 'ai-stock-predictor' ),
                'risk_level'       => __( 'Low', 'ai-stock-predictor' ),
                'risk_class'       => 'low',
                'reorder_quantity' => 0,
            ];
        }

        $coverage_days = $this->lead_time_days + $this->safety_stock_days;
        $reorder_quantity = max( 0, (int) ceil( ( $average_daily_sales * $coverage_days ) - $stock_quantity ) );

        if ( $stock_quantity <= 0 ) {
            return [
                'stockout_date'    => __( 'Today', 'ai-stock-predictor' ),
                'risk_level'       => __( 'High', 'ai-stock-predictor' ),
                'risk_class'       => 'high',
                'reorder_quantity' => $reorder_quantity,
            ];
        }

        $days_until_stockout = $stock_quantity / $average_daily_sales;
        $stockout_timestamp = current_time( 'timestamp' ) + ( (int) ceil( $days_until_stockout ) * DAY_IN_SECONDS );

        if ( $days_until_stockout <= $this->lead_time_days ) {
            $risk_level = __( 'High', 'ai-stock-predictor' );
            $risk_class = 'high';
        } elseif ( $days_until_stockout <= $coverage_days ) {
            $risk_level = __( 'Medium', 'ai-stock-predictor' );
            $risk_class = 'medium';
        } else {
            $risk_level = __( 'Low', 'ai-stock-predictor' );
            $risk_class = 'low';
        }

        return [
            'stockout_date'    => wp_date( get_option( 'date_format' ), $stockout_timestamp ),
            'risk_level'       => $risk_level,
            'risk_class'       => $risk_class,
            'reorder_quantity' => $reorder_quantity,
        ];
    }

    private function get_recent_product_sales_quantity( $product ) {
        $product_id = $product->get_id();

        if ( null === $this->recent_product_sales_quantities ) {
            $this->recent_product_sales_quantities = $this->get_recent_product_sales_quantities();
        }

        return $this->recent_product_sales_quantities[ $product_id ] ?? 0;
    }

    private function get_recent_product_sales_quantities() {
        $sold_quantities = [];
        $orders = wc_get_orders( [
            'status'       => [ 'completed', 'processing' ],
            'limit'        => -1,
            'date_created' => '>' . ( time() - ( DAY_IN_SECONDS * $this->sales_window_days ) ),
        ] );

        foreach ( $orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $quantity = (int) $item->get_quantity();
                $product_id = (int) $item->get_product_id();
                $variation_id = (int) $item->get_variation_id();

                if ( $product_id > 0 ) {
                    $sold_quantities[ $product_id ] = ( $sold_quantities[ $product_id ] ?? 0 ) + $quantity;
                }

                if ( $variation_id > 0 ) {
                    $sold_quantities[ $variation_id ] = ( $sold_quantities[ $variation_id ] ?? 0 ) + $quantity;
                }
            }
        }

        return $sold_quantities;
    }
}
