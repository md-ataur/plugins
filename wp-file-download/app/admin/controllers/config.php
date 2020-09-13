<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Form;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerConfig
 */
class WpfdControllerConfig extends Controller
{
    /**
     * Set theme setting
     *
     * @return void
     */
    public function savetheme()
    {
        $model = $this->getModel();
        $themes = $model->getThemes();
        $theme = Utilities::getInput('selecttheme', 'POST');
        if (!in_array($theme, $themes)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$model->savetheme($theme)) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save theme params
     *
     * @return void
     */
    public function savethemeparams()
    {
        $model = $this->getModel();
        $theme = Utilities::getInput('theme', 'GET', 'none');
        if ((string)$theme === '') {
            $theme = 'default';
        }
        $form = new Form();
        if (WpfdBase::checkExistTheme($theme)) {
            $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'site';
            $formfile .= DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . $theme;
            $formfile .= DIRECTORY_SEPARATOR . 'form.xml';
        } else {
            $formfile = wpfd_locate_theme($theme, 'form.xml');
        }
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveThemeParams($theme, $datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Clone a theme
     *
     * @return void
     */
    public function clonetheme()
    {
        $model = $this->getModel();
        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'clone.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (isset($datas['theme_name']) && ($datas['theme_name'] === '')) {
            $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('Please, Enter theme name', 'wpfd'));
        }
        if (!$model->clonetheme($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        } else {
            $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('Clone theme successfully', 'wpfd'));
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save file params
     *
     * @return void
     */
    public function savetfileparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'file_config.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveFileParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save search params
     *
     * @return void
     */
    public function savesearchparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'search.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->saveSearchParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save admin config
     *
     * @return void
     */
    public function saveadminconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_admin')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save frontend config
     *
     * @return void
     */
    public function savefrontendconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_frontend')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save statistics config
     *
     * @return void
     */
    public function savestatisticsconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_statistics')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }
    /**
     * Save role
     *
     * @return void
     */
    public function saveroles()
    {
        global $wp_roles;

        if (!isset($_POST['wpfd_role_nonce']) ||
            !check_admin_referer('wpfd_role_settings', 'wpfd_role_nonce') ||
            !current_user_can('manage_options')) {
            return;
        }
        $role_caps = get_option('_wpfd_role_caps', array());
        if (!isset($wp_roles)) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Overriding on null
            $wp_roles = new WP_Roles();
        }
        $roles = $wp_roles->role_objects;
        $roles_names = $wp_roles->role_names;

        $post_type = get_post_type_object('wpfd_file');
        $post_type_caps = (array)$post_type->cap;
        $wp_default_caps = array('read', 'read_post', 'read_private_posts', 'create_posts', 'edit_posts',
            'edit_post', 'edit_others_posts', 'delete_post', 'publish_posts');
        foreach ($wp_default_caps as $default_cap) {
            unset($post_type_caps[$default_cap]);
        }
        foreach ($roles as $user_role => $role) {
            $user_role_caps = Utilities::getInput($role->name, 'POST', 'none');
            foreach ($post_type_caps as $post_key => $post_cap) {
                if (isset($user_role_caps[$post_key]) && ($user_role_caps[$post_key] === 'on' || (int) $user_role_caps[$post_key] === 1)) {
                    $role->add_cap($post_key);
                } else {
                    $role->remove_cap($post_key);
                }
            }
        }
        $this->redirect('admin.php?page=wpfd-config#wpfd-user-roles');

        wp_die();
    }
    /**
     * Save notifications params
     *
     * @return void
     */
    public function savenotificationsparams()
    {
        $model = $this->getModel('notification');

        $form     = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'notifications.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-notification&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-notification&error=2');
        }
        $datas                                 = $form->sanitize();
        $datas['notify_add_event_editor']      = Utilities::getInput('notify_add_event_editor', 'POST', 'none');
        $datas['notify_edit_event_editor']     = Utilities::getInput('notify_edit_event_editor', 'POST', 'none');
        $datas['notify_delete_event_editor']   = Utilities::getInput('notify_delete_event_editor', 'POST', 'none');
        $datas['notify_download_event_editor'] = Utilities::getInput('notify_download_event_editor', 'POST', 'none');
        if (!$model->saveNotifications($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3#email_notication_editor');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd') . '#email_notication_editor');
    }

    /**
     * Save mail option params
     *
     * @return void
     */
    public function savemailoption()
    {
        $model = $this->getModel('notification');

        $form     = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'mail_option.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1#mail_option');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2#mail_option');
        }
        $datas = $form->sanitize();
        if (!$model->saveMailOption($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3#mail_option');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd') . '#mail_option');
    }
    /**
     * Save upload params
     *
     * @return void
     */
    public function saveuploadparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'upload.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveUploadParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save file in cate setting
     *
     * @return void
     */
    public function savefilecatparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'file_cat_sortcode.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveFileInCatParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Get Token of Dropbox on authenticate
     *
     * @return void
     */
    public function getTokenKey()
    {
        $dropAuthor = Utilities::getInput('dropAuthor', 'POST', 'string');

        $app = Application::getInstance('WpfdAddon');
        $path_wpfdaddondropbox = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType();
        $path_wpfdaddondropbox .= DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'WpfdAddonDropbox.php';
        require_once $path_wpfdaddondropbox;
        $dropbox = new WpfdAddonDropbox();

        if (!empty($dropAuthor)) {
            //convert code authorCOde to Token
            try {
                $list = $dropbox->convertAuthorizationCode($dropAuthor);
            } catch (Exception $ex) {
                $this->exitStatus(false, esc_html__('The Authorization Code are Wrong!', 'wpfd'));
            }
        } else {
            $this->exitStatus(false, esc_html__('The Authorization code could not be empty!', 'wpfd'));
        }
        if (!isset($list)) {
            $list = array();
        }
        if ($list['accessToken']) {
            $app = Application::getInstance('WpfdAddon');
            $path_wpfdhelper = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
            $path_wpfdhelper .= DIRECTORY_SEPARATOR . 'WpfdHelper.php';
            require_once $path_wpfdhelper;
            //save accessToken to database
            $saveParams = new WpfdAddonHelper();
            $params = $saveParams->getAllDropboxConfigs();
            $params['dropboxToken'] = $list['accessToken'];
            $saveParams->saveDropboxConfigs($params);
        } else {
            $this->exitStatus(false, esc_html__('The Authorization Code are Wrong!', 'wpfd'));
        }
        $this->exitStatus(true, $list);
    }

    /**
     * Get all versions to delete
     *
     * @param boolean $return Return array or json
     *
     * @return array
     */
    public function prepareVersions($return = false)
    {
        global $wpdb;
        if (!wp_verify_nonce(Utilities::getInput('security', 'POST', 'none'), 'wpfd-security')) {
            wp_send_json(array('success' => false, 'message' => esc_html__('Wrong security code!', 'wpfd')));
        }

        $metas = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT DISTINCT pm.post_id, tt.term_id FROM ' . $wpdb->postmeta . ' AS pm
                 INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = pm.post_id
                 INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE pm.meta_key = %s
                AND tt.taxonomy = %s',
                '_wpfd_file_versions',
                'wpfd-category'
            )
        );

        if (!$return) {
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            $total = is_countable($metas) ? count($metas) : 0;
            if ($total > 0) {
                wp_send_json(array('success' => true, 'total' => $total));
            } else {
                wp_send_json(array('success' => false, 'message' => esc_html__('No versions to delete!', 'wpfd')));
            }
        } else {
            $files = array();
            if (!is_wp_error($metas) && !empty($metas)) {
                foreach ($metas as $meta) {
                    $files[] = array(
                        'id' => $meta->post_id,
                        'catId' => $meta->term_id
                    );
                }
            }

            return $files;
        }
    }

    /**
     * Delete all files versions
     *
     * @return void
     */
    public function purgeVersions()
    {
        if (!wp_verify_nonce(Utilities::getInput('security', 'POST', 'none'), 'wpfd-security')) {
            wp_send_json(array('success' => false, 'message' => esc_html__('Wrong security code!', 'wpfd')));
        }
        $keep = Utilities::getInt('keep', 'POST');

        if ((int) $keep > 100) {
            $keep = 100;
        }

        $versions = $this->prepareVersions(true);

        if (is_array($versions) && !empty($versions)) {
            Application::getInstance('Wpfd');
            $fileModel = $this->getModel('file');
            foreach ($versions as $file) {
                $fileModel->deleteOldVersions($file['id'], $file['catId'], $keep);
            }
            wp_send_json(array('success' => true));
        } else {
            wp_send_json(array('success' => false, 'message' => esc_html__('No versions to delete!', 'wpfd')));
        }
    }
}
