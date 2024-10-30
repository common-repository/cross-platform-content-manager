<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class iCross_Taxonomy_Sync {
    // get taxonomies
    public function icross_get_taxonomies($post_ID) {

        $categories = get_the_category($post_ID);
        $tags = wp_get_post_tags($post_ID);

        $category_data = array_map(function($cat) {
            return ['name' => $cat->name, 'description' => $cat->description];
        }, $categories);

        $tag_data = array_map(function($tag) {
            return ['name' => $tag->name, 'description' => $tag->description];
        }, $tags);

        return ['categories' => $category_data, 'tags' => $tag_data];
    }

    // send taxonomies to remote site
    public function icross_taxonomies_to_remote_site($site, $post_ID, &$post_data) {
        $categories = $post_data['categories'];
        $tags = $post_data['tags'];

        // sync categories
        $remote_categories_ids = $this->icross_individual_taxonomy($site, 'categories', $categories);

        // sync tags
        $remote_tags_ids = $this->icross_individual_taxonomy($site, 'tags', $tags);

        // update $post_data with remote taxonomies ID's
        $post_data['categories'] = $remote_categories_ids;
        $post_data['tags'] = $remote_tags_ids;

        // return updated $post_data
        return $post_data;
    }

    private function icross_individual_taxonomy($site, $taxonomy_type, $taxonomy_names) {
        $remote_taxonomy_ids = [];

        foreach ($taxonomy_names as $name) {
            $exists = $this->icross_check_if_taxonomy_exists($site, $taxonomy_type, $name);

            if (!$exists) {
                $created_id = $this->icross_create_taxonomy_on_remote_site($site, $taxonomy_type, $name);
                if ($created_id) {
                    $remote_taxonomy_ids[] = $created_id;
                }
            } else {
                $remote_taxonomy_ids[] = $exists;
            }
        }
        
        return $remote_taxonomy_ids;
    } 

    private function icross_check_if_taxonomy_exists($site, $taxonomy_type, $taxonomy_data) {
        $name = urlencode(sanitize_text_field($taxonomy_data['name']));
        $url = esc_url_raw($site['url'] . '/wp-json/wp/v2/' . $taxonomy_type . '?search=' . $name);

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['username'] . ':' . $site['app_password'])
            )
        ));

        if (is_wp_error($response)) {
            error_log('Error checking taxonomy: ' . $response->get_error_message());
            return false;
        }

        $taxonomies = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($taxonomies) && is_array($taxonomies)) {
            return $taxonomies[0]['id'];
        }

        return false;
    }

    private function icross_create_taxonomy_on_remote_site($site, $taxonomy_type, $taxonomy_data) {
        $url = esc_url_raw($site['url'] . '/wp-json/wp/v2/' . $taxonomy_type);

        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['username'] . ':' . $site['app_password']),
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array(
                'name' => sanitize_text_field($taxonomy_data['name']),
                'description' => sanitize_text_field($taxonomy_data['description'])
            ))
        ));

        if (is_wp_error($response)) {
            error_log('Error creating taxonomy: ' . $response->get_error_message());
            return false;
        }

        $taxonomy = json_decode(wp_remote_retrieve_body($response), true);
        return isset($taxonomy['id']) ? $taxonomy['id'] : false;
    }

    private function icross_update_taxonomy_on_remote_site($site, $taxonomy_type, $taxonomy_id, $data) {
        $url = esc_url($site['url']) . '/wp-json/wp/v2/' . $taxonomy_type . '/' . $taxonomy_id;
    
        $response = wp_remote_request($url, array(
            'method' => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($site['username'] . ':' . $site['app_password']),
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array('name' => $data['name'], 'description' => $data['description']))
        ));
    
        if (is_wp_error($response)) {
            error_log('Error updating taxonomy: ' . $response->get_error_message());
            return false;
        }
    
        $taxonomy = json_decode(wp_remote_retrieve_body($response), true);
        return isset($taxonomy['id']) ? $taxonomy['id'] : false;
    }    
}
