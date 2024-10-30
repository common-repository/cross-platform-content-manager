<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <div class="nav-tab-wrapper">
        <div class="tab-menu">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <a href="#remote-sites" class="nav-tab nav-tab-active"><?php esc_html_e('Remote Sites', 'cross-platform-content-manager'); ?></a>
            <a href="#sync-settings" class="nav-tab"><?php esc_html_e('Sync Settings', 'cross-platform-content-manager'); ?></a>
            <a href="#social" class="nav-tab"><?php esc_html_e('Social', 'cross-platform-content-manager'); ?></a>
            <a href="#logs" class="nav-tab"><?php esc_html_e('Logs', 'cross-platform-content-manager'); ?></a>
        </div>
        <div class="tab-cta">
            <a href="https://ione.digital" target="_blank">ione.digital</a>
        </div>
    </div>

    <div id="tab-content">
        <div id="remote-sites" class="tab-pane active">
            <?php include(icross_plugin_dir . 'admin/parts/form-child-sites.php'); ?>
        </div>
        <div id="sync-settings" class="tab-pane">
            <h2><?php esc_html_e('Select Post Types to Sync', 'cross-platform-content-manager'); ?></h2>
            <?php include(icross_plugin_dir . 'admin/parts/form-post-types.php'); ?>
        </div>
        <div id="social" class="tab-pane">
            <h2><?php esc_html_e('Social networks to share posts ', 'cross-platform-content-manager'); ?><a href="#"><?php esc_html_e('Coming soon', 'cross-platform-content-manager'); ?></a></h2>
            <ul>
                <li><?php esc_html_e('Instagram', 'cross-platform-content-manager'); ?></li>
                <li><?php esc_html_e('Facebook', 'cross-platform-content-manager'); ?></li>
                <li><?php esc_html_e('Twitter', 'cross-platform-content-manager'); ?></li>
                <li><?php esc_html_e('LinkedIn', 'cross-platform-content-manager'); ?></li>
                <li><?php esc_html_e('Threads', 'cross-platform-content-manager'); ?></li>
            </ul>
        </div>
        <div id="logs" class="tab-pane">
            <?php $logs = $this->icross_get_logs(); ?>

            <textarea id="logs-content" disabled><?php echo esc_textarea($logs); ?></textarea>
            <div class="buttons-wrapper">
                <button id="copy-logs-button" class="button copy-logs"><?php esc_html_e('Copy', 'cross-platform-content-manager'); ?></button>
                <button id="clear-logs-button" class="button clear-logs"><?php esc_html_e('Clear Logs', 'cross-platform-content-manager'); ?></button>
            </div>
        </div>
    </div>
</div>