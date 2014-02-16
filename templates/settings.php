<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Omar
 * Date: 11/12/13
 * Time: 11:28 PM
 * To change this template use File | Settings | File Templates.
 */?>

<div class="wrap">
    <h2>Aragon-eRH</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('Aragon_eRH_RSS-group'); ?>
        <?php @do_settings_fields('Aragon_eRH_RSS-group'); ?>
        <table class="form-table">
            <tr valign="top">
                <td scope="row"><label for="Aragon_eRH_setting_rss-url">RSS URL</label></td>
                <td><input type="text" style="min-width: 100%;" name="Aragon_eRH_setting_rss-url" id="Aragon_eRH_setting_rss-url" value="<?php echo get_option('Aragon_eRH_setting_rss-url'); ?>" /></td>
                <td>
                    <?php
                        $url = get_option('Aragon_eRH_setting_rss-url');
                        if ($url == "") {
                            echo '<input type="button" disabled class="Aragon_eRH_Sync button-secondary" value="Sync Now"/>';
                        } else {
                            $nonce = wp_create_nonce("Aragon_eRH_Sync_nonce");
                            $link = admin_url('admin-ajax.php?action=Aragon_eRH_Sync&nonce='.$nonce);
                            echo '<a id="aerh-sync-now" class="sync-now button-secondary" href="' . $link . '" title="Sync Now" data-nonce="' . $nonce . '">Sync Now</a>';
                        }
                    ?>
                </td>
            </tr>
                <td>
                    <label for="Aragon_eRH_setting_rss-bulk-url">Bulk URL</label>
                </td>
                <td>
                    <?php
                        $bulk_nonce = get_option('Aragon_eRH_setting_rss-bulk-url');
                        $bulk_url = add_query_arg('aerh', $bulk_nonce, home_url('/'));
                    ?>
                    <input readonly type="text" style="min-width: 100%;" name="Aragon_eRH_setting_rss-bulk-nonce" id="Aragon_eRH_setting_rss-bulk-url" value="<?php echo $bulk_url; ?>" />
                </td>
                <td>
                    <?php
                        $nonce = wp_create_nonce("Aragon_eRH_Bulk_nonce");
                        $bulk_url = admin_url('admin-ajax.php?action=Aragon_eRH_Bulk_URL');
                        echo '<a id="aerh-bulk-url" class="bulk-url button-secondary" href="' . $bulk_url . '" title="Create New URL" data-nonce="' . $nonce . '">Create New URL</a>' ?>
                </td>
            </tr>
            <tr><td>Total Synced Records: <span id="sync-success" style="color: #FF0000; font-weight: bold;"></span></td></tr>
            <tr><td colspan="2">You can use the shortcode [Aragon-eRH] to display a list in any post.</td></tr>
            <tr><td><?php @submit_button(); ?></td></tr>
        </table>
        <table id="jobs-table" class="widefat">
            <thead>
                <tr><th>ID</th><th>URL</th><th>Title</th><th>Publication Date</th><th>Description</th></tr>
            </thead>
            <tfoot>
                <tr><th>ID</th><th>URL</th><th>Title</th><th>Publication Date</th><th>Description</th></tr>
            </tfoot>
        </table>
    </form>
</div>
<script type="text/javascript">get_table_data()</script>