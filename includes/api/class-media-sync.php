<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class iCross_Media_Sync {

    public function icross_upload_image_to_remote_site($site, $image_url, $post_ID) {
        $image_url = esc_url_raw($image_url);
        
        // checking file type
        $filename = basename($image_url);
        $file_type = wp_check_filetype($filename);
        $mime_type = $file_type['type'];
        
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (!in_array($mime_type, $allowed_mime_types)) {
            error_log("The file type of {$filename} is not allowed for upload.");
            return false;
        }
        
        $image_response = wp_remote_get($image_url);
        if ( is_wp_error( $image_response ) ) {
            $error_message = $image_response->get_error_message();
            error_log("Unable to get image data from: {$image_url}");
            return false;
        } else {
            $image_data = wp_remote_retrieve_body( $image_response );
        }

        // end checking file type

        $image_hash = md5($image_url);
        $remote_image_id_key = 'remote_image_id_' . $image_hash . '_' . md5($site['url']);
        $remote_image_url_key = 'remote_image_url_' . $image_hash . '_' . md5($site['url']);
        $remote_image_id = get_option($remote_image_id_key);

        if (!empty($remote_image_id)) {
            return ['id' => $remote_image_id, 'url' => get_option($remote_image_url_key)];
        }

        $filename = basename($image_url);
        $mime_type = wp_check_filetype($filename)['type'];

        $endpoint = esc_url_raw($site['url']) . '/wp-json/wp/v2/media';
        $username = $site['username'];
        $password = str_replace(' ', '', $site['app_password']);

        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Content-Type' => $mime_type
        );

        $response = wp_remote_post($endpoint, array(
            'method'    => 'POST',
            'headers'   => $headers,
            'body'      => $image_data,
            'timeout'   => 45,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            error_log('Error in uploading image: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code != 201) {
            error_log("Failed to upload image. Response Code: {$response_code}");
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($body['id']) && isset($body['source_url'])) {
            $source_url = esc_url_raw($body['source_url']);
            update_option('remote_image_id_' . $image_hash, $body['id']);
            update_option('remote_image_url_' . $image_hash, $body['source_url']);
            return ['id' => $body['id'], 'url' => $body['source_url']];
        } else {
            error_log('Failed to upload image. No ID or URL returned.');
            return false;
        }
    }

    public function icross_media_in_content($site, $content, $post_ID) {
        $updated_content = $content;

        // find all images in the post content
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $content, $matches);
        $images = $matches[1];

        foreach ($images as $image_url) {
            $image_url = esc_url_raw($image_url);
            $remote_image_data = $this->icross_upload_image_to_remote_site($site, $image_url, $post_ID);

            if ($remote_image_data && !empty($remote_image_data['url'])) {
                $updated_content = str_replace($image_url, esc_url($remote_image_data['url']), $updated_content);
            }
        }

        return $updated_content;
    }
}