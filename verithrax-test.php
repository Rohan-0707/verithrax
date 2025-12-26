<?php
/**
 * Plugin Name: Verithrax AI Connector (Mark X)
 * Description: Connects WooCommerce to Python AI. Extracts detailed pricing and attributes.
 * Version: 5.0.0
 * Author: Rohan Kumar Bhoi
 */

if (!defined('ABSPATH')) exit;

// --- CONFIGURATION CONSTANTS ---
define('VERITHRAX_WEBHOOK_URL', 'https://sharolyn-stelliferous-leila.ngrok-free.dev/webhook'); 

// --- 0. ENQUEUE CSS IN HEADER ---
function verithrax_enqueue_styles() {
    $css = '
        /* Import a clean, readable font */
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap");

        /* Main blog container */
        .qwer-poiu {
            font-family: "Inter", sans-serif;
            background-color: #ffffff;
            color: #4a5568;
            line-height: 1.7;
            padding: 0rem 1rem;
        }
        
        .zxcv-asdf {
             max-width: 1200px;
             margin: 0 auto;
        }

        /* Typography */
        .ghjk-qwer { /* Main Title */
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: #1a202c;
            text-align: center;
        }

        .tyui-hjkl { /* Subtitle / Section Header */
            font-size: 2rem;
            font-weight: 700;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
            color: #1a202c;
            text-align: center;
        }

        .bnm-poi { /* Paragraph Text */
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .bnm-poi strong {
            color: #1a202c;
            font-weight: 600;
        }
        
        /* Image and Caption Styling */
        .img-container-zxcv {
            margin: 2rem 0;
            text-align: center;
        }
        .img-container-zxcv img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .img-container-zxcv figcaption {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 0.75rem;
            font-style: italic;
        }


        /* --- Card Grid Styling --- */
        .lkjh-rewq {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            margin: 2.5rem 0;
        }

        /* Individual Card Styling */
        .vbnm-yuio {
            background-color: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            text-align: left;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1 1 320px;
        }
        
        .vbnm-yuio:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Icon Styling */
        .prty-dfgh {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-bottom: 1rem;
        }
        
        .prty-dfgh svg {
            width: 24px;
            height: 24px;
            stroke: white;
        }
        
        /* Icon Colors */
        .hjkl-zxcv { background-color: #4285F4; } /* Blue */
        .asdf-ghjk { background-color: #DB4437; } /* Red */
        .yuiop-bnm { background-color: #0F9D58; } /* Green */
        .cvbnm-lkjh { background-color: #F4B400; } /* Yellow */
        .rewq-zxcv { background-color: #673AB7; } /* Purple */

        /* Card Typography */
        .vbnm-yuio h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .vbnm-yuio p, .vbnm-yuio li {
            font-size: 1rem;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .vbnm-yuio ul {
            padding-left: 0;
            list-style-type: none;
        }
        
        .vbnm-yuio li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .vbnm-yuio li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #0F9D58;
            font-weight: bold;
        }

        /* Table Styling (within a single card-like container) */
        .table-container-fghj {
            background-color: #f7fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 2.5rem 0;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
        }
        
        .table-custom-wert {
            width: 100%;
            border-collapse: collapse;
            font-size: 1rem;
        }

        .table-custom-wert th, .table-custom-wert td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom-wert th {
            font-weight: 600;
            color: #2d3748;
        }
        
        .table-custom-wert tr:last-child td {
            border-bottom: none;
        }
        
        .table-custom-wert td strong {
            font-weight: 500;
        }
        
        /* FAQ Section Styling */
        .faq-item-qazx {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1.5rem;
        }
        .faq-item-qazx h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
            margin: 0 0 0.5rem 0;
        }
         .faq-item-qazx p {
            margin: 0;
            font-size: 1rem;
        }

        /* Product Showcase 2-Column Layout */
        .product-showcase-container {
            display: flex;
            gap: 2rem;
            margin: 2.5rem 0;
            padding: 2rem;
            background-color: #f7fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .product-image-column {
            flex: 0 0 45%;
            max-width: 45%;
        }
        
        .product-image-column img {
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            object-fit: contain;
        }
        
        .product-details-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .product-details-column h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .product-details-column .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }
        
        .product-details-column .product-short-description {
            font-size: 1rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .product-details-column .product-brand {
            font-size: 0.9rem;
            color: #718096;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .product-cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 1rem;
        }
        
        .product-cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
        }

        /* Mobile Responsive adjustments */
        @media (max-width: 768px) {
            .ghjk-qwer { font-size: 1.8rem; }
            .tyui-hjkl { font-size: 1.4rem; }
            .bnm-poi { font-size: 1rem; }
            .vbnm-yuio { padding: 1.5rem; }
            
            .product-showcase-container {
                flex-direction: column;
                padding: 1.5rem;
            }
            
            .product-image-column {
                flex: 1 1 100%;
                max-width: 100%;
                margin-bottom: 1.5rem;
            }
            
            .product-details-column {
                flex: 1 1 100%;
            }
            
            .product-details-column h2 {
                font-size: 1.5rem;
            }
        }
    ';
    
    // Output CSS directly in header
    echo '<style type="text/css" id="verithrax-article-styles">' . $css . '</style>';
}
add_action('wp_head', 'verithrax_enqueue_styles', 100);

// --- 1. SETTINGS PAGE LOGIC ---
function verithrax_register_settings() {
    register_setting('verithrax_options_group', 'verithrax_brand_name');
    register_setting('verithrax_options_group', 'verithrax_founders');
    register_setting('verithrax_options_group', 'verithrax_about_brand');
    register_setting('verithrax_options_group', 'verithrax_wp_username');
    register_setting('verithrax_options_group', 'verithrax_wp_app_password');
    register_setting('verithrax_options_group', 'verithrax_wp_base_url');
    
    // Sanitize callback for app password
    add_filter('sanitize_option_verithrax_wp_app_password', 'sanitize_text_field');
}
add_action('admin_init', 'verithrax_register_settings');

function verithrax_register_options_page() {
    add_options_page('Verithrax Config', 'Verithrax Config', 'manage_options', 'verithrax', 'verithrax_options_page_html');
}
add_action('admin_menu', 'verithrax_register_options_page');

function verithrax_options_page_html() {
    ?>
    <div class="wrap">
        <h1>Verithrax AI Configuration</h1>
        <form method="post" action="options.php">
            <?php settings_fields('verithrax_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><h2>Brand Information</h2></th>
                    <td></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Brand Name</th>
                    <td><input type="text" name="verithrax_brand_name" value="<?php echo esc_attr(get_option('verithrax_brand_name', 'My Brand')); ?>" style="width: 300px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Founders</th>
                    <td><input type="text" name="verithrax_founders" value="<?php echo esc_attr(get_option('verithrax_founders', 'The Founders')); ?>" style="width: 300px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">About Brand / Company Context</th>
                    <td>
                        <textarea name="verithrax_about_brand" rows="5" cols="50"><?php echo esc_textarea(get_option('verithrax_about_brand', '')); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><h2>WordPress API Credentials</h2></th>
                    <td></td>
                </tr>
                <tr valign="top">
                    <th scope="row">WordPress Base URL</th>
                    <td>
                        <input type="url" name="verithrax_wp_base_url" value="<?php echo esc_attr(get_option('verithrax_wp_base_url', home_url())); ?>" style="width: 400px;" />
                        <p class="description">Your WordPress site URL (e.g., https://yoursite.com)</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">WordPress Username</th>
                    <td>
                        <input type="text" name="verithrax_wp_username" value="<?php echo esc_attr(get_option('verithrax_wp_username', '')); ?>" style="width: 300px;" />
                        <p class="description">WordPress username or email for API authentication</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Application Password</th>
                    <td>
                        <input type="password" name="verithrax_wp_app_password" value="<?php echo esc_attr(get_option('verithrax_wp_app_password', '')); ?>" style="width: 300px;" />
                        <p class="description">
                            <a href="<?php echo admin_url('profile.php#application-passwords-section'); ?>" target="_blank">Create Application Password</a> 
                            (Users → Profile → Application Passwords)
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// --- 2. WEBHOOK LOGIC ---

function verithrax_send_product_webhook($post_id, $post, $update) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if ($post->post_type !== 'product') return;

    // Configuration
    $brand_name = get_option('verithrax_brand_name', 'SaroGenix');
    $founders = get_option('verithrax_founders', 'Rohan & Abhigyan');
    $about_brand = get_option('verithrax_about_brand', '');
    
    // WordPress API Credentials
    $wp_base_url = get_option('verithrax_wp_base_url', home_url());
    $wp_username = get_option('verithrax_wp_username', '');
    $wp_app_password = get_option('verithrax_wp_app_password', '');

    // Product Data
    $product = wc_get_product($post_id);
    $image_url = get_the_post_thumbnail_url($post_id, 'full');
    $product_link = get_permalink($post_id);

    // Pricing
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $price_html = strip_tags($product->get_price_html()); // "From $10" or "$10 $5"

    // Attributes (Material, etc.)
    $attributes_list = array();
    foreach ($product->get_attributes() as $attribute) {
        if ($attribute->is_taxonomy()) {
            $terms = wp_get_post_terms($post_id, $attribute->get_name(), array('fields' => 'names'));
            $attributes_list[] = wc_attribute_label($attribute->get_name()) . ': ' . implode(', ', $terms);
        } else {
            $attributes_list[] = $attribute->get_name() . ': ' . $attribute->get_options()[0];
        }
    }
    $attributes_string = implode(' | ', $attributes_list);

    // Payload
    $webhook_data = array(
        'post_id' => $post_id,
        'post_title' => $post->post_title,
        'post_content' => wp_strip_all_tags($post->post_content),
        'product_image' => $image_url,
        'regular_price' => $regular_price,
        'sale_price' => $sale_price,
        'price_display' => $price_html,
        'attributes' => $attributes_string,
        'product_link' => $product_link,
        'brand_name' => $brand_name,
        'founders_name' => $founders,
        'about_brand' => $about_brand,
        'wp_base_url' => $wp_base_url,
        'wp_username' => $wp_username,
        'wp_app_password' => $wp_app_password,
        'action' => $update ? 'updated' : 'created'
    );
    
    $args = array(
        'method' => 'POST',
        'timeout' => 60,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($webhook_data),
    );
    
    wp_remote_post(VERITHRAX_WEBHOOK_URL, $args);
}

add_action('save_post_product', 'verithrax_send_product_webhook', 10, 3);
