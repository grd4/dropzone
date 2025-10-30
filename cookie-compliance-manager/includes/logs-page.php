<?php
/**
 * Consent Logs Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ccm_consent_log';

// Pagination
$per_page = 20;
$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$offset = ($page - 1) * $per_page;

// Get total count
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = ceil($total_items / $per_page);

// Get logs
$logs = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY consent_timestamp DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    )
);

?>

<div class="wrap">
    <h1><?php _e('Cookie Consent Logs', 'cookie-compliance-manager'); ?></h1>
    <p><?php _e('This page shows all recorded user consent for GDPR/CCPA compliance purposes.', 'cookie-compliance-manager'); ?></p>

    <?php if (empty($logs)): ?>
        <p><?php _e('No consent logs found yet.', 'cookie-compliance-manager'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'cookie-compliance-manager'); ?></th>
                    <th><?php _e('IP Address', 'cookie-compliance-manager'); ?></th>
                    <th><?php _e('User Agent', 'cookie-compliance-manager'); ?></th>
                    <th><?php _e('Consent Data', 'cookie-compliance-manager'); ?></th>
                    <th><?php _e('Timestamp', 'cookie-compliance-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo esc_html($log->id); ?></td>
                        <td><?php echo esc_html($log->user_ip); ?></td>
                        <td><?php echo esc_html(substr($log->user_agent, 0, 50)) . (strlen($log->user_agent) > 50 ? '...' : ''); ?></td>
                        <td>
                            <?php
                            $consent = json_decode($log->consent_data, true);
                            if ($consent) {
                                $badges = array();
                                foreach ($consent as $category => $value) {
                                    if ($value) {
                                        $badges[] = '<span class="ccm-consent-badge ccm-consent-' . esc_attr($category) . '">' . esc_html(ucfirst($category)) . '</span>';
                                    }
                                }
                                echo implode(' ', $badges);
                            } else {
                                echo esc_html($log->consent_data);
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($log->consent_timestamp); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $page
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="ccm-export-section" style="margin-top: 30px;">
        <h2><?php _e('Export Logs', 'cookie-compliance-manager'); ?></h2>
        <p><?php _e('For compliance purposes, you can export consent logs to CSV.', 'cookie-compliance-manager'); ?></p>
        <button type="button" class="button button-secondary" id="ccm-export-csv">
            <?php _e('Export to CSV', 'cookie-compliance-manager'); ?>
        </button>
    </div>
</div>

<style>
.ccm-consent-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    margin-right: 5px;
}
.ccm-consent-essential {
    background-color: #d4edda;
    color: #155724;
}
.ccm-consent-analytics {
    background-color: #d1ecf1;
    color: #0c5460;
}
.ccm-consent-marketing {
    background-color: #fff3cd;
    color: #856404;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#ccm-export-csv').on('click', function() {
        window.location.href = '<?php echo admin_url('admin-ajax.php?action=ccm_export_logs'); ?>';
    });
});
</script>
