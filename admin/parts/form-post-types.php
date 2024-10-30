<?php
    if ( ! defined( 'ABSPATH' ) ) exit;
?>

<form action="" method="post" id="save-settings">
<?php
    wp_nonce_field('icross_settings_action', 'icross_settings_nonce');

    $sync_settings = get_option('icross_post_types_settings', array());
    $post_types = get_post_types(array('public' => true), 'objects');
    
    if($post_types) { ?>
    <ul class="post-types">
    <?php foreach ($post_types as $post_type) {
        // post types
        if( $post_type->name === "post" || $post_type->name === "page" ) { ?>
        <li class="field-group">
            <input type="checkbox" name="sync_settings[<?php echo esc_attr($post_type->name); ?>]" value="1" id="<?php echo esc_attr($post_type->name); ?>" <?php checked('1', $sync_settings[$post_type->name] ?? '0'); ?>>
            <label for="<?php echo esc_attr($post_type->name); ?>">
                <div class="switcher">
                    <span></span>
                </div>
                <?php echo '<span>' . esc_html($post_type->labels->singular_name) . '</span>'; ?>
            </label>
        </li>
        <?php }
    } ?>
    </ul>

    <h2><?php esc_html_e('Select SEO plugin that you are using', 'cross-platform-content-manager'); ?></h2>
    <ul class="seo-features">
        <li class="field-group">
            <input type="radio" name="metadata" id="rank" disabled>
            <label for="rank">
                <div class="switcher">
                    <span></span>
                </div>
                <span><?php esc_html_e('Rank Math', 'cross-platform-content-manager'); ?></span>
                <a href="#" class="soon"><?php esc_html_e('Coming soon', 'cross-platform-content-manager'); ?></a>
            </label>
        </li>
        <li class="field-group">
            <input type="radio" name="metadata" id="yoast" disabled>
            <label for="yoast">
                <div class="switcher">
                    <span></span>
                </div>
                <span><?php esc_html_e('Yoast Seo', 'cross-platform-content-manager'); ?></span>
                <a href="#" class="soon"><?php esc_html_e('Coming soon', 'cross-platform-content-manager'); ?></a>
            </label>
        </li>
    </ul>
    <?php } ?>
    <input type="submit" class="button button-primary" value="<?php esc_html_e('Save Settings', 'cross-platform-content-manager'); ?>">
</form>