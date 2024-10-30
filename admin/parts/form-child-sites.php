<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="child-sites">

    <h2><?php esc_html_e('Configured Sites', 'cross-platform-content-manager'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Site Name', 'cross-platform-content-manager'); ?></th>
                <th><?php esc_html_e('URL', 'cross-platform-content-manager'); ?></th>
                <th><?php esc_html_e('User Name', 'cross-platform-content-manager'); ?></th>
                <th><?php esc_html_e('Application Password', 'cross-platform-content-manager'); ?></th>
                <th><?php esc_html_e('Actions', 'cross-platform-content-manager'); ?></th>
                <th><?php esc_html_e('Remove', 'cross-platform-content-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $icross_remote_sites = get_option('icross_remote_sites', []);
                foreach ($icross_remote_sites as $index => $child_site) {
                    echo '<tr>';
                    echo '<td>' . esc_html($child_site['name']) . '</td>';
                    echo '<td>' . esc_html($child_site['url']) . '</td>';
                    echo '<td>' . esc_html($child_site['username']) . '</td>';
                    if( $child_site['app_password'] ) {
                        echo '<td>' . esc_html(str_repeat('*', 8)) . '</td>';
                    } else {
                        echo '<td></td>';
                    }
                    echo '<td><button type="button" disabled class="sync-all-posts button button-primary" data-index="' . esc_attr($index) . '">' . esc_html__('Sync All Posts', 'cross-platform-content-manager') . '</button></td>';
                    echo '<td><button type="button" class="remove-site" data-index="' . esc_attr($index) . '">&#215;</button></td>';
                    echo '</tr>';
                }
                $sites_count = count( $icross_remote_sites );
            ?>
        </tbody>
    </table>
    <?php if($sites_count < 3) { ?>
        <h2><?php esc_html_e('Add Websites To Sync', 'cross-platform-content-manager'); ?></h2>
        <form id="add-child-site-form" method="post">
            <input type="text" name="icross_nonce" hidden>
            <div class="field-group">
                <label><?php esc_html_e('Site Name:', 'cross-platform-content-manager'); ?></label>
                <input type="text" name="icross_remote_sites[][name]" id="new_site_name" placeholder="<?php esc_attr_e('Enter any site name', 'cross-platform-content-manager'); ?>">
            </div>
            <div class="field-group">
                <label><?php esc_html_e('Child Site URL:', 'cross-platform-content-manager'); ?></label>
                <input type="url" name="icross_remote_sites[][url]" id="new_url" placeholder="<?php esc_attr_e('full site linc with http protocol', 'cross-platform-content-manager'); ?>">
            </div>
            <div class="field-group">
                <label><?php esc_html_e('User Name:', 'cross-platform-content-manager'); ?></label>
                <input type="text" name="icross_remote_sites[][username]" id="new_username" placeholder="<?php esc_attr_e('Admin or Editor username', 'cross-platform-content-manager'); ?>">
            </div>
            <div class="field-group">
                <label><?php esc_html_e('Application Password:', 'cross-platform-content-manager'); ?></label>
                <input type="password" name="icross_remote_sites[][app_password]" id="new_app_password" placeholder="<?php esc_attr_e('Application password of user', 'cross-platform-content-manager'); ?>">
            </div>
            <input type="submit" value="<?php echo esc_attr(__('Add New Site', 'cross-platform-content-manager')); ?>" class="button">
        </form>
    <?php } ?>
</div>