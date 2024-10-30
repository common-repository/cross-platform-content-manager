<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class iCross_Post_Sync {
    private $media_sync;
    private $taxonomies_sync;
    
    public function __construct( iCross_Media_Sync $media_sync, iCross_Taxonomy_Sync $taxonomies_sync ) {
        $this->media_sync = $media_sync;
        $this->taxonomies_sync = $taxonomies_sync;
        add_action('save_post', array($this, 'icross_save_post'), 10, 3);
        add_action('trashed_post', array($this, 'icross_delete_post'));
    }

    public function icross_add_log($message) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
        global $wp_filesystem;
    
        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir['basedir']) . 'icross-logs';
        $log_file = $log_dir . '/icross-logs.log';
    
        if (!$wp_filesystem->is_dir($log_dir)) {
            $wp_filesystem->mkdir($log_dir);
        }
    
        $existing_content = $wp_filesystem->exists($log_file) ? $wp_filesystem->get_contents($log_file) : '';
    
        $current_time = current_time('mysql');
        $log_message = "{$current_time} - {$message}\n";
    
        if (!empty($existing_content)) {
            $log_message = "\n" . $log_message;
        }
    
        $new_content = $existing_content . $log_message;
    
        $wp_filesystem->put_contents($log_file, $new_content, FS_CHMOD_FILE);
    }          

    public function icross_save_post($post_ID, $post, $update) {
        if (!current_user_can('edit_post', $post_ID)) {
            error_log('User attempted to edit post ' . $post_ID . ', but they do not have the necessary permissions.');
            return;
        }

        // ignore autosave & revision
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || $post->post_type === 'revision' || $post->post_status === 'trash') { // || $post->post_status !== 'publish' 
            return;
        }

        // get post type
        $post_type = $post->post_type;

        // get post status
        $post_status = $post->post_status;

        // get sync settings
        $sync_settings = get_option('icross_post_types_settings', []);
        

        // checking for access to sync of the post type
        if (empty($sync_settings[$post->post_type])) {
            error_log("Sync not enabled for post type: {$post->post_type}");
            return;
        }

        // get taxonomies
        $taxonomies = $this->taxonomies_sync->icross_get_taxonomies($post_ID);

        // post data
        $post_data = [
            'title'     => sanitize_text_field($post->post_title),
            'content'   => wp_kses_post($post->post_content),
            'status'    => $post_status,
            'sticky'    => is_sticky($post_ID) ? true : false,
            'thumbnail' => esc_url(get_the_post_thumbnail_url($post_ID))
        ];        

        // add taxonomieas to post data
        $post_data['categories'] = $taxonomies['categories'];
        $post_data['tags'] = $taxonomies['tags'];

        // error_log('Post data: ' . print_r($post_data, true));

        // sites to sync
        $icross_remote_sites = get_option('icross_remote_sites', []);
        foreach ($icross_remote_sites as $site) {
            $meta_key = 'sync_to_site_' . $site['url'];
            $meta_value = get_post_meta($post_ID, $meta_key, true);
            
            if ('yes' === $meta_value) {
                if ($post_status === 'draft') {
                    $this->icross_post_status_to_remote_sites($site, $post_ID, 'draft', $post_data);
                } else {
                    $this->icross_send_data_to_remote_site($site, $post_ID, $post_data);
                }
            }
        }

        // error_log('Sites to sync:' . print_r($icross_remote_sites, true));
    }

    private function icross_send_data_to_remote_site($site, $post_ID, $post_data) {
        
        // post data
        $post_data['content'] = $this->media_sync->icross_media_in_content($site, $post_data['content'], $post_ID);
        $post_data['status'] = get_post_status($post_ID);
        $post_data['sticky'] = is_sticky($post_ID);

        // start syncing taxonomies
        $this->taxonomies_sync->icross_taxonomies_to_remote_site($site, $post_ID, $post_data);

        // get post type
        $post_type = get_post_type($post_ID);
        
        // sync setings
        $sync_settings = get_option('icross_post_types_settings', []);
        
        if (empty($sync_settings[$post_type])) {
            error_log("Sync not enabled for post type: {$post_type}");
            return;
        }

        $featured_image_url = get_the_post_thumbnail_url($post_ID);
        if ($featured_image_url) {
            $remote_image_data = $this->media_sync->icross_upload_image_to_remote_site($site, $featured_image_url, $post_ID);
            if ($remote_image_data && !empty($remote_image_data['id'])) {
                $post_data['featured_media'] = $remote_image_data['id'];
            }
        }
        
        // API url
        $post_type_object = get_post_type_object($post_type);
        $rest_base = isset($post_type_object->rest_base) ? $post_type_object->rest_base : $post_type;
        $child_post_id = get_post_meta($post_ID, 'child_post_id_' . $site['url'], true);
        $base_endpoint = esc_url_raw($site['url']) . '/wp-json/wp/v2/' . esc_attr($rest_base);
        $endpoint = !empty($child_post_id) ? $base_endpoint . '/' . $child_post_id : $base_endpoint;

        $this->icross_add_log("Sending data to website " . esc_url($site['url']) . " for post : '" . esc_html($post_data['title']) ."'.");

        // request
        $method = !empty($child_post_id) ? 'PUT' : 'POST';
        $username = $site['username']; // username of remote site
        $password = str_replace(' ', '', $site['app_password']); // Application Password of remote site
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            'Content-Type'  => 'application/json'
        );

        $response = wp_remote_post($endpoint, array(
            'method'    => $method,
            'headers'   => $headers,
            'body'      => wp_json_encode($post_data),
            'timeout'   => 45,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            error_log('Error in sync: ' . $response->get_error_message());
            set_transient('icross_sync_status', 'error', 60);
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_code = wp_remote_retrieve_response_code($response);
            $response_message = wp_remote_retrieve_response_message($response);

            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($body['id'])) {
                // save ID of the post from remote site
                update_post_meta($post_ID, 'child_post_id_' . $site['url'], $body['id']);
            } else {
                error_log('Sync successful but no post ID returned for ' . $site['url']);
            }
            
            set_transient('icross_sync_status', 'success', 60);
        }

        $this->icross_add_log("Data synced successfully with: " . esc_url($site['url']) . " for post: '" . esc_html($post_data['title']) . "'");
    }

    // sync post status
    private function icross_post_status_to_remote_sites($site, $post_ID, $status, $post_data) {
        $icross_remote_sites = get_option('icross_remote_sites', []);
        foreach ($icross_remote_sites as $site) {
            $remote_post_id = get_post_meta($post_ID, 'child_post_id_' . $site['url'], true);
            if (!$remote_post_id) {
                continue;
            }

            $this->icross_add_log("Syncing post status to website " . esc_url($site['url']) . " for post : '" . esc_html($post_data['title']) . "'.");

            $post_type = get_post_type($post_ID);
            $post_type_object = get_post_type_object($post_type);
            $rest_base = isset($post_type_object->rest_base) ? $post_type_object->rest_base : $post_type;
            $child_post_id = get_post_meta($post_ID, 'child_post_id_' . $site['url'], true);
            $base_endpoint = $site['url'] . '/wp-json/wp/v2/' . $rest_base;
            $endpoint = !empty($child_post_id) ? $base_endpoint . '/' . $child_post_id : $base_endpoint;
    
            $username = $site['username'];
            $password = str_replace(' ', '', $site['app_password']);
            $headers = array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                'Content-Type'  => 'application/json'
            );
    
            $response = wp_remote_post($endpoint, array(
                'method'    => 'PUT',
                'headers'   => $headers,
                'body'      => wp_json_encode(array('status' => $status)),
                'timeout'   => 45,
                'sslverify' => true
            ));
    
            if (is_wp_error($response)) {
                error_log('Error in syncing post status to ' . $site['url'] . ': ' . $response->get_error_message());
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
    
                if ($response_code == 200 || $response_code == 201) {
                    $this->icross_add_log("Post status successfully synced with " . esc_url($site['url']) . " for post :'" . esc_html($post_data['title']) . "'.");
                } else {
                    error_log("Failed to sync post status with {$site['url']}. Response: {$response_body}");
                }
            }
        }
    }

    // delete remote post
    public function icross_delete_post($post_ID) {
        if (!current_user_can('delete_post', $post_ID)) {
            error_log('User attempted to delete post ' . $post_ID . ', but they do not have the necessary permissions.');
            return;
        }

        $icross_remote_sites = get_option('icross_remote_sites', []);
        foreach ($icross_remote_sites as $site) {
            $remote_post_id = get_post_meta($post_ID, 'child_post_id_' . $site['url'], true);
            if ($remote_post_id) {
                $this->icross_delete_remote_post($site, $post_ID, $remote_post_id);
            }
        }
    }
    
    private function icross_delete_remote_post($site, $post_ID, $remote_post_id) {

        $post_title = get_the_title($post_ID);
        $this->icross_add_log("Deleting post '" . esc_html($post_title) . "' from website " . esc_url($site['url']) . ".");
        
        $post_type = get_post_type($post_ID);
        $post_type_object = get_post_type_object($post_type);
        $rest_base = isset($post_type_object->rest_base) ? $post_type_object->rest_base : $post_type;

        $endpoint = esc_url_raw($site['url']) . '/wp-json/wp/v2/' . esc_attr($rest_base) . '/' . intval($remote_post_id);

        $username = $site['username'];
        $password = str_replace(' ', '', $site['app_password']);

        $response = wp_remote_request($endpoint, array(
            'method'    => 'DELETE',
            'headers'   => array(
                'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
            )
        ));

        if (is_wp_error($response)) {
            error_log('Error in deleting post: ' . $response->get_error_message());
        } else {
            $this->icross_add_log("Post '" . esc_html($post_title) . "' deleted successfully on " . esc_url($site['url']) . ".");
        }
    }
}