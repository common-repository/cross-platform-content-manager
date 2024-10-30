<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class iCross_SyncManager {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'icross_add_sync_meta_boxes'));
        add_action('save_post', array($this, 'icross_save_sync_settings'));
    }

    public function icross_add_sync_meta_boxes() {
        $sync_settings = get_option('icross_post_types_settings', array());
        $post_types = array_keys($sync_settings);

        foreach ($post_types as $post_type) {
            if (isset($sync_settings[$post_type]) && $sync_settings[$post_type] == '1') {
                add_meta_box(
                    'sync_meta_box',
                    __('Sites to Sync', 'cross-platform-content-manager'),
                    array($this, 'icross_meta_box_content'),
                    $post_type,
                    'side',
                    'high',
                );
            }
        }
    }

    public function icross_meta_box_content($post) {
        wp_nonce_field('icross_save_sync_settings_action', 'icross_nonce');
        
        $icross_remote_sites = get_option('icross_remote_sites', []);

        foreach ($icross_remote_sites as $index => $site) {
            $isChecked = get_post_meta($post->ID, 'sync_to_site_' . $site['url'], true) === 'yes';
            echo '<label>';
            echo '<input type="checkbox" name="sync_to_sites[]" value="' . esc_attr($site['url']) . '" ' . checked($isChecked, true, false) . '>';
            echo esc_html($site['name']);
            echo '</label><br>';
        }
    }

    public function icross_save_sync_settings($post_id) {
        if (!isset($_POST['icross_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['icross_nonce'])), 'icross_save_sync_settings_action')) {
            return;
        }        
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post', $post_id)) {
            return;
        }

        $sync_to_sites = isset($_POST['sync_to_sites']) ? (array) $_POST['sync_to_sites'] : [];
        $sync_to_sites = array_map('esc_url_raw', $sync_to_sites);

        $icross_remote_sites = get_option('icross_remote_sites', []);
        foreach ($icross_remote_sites as $site) {
            $site_url = esc_url_raw($site['url']);
            if (in_array($site_url, $sync_to_sites)) {
                update_post_meta($post_id, 'sync_to_site_' . sanitize_text_field($site['url']), 'yes');
            } else {
                delete_post_meta($post_id, 'sync_to_site_' . sanitize_text_field($site['url']));
            }
        }
    }
}