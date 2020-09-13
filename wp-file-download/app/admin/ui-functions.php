<?php
/*
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version: 4.4
 */

defined('ABSPATH') || die();

/**
 * CONFIGURATION MENU
 */
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_configuration_menu_logo', 10);
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_configuration_menu_search', 20);
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_configuration_menu_items', 30);

/**
 * Display JoomUnited logo in left menu
 *
 * @return void
 */
function wpfd_admin_ui_configuration_menu_logo()
{
    $logo = plugins_url('assets/ui/images/logo-joomUnited-white.png', __FILE__);
    ?>
    <div class="ju-logo">
        <a href="https://www.joomunited.com" target="_blank" title="Visit plugin site">
            <img src="<?php echo esc_url($logo); ?>" alt="WP File Download" />
        </a>
    </div>
    <?php
}

/**
 * Display JoomUnited Search box in left menu
 *
 * @return void
 */
function wpfd_admin_ui_configuration_menu_search()
{
    ?>
    <div class="ju-menu-search">
        <i class="material-icons ju-menu-search-icon">search</i>
        <input type="text" class="ju-menu-search-input" placeholder="Search settings" />
    </div>
    <?php
}

/**
 * Print menu items
 *
 * @return void
 */
function wpfd_admin_ui_configuration_menu_items()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this need to print out menu struct
    echo wpfd_admin_ui_build_menu_html();
}

/**
 * Menu items
 *
 * @return array
 */
function wpfd_admin_ui_configuration_menu_get_items()
{
    $items = array(
        'main-settings'      => array(esc_html__('Main setting', 'wpfd'), 'configform', 10),
        'search-upload'      => array(esc_html__('Search & Upload', 'wpfd'), 'searchform,upload_form', 20),
        'themes'             => array(esc_html__('Themes', 'wpfd'), 'themeforms', 30),
        'clone-theme'        => array(esc_html__('Clone theme', 'wpfd'), 'clone_form', 40),
        'single-file'        => array(esc_html__('Single file', 'wpfd'), 'file_configform,file_catform', 50),
        'translate'          => array(esc_html__('Translate', 'wpfd'), 'translate_form', 60),
        'email-notification' => array(esc_html__('Email notification', 'wpfd'), 'notifications_form', 70),
        'user-roles'         => array(esc_html__('User roles', 'wpfd'), 'rolesform', 80),
    );
    $items = apply_filters('wpfd_admin_ui_configuration_menu_get_items', $items);

    // Sort menu by position
    uasort($items, function ($a, $b) {
        return $a[2] - $b[2];
    });
    return $items;
}

/**
 * PAGES
 */
/**
 * Configuration User Role Page
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_content()
{
    $html = '<h2 class="ju-heading">' . esc_html__('User Roles', 'wpfd') . '</h2>';
    $html .= '<form id="wpfd-role-form" method="post" action="admin.php?page=wpfd-config&amp;task=config.saveroles">';
    $html .= wp_nonce_field('wpfd_role_settings', 'wpfd_role_nonce', true, false);
    $html .= wpfd_admin_ui_user_roles_search();
    $html .= wpfd_admin_ui_user_roles_role_cap_fields();
    $html .= wpfd_admin_ui_button('Save', 'orange-button');
    $html .= '</form>';

    return $html;
}

/**
 * Get global roles
 *
 * @return WP_Roles
 */
function wpfd_admin_ui_user_roles_get_roles()
{
    global $wp_roles;

    if (!isset($wp_roles)) {
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- create if wp_roles is null
        $wp_roles = new WP_Roles();
    }

    return $wp_roles;
}

/**
 * Role search bar
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_search()
{
    ob_start();
    ?>
    <div class="ju-role-search">
        <i class="material-icons ju-role-search-icon">search</i>
        <input type="text" class="ju-role-search-input" placeholder="Search role name" />
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

/**
 * Build roles fields
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_role_cap_fields()
{
    $output = '';
    $c_roles = wpfd_admin_ui_user_roles_get_roles();
    $roles = $c_roles->role_objects;
    $roles_name = $c_roles->role_names;

    // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
    if (is_countable($roles) && !empty($roles)) {
        foreach ($roles as $name => $role) {
            $readableName = $roles_name[$role->name];
            $output .= '<h3 class="ju-heading ju-toggle">' . $readableName . '</h3>';
            $caps = wpfd_admin_ui_user_roles_filter_default_cap();
            $output .= '<div class="ju-settings-option-group">';
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($roles) && !empty($roles)) {
                foreach ($caps as $post_key => $post_cap) {
                    $output .= wpfd_admin_ui_user_roles_role_cap_field($role, $post_key, $post_cap);
                }
            }
            $output .= '</div>';
        }
    }

    return $output;
}

/**
 * Build user role field
 *
 * @param object $role     Role
 * @param string $post_key Key
 * @param string $post_cap Caption
 *
 * @return false|string
 */
function wpfd_admin_ui_user_roles_role_cap_field($role, $post_key, $post_cap)
{
    $name = $role->name . '[' . $post_key . ']';
    $id = 'wpfd-' . $role->name . '-' . $post_key . '-edit';
    $checked = isset($role->capabilities[$post_key]);

    return wpfd_admin_ui_switcher($name, $id, $post_cap, $checked);
}

/**
 * Filter remove default wordpress cap
 *
 * @return array
 */
function wpfd_admin_ui_user_roles_filter_default_cap()
{
    $fileType       = get_post_type_object('wpfd_file');
    $post_type_caps = $fileType->cap;
    $caps            = (array) $post_type_caps;
    $wp_default_caps = array(
        'read',
        'read_post',
        'read_private_posts',
        'create_posts',
        'edit_posts',
        'edit_post',
        'edit_others_posts',
        'delete_post',
        'publish_posts'
    );
    foreach ($wp_default_caps as $default_cap) {
        unset($caps[$default_cap]);
    }

    return $caps;
}
/**
 * HELPERS
 */
/**
 * Switcher
 *
 * @param string  $name    Input name
 * @param string  $id      Input id
 * @param string  $label   Input label
 * @param boolean $checked Input checked
 *
 * @return false|string
 */
function wpfd_admin_ui_switcher($name = '', $id = '', $label = '', $checked = false)
{
    ob_start();
    ?>
    <div class="ju-settings-option">
        <label for="<?php echo esc_attr($id); ?>" class="ju-setting-label"><?php echo esc_html($label); ?></label>
        <div class="ju-switch-button">
            <label class="switch">
                <input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" <?php checked($checked, 1); ?> />
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * Ju button element
 *
 * @param string $label Button label
 * @param string $class Additional button class
 *
 * @return string
 */
function wpfd_admin_ui_button($label = 'Save', $class = '')
{
    return '<input type="submit" value="' . $label . '" class="ju-button ' . $class . '">';
}

/**
 * Build left menu html
 *
 * @param null|array $items Menu items
 *
 * @return string
 */
function wpfd_admin_ui_build_menu_html($items = null)
{
    if (is_null($items)) {
        $items = wpfd_admin_ui_configuration_menu_get_items();
    }
    $html = '<ul class="tabs ju-menu-tabs">';
    foreach ($items as $key => $item) {
        $html .= '<li class="tab">';
        $html .= '<a href="#wpfd-' . $key . '" class="link-tab waves-effect waves-light ' . $key . '">';

        if (wpfd_admin_ui_icon_exists($key)) {
            $icon = plugins_url('app/admin/assets/ui/images/icon-' . $key . '.svg', WPFD_PLUGIN_FILE);
            $html    .= '<img src="' . $icon . '" />&nbsp;';
        } elseif (isset($item[3])) {
            $html    .= '<img src="' . esc_url($item[3]) . '" />&nbsp;';
        }
        $html .= $item[0];
        $html .= '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

/**
 * Check for icon is exists
 *
 * @param string $name Icon name
 *
 * @return boolean
 */
function wpfd_admin_ui_icon_exists($name)
{
    $iconPath = realpath(dirname(WPFD_PLUGIN_FILE)) . DIRECTORY_SEPARATOR;
    $iconPath .= 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    $iconPath .= 'ui' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'icon-' . esc_attr($name) . '.svg';

    if (file_exists($iconPath)) {
        return true;
    }
    return false;
}

/**
 * Build content wrapper
 *
 * @param string $name    Wrapper name
 * @param string $html    Content html
 * @param string $message Message
 *
 * @return string
 */
function wpfd_admin_ui_configuration_build_content($name, $html, $message = '')
{
    $output = '<div class="ju-content-wrapper" id="wpfd-' . esc_attr($name) . '">';
    if ($message !== '') {
        $output .= $message;
    }
    $output .= $html;
    $output .= '</div>';

    return $output;
}

/**
 * Build top bar tabs
 *
 * @param array  $tabs    Tabs array
 * @param string $message Message
 *
 * @return string
 */
function wpfd_admin_ui_configuration_build_tabs($tabs, $message = '')
{
    if (is_array($tabs) && !empty($tabs)) {
        $tabHtml = '<div class="ju-top-tabs-wrapper"><ul class="tabs ju-top-tabs">';
        $html = '';

        foreach ($tabs as $key => $content) {
            $tabHtml .= '<li class="tab">';
            $tabHtml .= '<a href="#' . $key . '" class="link-tab">' . wpfd_admin_ui_configuration_parse_tab_name_from_key($key) . '</a>';
            $tabHtml .= '</li>';
            $html .= '<div class="ju-content-wrapper" id="' . $key . '">';
            if ($message !== '') {
                $html .= $message;
            }
            $html .= $content;
            $html .= '</div>';
        }
        $tabHtml .= '</div>';

        return $tabHtml . $html;
    }

    return '';
}

/**
 * Get tab name from tab key
 *
 * @param string $key Key name
 *
 * @return string
 */
function wpfd_admin_ui_configuration_parse_tab_name_from_key($key)
{
    $key = preg_replace('/\_/', ' ', $key);

    // Made ggd theme name upper in tab name
    if (strtolower($key) === 'ggd') {
        return strtoupper($key);
    }

    return ucfirst($key);
}

/**
 * Load wpfd ui assets
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function wpfd_admin_ux_load_assets($hook)
{
    if (strpos($hook, 'page_wpfd-config') === false) {
        return;
    }
    wp_register_script('wpfd-admin-ui-script-velocity', plugins_url('assets/ui/js/velocity.min.js', __FILE__), array('jquery'), WPFD_VERSION);

    wp_enqueue_style('wpfd-admin-ui-style', plugins_url('assets/ui/css/style.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-waves', plugins_url('assets/ui/css/waves.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-configuration', plugins_url('assets/ui/css/configuration.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('jquery-qtip-style', plugins_url('assets/ui/css/jquery.qtip.css', __FILE__), array(), WPFD_VERSION, false);

    wp_register_script('wpfd-admin-ui-script-waves', plugins_url('assets/ui/js/waves.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script', plugins_url('assets/ui/js/script.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-configuration', plugins_url('assets/ui/js/configuration.js', __FILE__), array('jquery', 'wpfd-chosen'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-tabs', plugins_url('assets/ui/js/tabs.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('jquery-qtip', plugins_url('assets/ui/js/jquery.qtip.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    // Load fonts
    wp_enqueue_style('wpfd-admin-ui-font-nutiosans', plugins_url('assets/ui/fonts/nutiosans.css', __FILE__));
    $scripts = array(
        'wpfd-admin-ui-script',
        'wpfd-admin-ui-script-configuration',
        'wpfd-admin-ui-script-velocity',
        'wpfd-admin-ui-script-tabs',
        'wpfd-admin-ui-script-waves',
        'jquery-qtip'
    );

    foreach ($scripts as $script) {
        wp_enqueue_script($script);
    }
}
add_action('admin_enqueue_scripts', 'wpfd_admin_ux_load_assets', 10, 1);

/**
 * Load wpfd statistics page assets
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function wpfd_admin_statistics_load_assets($hook)
{
    if (strpos($hook, 'page_wpfd-statistics') === false) {
        return;
    }
    wp_enqueue_style('wpfd-admin-ui-font-nutiosans', plugins_url('assets/ui/fonts/nutiosans.css', __FILE__));
    wp_enqueue_style('wpfd-admin-statistics', plugins_url('assets/ui/css/statistics.css', __FILE__), array(), WPFD_VERSION);

    wp_register_script('wpfd-moment', plugins_url('assets/ui/js/moment.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-daterangepicker', plugins_url('assets/ui/js/daterangepicker.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-statistics', plugins_url('assets/ui/js/statistics.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-chartjs', plugins_url('assets/ui/js/chart.min.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-chosen', plugins_url('app/admin/assets/js/chosen.jquery.min.js', WPFD_PLUGIN_FILE), array('jquery'), WPFD_VERSION);

    wp_enqueue_style('wpfd-daterangepicker-style', plugins_url('assets/ui/css/daterangepicker.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-chartjs-style', plugins_url('assets/ui/css/chart.min.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-chosen-style', plugins_url('app/admin/assets/css/chosen.css', WPFD_PLUGIN_FILE), array(), WPFD_VERSION);

    wp_enqueue_script('jquery');
    wp_enqueue_script('wpfd-moment');
    wp_enqueue_script('wpfd-daterangepicker');
    wp_enqueue_script('wpfd-admin-statistics');
    wp_enqueue_script('wpfd-chartjs');
    wp_enqueue_script('wpfd-chosen');
}
add_action('admin_enqueue_scripts', 'wpfd_admin_statistics_load_assets', 20, 1);
