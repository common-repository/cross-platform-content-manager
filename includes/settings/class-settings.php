<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class iCross_SettingsPage {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'icross_enqueue_scripts'));
        add_action('admin_menu', array($this, 'icross_add_plugin_page'));
        add_action('wp_ajax_save_remote_site', array($this, 'icross_handle_save_remote_site'));
        add_action('wp_ajax_remove_remote_site', array($this, 'icross_remove_remote_site_callback'));
        add_action('wp_ajax_clear_logs', array($this, 'icross_clear_logs_handler'));
        add_action('wp_dashboard_setup', array($this, 'icross_add_icross_widget'));
    }

    public function icross_enqueue_scripts($hook) {
        if ('toplevel_page_icross-settings' != $hook) {
            return;
        }

        // css
        wp_enqueue_style('icross-admin', icross_assets_url . '/css/admin.css', array(), icross_version);

        // js
        wp_enqueue_script('icross-admin', icross_assets_url . '/js/admin.js', array('jquery'), icross_version, true);
        wp_localize_script('icross-admin', 'myAjaxObject', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('remove_remote_site_nonce'),
            'clear_logs_nonce' => wp_create_nonce('clear_logs_nonce')
        ));
    }

    // add plugin page
    public function icross_add_plugin_page() {
        add_menu_page(
            'CPCM',
            'CPCM',
            'manage_options',
            'icross-settings',
            array($this, 'icross_settings_page_html'),
            'dashicons-rest-api',
            6
        );
    }

    public function sanitize_and_prepare_site($site) {
        if (!isset($site['name'], $site['username'], $site['url'], $site['app_password'])) {
            return false;
        }
    
        $sanitized_site = [
            'name' => sanitize_text_field($site['name']),
            'username' => sanitize_text_field($site['username']),
            'app_password' => sanitize_text_field($site['app_password']),
            'url' => ''
        ];
    
        $validated_url = filter_var($site['url'], FILTER_VALIDATE_URL) ? esc_url_raw($site['url']) : '';
        if (!empty($validated_url)) {
            $sanitized_site['url'] = $validated_url;
            return $sanitized_site;
        }
    
        return false;
    }
    
    public function icross_handle_save_remote_site() {
        check_ajax_referer('icross_settings_action', 'icross_settings_nonce');
    
        $icross_remote_sites_raw = isset($_POST['icross_remote_sites']) ? wp_unslash($_POST['icross_remote_sites']) : [];
        $icross_remote_sites = [];
        $existing_icross_remote_sites = get_option('icross_remote_sites', []);
    
        foreach ($icross_remote_sites_raw as $site) {
            $sanitized_site = $this->sanitize_and_prepare_site($site);
            if ($sanitized_site) {
                $existing_icross_remote_sites[] = $sanitized_site;
            }
        }
    
        if (!empty($existing_icross_remote_sites)) {
            update_option('icross_remote_sites', $existing_icross_remote_sites);
            wp_send_json_success('Site added successfully');
        } else {
            wp_send_json_error('No data received');
        }
    
        wp_die();
    }    

    // remove child site from DB
    public function icross_remove_remote_site_callback() {

        check_ajax_referer('remove_remote_site_nonce', 'nonce');
    
        if (current_user_can('manage_options')) {
            $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
            $icross_remote_sites = get_option('icross_remote_sites', []);
    
            if ($index >= 0 && $index < count($icross_remote_sites)) {
                array_splice($icross_remote_sites, $index, 1);
                update_option('icross_remote_sites', $icross_remote_sites);
    
                wp_send_json_success();
            } else {
                wp_send_json_error();
            }
        }
    
        wp_send_json_error();
    }

    // plugin page layout
    public function icross_settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            check_admin_referer('icross_settings_action', 'icross_settings_nonce');
    
            if (isset($_POST['sync_settings'])) {
                $sync_settings = [];
                $post_types = get_post_types(array('public' => true), 'objects');
    
                foreach ($post_types as $post_type) {
                    if (isset($_POST['sync_settings'][$post_type->name])) {
                        $post_type_name = sanitize_key($post_type->name);
                        if ($post_type_name === $post_type->name) {
                            $sync_settings[$post_type_name] = ($_POST['sync_settings'][$post_type_name] === '1') ? '1' : '0';
                        } else {
                            continue;
                        }
                    } else {
                        $sync_settings[$post_type->name] = '0';
                    }
                }
    
                update_option('icross_post_types_settings', $sync_settings);
            }
        }
    
        include( icross_plugin_dir . 'admin/pages/settings-page.php' );
    }

    private function icross_get_logs() {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
    
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'icross-logs';
        $log_file = $log_dir . '/icross-logs.log';
        
        if ($wp_filesystem->exists($log_file)) {
            return $wp_filesystem->get_contents($log_file);
        } else {
            return "Logs are missed.";
        }
    }    
    
    public static function icross_clear_logs_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You have no rules to edit this page!');
            return;
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'clear_logs_nonce')) {
            wp_send_json_error('Nonce verification failed');
            return;
        }        
    
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
    
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'icross-logs';
        $log_file = $log_dir . '/icross-logs.log';
    
        if ($wp_filesystem->exists($log_file)) {
            $wp_filesystem->put_contents($log_file, '');
            wp_send_json_success('Logs were cleared');
        } else {    
            wp_send_json_error('Log file is missed');
        }
    }

    public function icross_add_icross_widget() {
        wp_add_dashboard_widget(
            'icross_widget',
            'CPCM Overview',
            array($this, 'icross_display_icross_widget')
        );
    }

    public function icross_display_icross_widget() {
        $settings_url = admin_url('admin.php?page=icross-settings');

        $icross_remote_sites = get_option('icross_remote_sites', []);
        if (!is_array($icross_remote_sites)) {
            $icross_remote_sites = [];
        }

        $sync_settings = get_option('icross_post_types_settings', []);
        if (!is_array($sync_settings)) {
            $sync_settings = [];
        }
    
        echo '<p><a href="' . esc_url($settings_url) . '">' . count($icross_remote_sites) . ' sites</a> are ready to sync.</p>';
    
        echo '<p>Sync enabled for post types:</p>';
        echo '<ul>';
        foreach ($sync_settings as $post_type => $enabled) {
            if ($enabled) {
                $edit_link = admin_url('edit.php?post_type=' . $post_type);
                echo '<li><a href="' . esc_url($edit_link) . '">' . esc_html($post_type) . '</a></li>';
            }
        }
        echo '</ul>';
    }
}
