<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Filter;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdFilter
 */
class WpfdFilter extends Filter
{
    /**
     * Full text search model instance
     *
     * @var $ftsModel
     */
    private $ftsModel;

    /**
     * Include file in search
     *
     * @var $includeGlobalSearch
     */
    private $includeGlobalSearch;

    /**
     * Load filters
     *
     * @return void
     */
    public function load()
    {
        add_filter('the_content', array($this, 'wpfdReplace'), 999999);
        add_filter('themify_builder_module_content', array($this, 'themifyModuleContent'));
        add_filter('template_include', array($this, 'includeTemplate'), 99);
        add_filter('rewrite_rules_array', array($this, 'wpfdInsertRewriteRules'), 99);
        add_filter('query_vars', array($this, 'wpfdInsertQueryVars'));
        add_action('wp_loaded', array($this, 'wpfdFlushRules'));
        add_action('parse_request', array($this, 'wpfdRedirect'), 1, 1);
        add_shortcode('wpfd_category', array($this, 'categoryShortcode'));
        add_shortcode('wpfd_single_file', array($this, 'singleFileShortcode'));
        add_shortcode('wpfd_files', array($this, 'filesShortcode'));
        // acf pro - filter for every value load
        add_filter('acf/format_value', array($this, 'wpfdAcfLoadValue'), 10, 3);

        // Full text search enable ?
        $configModel  = Model::getInstance('config');
        $searchConfig = $configModel->getSearchConfig();

        $enableFts = ((int) $searchConfig['plain_text_search'] === 1) ? true : false;
        if (!isset($searchConfig['include_global_search'])) {
            $searchConfig['include_global_search'] = 1;
        }
        $this->includeGlobalSearch = ((int) $searchConfig['include_global_search'] === 1) ? true : false;
        if ($this->includeGlobalSearch || $enableFts) {
            add_filter('the_title', array($this, 'wpfdAddMetadata'), 0, 2);
            add_filter('the_posts', array($this, 'wpfdGetMeta'), 10, 2);
            add_filter('the_excerpt', array($this, 'wpfdTheContentSearch'), 10);
        }

        if ($enableFts) {
            $this->ftsModel = Model::getInstance('fts');

            //Set hook to wp search query
            add_action('pre_get_posts', array($this, 'indexPreGetPosts'), 10);
            add_filter('posts_search', array($this, 'indexSqlSelect'), 10, 2);
            add_filter('posts_join', array($this, 'indexSqlJoins'), 10, 2);
            add_filter('posts_search_orderby', array($this, 'indexSqlOrderby'), 10, 2);
            add_filter('the_posts', array($this, 'indexThePosts'), 10, 2);
            add_filter('posts_clauses', array($this, 'indexPostsClauses'), 10, 2);
            add_filter('posts_fields', array($this, 'indexPostsFields'), 10, 2);
            add_filter('posts_distinct', array($this, 'indexPostsDistinct'), 10, 2);
        } elseif ($this->includeGlobalSearch) {
            add_filter('pre_get_posts', array($this, 'wpfdPreGetPosts'), 10);
        }
    }

    /**
     * FULL TEXT SEARCH
     *
     * @param mixed $wpq Wordpress query
     *
     * @return mixed
     */
    public function indexPreGetPosts($wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $cluster_weights = array(
            'post_title' => 1,
            'post_content' => 1,
        );
        if (empty($wpq->query_vars['s'])) {
            return '';
        }
        return $this->ftsModel->sqlPrePosts($wpq, $cluster_weights, $this->includeGlobalSearch);
    }

    /**
     * Index Sql select
     *
     * @param mixed $search Search
     * @param mixed $wpq    Wordpress query
     *
     * @return mixed
     */
    public function indexSqlSelect($search, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $q = $wpq->query_vars;

        return $this->ftsModel->sqlSelect($search, $wpq);
    }

    /**
     * Index Sql joins
     *
     * @param string $join Join query
     * @param mixed  $wpq  Wordpress query
     *
     * @return string
     */
    public function indexSqlJoins($join, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        $cluster_weights = array(
            'post_title' => 1,
            'post_content' => 1,
        );
        return $this->ftsModel->sqlJoins($join, $wpq, $cluster_weights);
    }

    /**
     * Index Sql order by
     *
     * @param string $orderby Order by
     * @param mixed  $wpq     Wordpress query
     *
     * @return string
     */
    public function indexSqlOrderby($orderby, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlOrderby($orderby, $wpq);
    }

    /**
     * Index the posts
     *
     * @param mixed $posts Posts
     * @param mixed $wpq   Wordpress query
     *
     * @return mixed
     */
    public function indexThePosts($posts, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlThePosts($posts, $wpq);
    }

    /**
     * Index posts clauses
     *
     * @param string $clauses Clauses
     * @param mixed  $wpq     Wordpress query
     *
     * @return string
     */
    public function indexPostsClauses($clauses, $wpq)
    {
        if ((!isset($GLOBALS['posts_clauses'])) || (!is_array($GLOBALS['posts_clauses']))) {
            $GLOBALS['posts_clauses'] = array();
        }
        $GLOBALS['posts_clauses'][] = $clauses;
        return $clauses;
    }

    /**
     * Index Posts Fields
     *
     * @param string $fields Fields
     * @param mixed  $wpq    Wordpress query
     *
     * @return string
     */
    public function indexPostsFields($fields, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlPostsFields($fields, $wpq);
    }

    /**
     * Index posts distinct
     *
     * @param string $distinct Distinct
     * @param mixed  $wpq      Wordpress query
     *
     * @return string
     */
    public function indexPostsDistinct($distinct, $wpq)
    {
        if (!$this->ftsModel) {
            $this->ftsModel = Model::getInstance('fts');
        }
        return $this->ftsModel->sqlPostsDistinct($distinct, $wpq);
    }

    /**
     * Include wpfd files in search result
     *
     * @param mixed $query Wordpress query
     *
     * @return void
     */
    public function wpfdPreGetPosts($query)
    {
        if (!is_search()) {
            return;
        }
        $types = array('post', 'page');
        $types = apply_filters('wpfd_search_post_types', $types);
        if (isset($query->query_vars['post_type'])) {
            $types = $query->query_vars['post_type'];
        }

        if (is_search() && $query->is_main_query()) {
            if (is_array($types)) {
                $types[] = 'wpfd_file';
            }

            $query->set('post_type', $types);
        }
    }

    /**
     * Show file infomartion for $post->post_content in template used
     *
     * @param WP_Post[] $posts Array of posts
     * @param WP_Query  $query Query
     *
     * @return array
     */
    public function wpfdGetMeta($posts, $query)
    {
        if (!$query->is_main_query()) {
            return $posts;
        }
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($posts) && !count($posts)) {
            return $posts;
        }

        $files = array();
        $other = array();
        $user = wp_get_current_user();

        foreach ($posts as $post) {
            if ($post->post_type === 'wpfd_file') {
                if (false !== $this->wpfdCheckAccess($post, $user)) {
                    $files[] = $post;
                }
            } else {
                $other[] = $post;
            }
        }

        $results = array_merge($files, $other);

        return $results;
    }

    /**
     * Check permission for single post
     *
     * @param mixed $post Post object
     * @param mixed $user Current user object
     *
     * @return boolean
     */
    private function wpfdCheckAccess($post, $user)
    {
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('file');
        $categoryModel = Model::getInstance('category');

        $file = $fileModel->getFile($post->ID);

        if (!$file) {
            return false;
        }

        $category = $categoryModel->getCategory($file->catid);
        if (empty($category) || is_wp_error($category)) {
            return false;
        }

        if ((int) $category->access === 1) {
            $roles = array();

            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }

            $allows = array_intersect($roles, $category->roles);
            $allows_single = false;

            if (isset($category->params['canview']) && $category->params['canview'] === '') {
                $category->params['canview'] = 0;
            }
            if (isset($category->params['canview']) &&
                ((int) $category->params['canview'] !== 0) &&
                is_countable($category->roles) && // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                !count($category->roles)
            ) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }
                if ($allows_single === false) {
                    return false;
                }
            } elseif (isset($category->params['canview']) &&
                      ((int) $category->params['canview'] !== 0) &&
                      is_countable($category->roles) && // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                      count($category->roles)
            ) {
                if ((int) $category->params['canview'] === (int) $user->ID) {
                    $allows_single = true;
                }

                if (!($allows_single === true || !empty($allows))) {
                    return false;
                }
            } else {
                if (empty($allows)) {
                    return false;
                }
            }
        }
        return $file;
    }

    /**
     * Include metadata to file title in search
     *
     * @param string  $title Title
     * @param integer $id    File Id
     *
     * @return string
     */
    public function wpfdAddMetadata($title, $id = null)
    {
        global $wp_query;
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('file');

        if ($wp_query->is_search && get_post_type($id) === 'wpfd_file') {
            $fileInfo = $fileModel->getFile($id);

            if (!$fileInfo) {
                return $title;
            }
            return $title . '.' . $fileInfo->ext . '&nbsp;(' . WpfdHelperFiles::bytesToSize($fileInfo->size) . ')';
        }
        return $title;
    }

    /**
     * Replace content with shortcode
     *
     * @param string $content Content
     *
     * @return string
     */
    public function wpfdTheContentSearch($content)
    {
        global $post;

        if (isset($post->post_type) && $post->post_type === 'wpfd_file') {
            if (is_search()) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escape in wpfdFileContent
                echo self::wpfdFileContent();

                return '';
            }
        }
        return $content;
    }

    /**
     * Get single file content
     *
     * @return string
     */
    public static function wpfdFileContent()
    {
        global $post;

        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('file');
        $fileInfo = $fileModel->getFile($post->ID);

        return do_shortcode(
            '[wpfd_single_file id="' . esc_attr($fileInfo->ID) . '" catid ="' . esc_attr($fileInfo->catid) . '" name ="' . esc_attr($fileInfo->post_title) . '"]'
        );
    }

    /**
     * Replace file permalink by download or preview link
     *
     * @param string $permalink Link
     * @param mixed  $post      Post Object
     *
     * @return string
     */
    public function wpfdSearchPermalink($permalink, $post)
    {
        global $wp_query;
        $app = Application::getInstance('Wpfd');
        $fileModel = Model::getInstance('file');

        if ($wp_query->is_search && $post->post_type === 'wpfd_file') {
            $fileInfo = $fileModel->getFile($post->ID);
            if ($fileInfo) {
                if (isset($fileInfo->viewerlink)) {
                    return $fileInfo->viewerlink;
                }

                return $fileInfo->linkdownload;
            }
        }

        return $permalink;
    }

    /**
     * Function to avoid error when apply_filters
     *
     * @param mixed $termId Term id
     *
     * @return void
     */
    public function wpfdAddonCategoryFrom($termId)
    {
    }

    /**
     * Redirect to download link
     *
     * @param mixed $query Wordpress query
     *
     * @return void
     */
    public function wpfdRedirect($query)
    {
        if (!empty($query->query_vars['wpfd_filename']) && !empty($query->query_vars['wpfd_file_id']) &&
            !empty($query->query_vars['wpfd_category_id']) && !empty($query->query_vars['wpfd_category_name'])
        ) {
            Application::getInstance('Wpfd');
            $path_control_file = dirname(WPFD_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
            $path_control_file .= 'site' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'file.php';
            include_once($path_control_file);
            $fileController = new WpfdControllerFile();
            $fileController->download($query->query_vars['wpfd_file_id'], $query->query_vars['wpfd_category_id']);
            exit;
        } elseif (!empty($query->query_vars['wpfd_category_id']) && !empty($query->query_vars['wpfd_category_name']) &&
            !empty($query->query_vars['wpfd_download_cat'])
        ) {
            Application::getInstance('Wpfd');
            $path_control_files = dirname(WPFD_PLUGIN_FILE) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
            $path_control_files .= 'site' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'files.php';
            include_once($path_control_files);
            $filesController = new WpfdControllerFiles();
            $filesController->download(
                $query->query_vars['wpfd_category_id'],
                $query->query_vars['wpfd_category_name']
            );
            exit;
        }
    }

    /**
     * Method to flush rules
     *
     * @return void
     */
    public function wpfdFlushRules()
    {
        $rules = get_option('rewrite_rules');
        // Flush rule on download only.
        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = rawurlencode($config['uri']);
        }

        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], $seo_uri) > 0 && (!isset($rules['index.php/([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?']) ||
            !isset($rules['([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?']))) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    /**
     * Insert rewrite rules
     *
     * @param array $rules Rulers array
     *
     * @return array
     */
    public function wpfdInsertRewriteRules($rules)
    {
        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = rawurlencode($config['uri']);
        }

        $newrules = array();
        $url1 = site_url();
        $url2 = home_url();

        $index = '';
        if (strpos($url1, $url2) !== false) {
            $index = str_replace($url2, '', $url1);
            $index = trim($index, '/');
        }

        if ($index !== '') {
            $url_str_1 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&';
            $url_str_1 .= 'wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_1 .= '&wpfd_filename=$matches[4]';
            $site_url_1 = site_url($url_str_1);
            $newrules['index.php/' . $index . '/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_1;

            $url_str_2 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_2 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_2 .= '&wpfd_filename=$matches[4]';
            $site_url_2 = site_url($url_str_2);
            $newrules[$index . '/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_2;

            $url_str_3 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_3 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_3 = site_url($url_str_3);
            $newrules['index.php/' . $index . '/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_3;

            $url_str_4 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_4 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_4 = site_url($url_str_4);
            $newrules[$index . '/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_4;
        } else {
            $url_str_1 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_1 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_1 .= '&wpfd_filename=$matches[4]';
            $site_url_1 = site_url($url_str_1);
            $newrules['index.php/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_1;

            $url_str_2 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download';
            $url_str_2 .= '&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]';
            $url_str_2 .= '&wpfd_filename=$matches[4]';
            $site_url_2 = site_url($url_str_2);
            $newrules[$seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = $site_url_2;

            $url_str_3 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_3 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_3 = site_url($url_str_3);
            $newrules['index.php/' . $seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_3;

            $url_str_4 = '/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=files.download';
            $url_str_4 .= '&wpfd_download_cat=$matches[1]&wpfd_category_id=$matches[2]&wpfd_category_name=$matches[3]';
            $site_url_4 = site_url($url_str_4);
            $newrules[$seo_uri . '/([^/]*)/([0-9]+)/([^/]*)/?'] = $site_url_4;
        }
        // Fix conflict with Membership Pro Ultimate WP
        if (class_exists('Ihc_Db')) {
            $inside_page = get_option('ihc_general_register_view_user');
            if ($inside_page && !defined('DOING_AJAX')) {
                $page_slug = Ihc_Db::get_page_slug($inside_page);
                $newrules[$page_slug . '/([^/]+)/?'] = 'index.php?pagename=' . $page_slug . '&ihc_name=$matches[1]';
            }
        }

        return $newrules + $rules;
    }

    /**
     * Append vars for download
     *
     * @param array $vars Query vars
     *
     * @return array
     */
    public function wpfdInsertQueryVars($vars)
    {
        $wpfd_insert_query_array = array(
            'wpfd_filename',
            'wpfd_file_id',
            'wpfd_category_id',
            'wpfd_category_name',
            'wpfd_download_cat'
        );
        foreach ($wpfd_insert_query_array as $v) {
            array_push($vars, $v);
        }
        return $vars;
    }

    /**
     * Archive template for category
     *
     * @param string $template_path Template path
     *
     * @return string
     */
    public function includeTemplate($template_path)
    {
        $post_type = get_query_var('post_type');
        $plugin_path = plugin_dir_path(WPFD_PLUGIN_FILE);
        if (is_tax('wpfd-category')) {
            if (get_post_type() === 'wpfd_file') {
                if (is_archive()) {
                    $theme_file = locate_template(array('archive-wpfd-category.php'));
                    if ($theme_file) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = $plugin_path . 'app/site/themes/archive-wpfd-category.php';
                    }
                }
            } else {
                $wpfd_category = Utilities::getInput('wpfd-category', 'GET', 'none');
                if (!empty($wpfd_category)) {
                    $theme_file = locate_template(array('empty-wpfd-category.php'));
                    if ($theme_file) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = $plugin_path . 'app/site/themes/empty-wpfd-category.php';
                    }
                }
            }
        } elseif ($post_type === 'wpfd_file') {
            if ($this->includeGlobalSearch) {
                $theme_file = locate_template(array('wpfd-single.php'));
                if ($theme_file) {
                    $template_path = $theme_file;
                } else {
                    $template_path = $plugin_path . 'app/site/themes/wpfd-single.php';
                }
            }
        }

        return $template_path;
    }

    /**
     * Method module content
     *
     * @param string $content Content
     *
     * @return string
     */
    public function themifyModuleContent($content)
    {
        $content = $this->wpfdReplace($content);
        return $content;
    }

    /**
     * Method replace content
     *
     * @param string $content Content
     *
     * @return string
     */
    public function wpfdReplace($content)
    {
        $content = preg_replace_callback(
            '@<img[^>]*?data\-wpfdcategory="([0-9]+)".*?>@',
            array($this, 'replace'),
            $content
        );

        //Replace single file
        $content = preg_replace_callback(
            '@<img[^>]*?data\-wpfdfile="(.*?)".*?>@',
            array($this, 'replaceSingle'),
            $content
        );

        return $content;
    }

    /**
     * Replace single category callback
     *
     * @param array $match Match place holder
     *
     * @return string
     */
    private function replace($match)
    {
        add_action('wp_footer', array($this, 'wpfdFooter'));
        return $this->callTheme($match[1]);
    }

    /**
     * Replace single file callback
     *
     * @param array $match Match place holder
     *
     * @return string
     */
    private function replaceSingle($match)
    {
        //get category of file then check access role
        preg_match('@.*data\-category="([0-9]+)".*@', $match[0], $matchCat);
        if (!empty($matchCat)) {
            $catid = (int)$matchCat[1];
        } else {
            $term_list = wp_get_post_terms((int)$match[1], 'wpfd-category', array('fields' => 'ids'));
            $catid = $term_list[0];
        }
        return $this->callSingleFile($match[1], $catid);
    }

    /**
     * Display wpfd scripts in footer
     *
     * @return void
     */
    public function wpfdFooter()
    {
        echo '<div id="wpfd-loading-wrap"><div class="wpfd-loading"></div></div>';
        echo '<div id="wpfd-loading-tree-wrap"><div class="wpfd-loading-tree-bg"></div></div>';
    }

    /**
     * Category shortcode
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function categoryShortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            add_action('wp_footer', array($this, 'wpfdFooter'));
            return $this->callTheme($atts['id'], $atts);
        } else {
            add_action('wp_footer', array($this, 'wpfdFooter'));
            return $this->contentAllCat($atts);
        }
    }

    /**
     * Files shortcode
     *
     * Use: [wpfd_files catids="1,2,3" order="id|title|date|modified|rand" direction="asc|desc" users="<user_id>" limit="<total_display_file>" style="1" download="1" showhits="1"]
     * Params:
     * catids: list category or use 'all' for all categories. Default 'all'
     * order: Order of file accept id,title,date,modified and rand value. Default 'id'
     * direction: Ordering direction. Accept asc or desc. Default 'desc'
     * limit: limit of file will showing, max 100 files. Default '5'
     * download: Allow download or not. Accept 1 or 0. Default 1
     * preview: Allow preview or not. Accept 1 or 0. Default 1
     * showhits: Showing download count or not. Accept 1 or 0. Default 1
     * liststyle: Style for listing. Accept all value for list-style-type css properties. Default 'none'
     * width: Width of the list in pixel. Default '500'
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function filesShortcode($atts)
    {
        $user = wp_get_current_user();

        if (isset($atts['limit'])) {
            // Cast limit to number for security reason
            $limit = (int) $atts['limit'];
        } else {
            $limit = 5;
        }

        // Check for limit
        if ($limit === 0) {
            return '';
        }

        // Setup default value for missing attribute
        if (isset($atts['catids']) && $atts['catids'] !== '') {
            // Filter category id in number only
            $categories = preg_split('/[\D]+/', $atts['catids']);

            // Check for sure there is a valid category id
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($categories) && count($categories) === 0) {
                $categories = 'all';
            }
        } else {
            $categories = 'all';
        }

        if (isset($atts['cat_operator']) && in_array($atts['cat_operator'], array('IN', 'AND', 'NOT IN'))) {
            $categoryOperator = $atts['cat_operator'];
        } else {
            $categoryOperator = 'IN';
        }

        if (isset($atts['order']) && in_array(strtolower($atts['order']), array('id', 'title', 'date', 'modified', 'rand'))) {
            $fileOrder = (strtolower($atts['order']) === 'id') ? strtoupper($atts['order']) : strtolower($atts['order']);
        } else {
            $fileOrder = 'ID';
        }

        if (isset($atts['direction']) && in_array(strtolower($atts['direction']), array('asc', 'desc'))) {
            $orderDirection = strtoupper($atts['direction']);
        } else {
            $orderDirection = 'DESC';
        }

        if (isset($atts['users']) && $atts['users'] !== '') {
            // Filter category id in number only
            $userIds = preg_split('/[\D]+/', $atts['users']);
        }

        if (!isset($atts['style'])) {
            $style = 1;
        } else {
            $style = (int) $atts['style'];
        }

        if (!isset($atts['download'])) {
            $download = 1;
        } else {
            $download = (int) $atts['download'];
        }

        if (!isset($atts['showhits'])) {
            $showhits = 1;
        } else {
            $showhits = (int) $atts['showhits'];
        }

        if (!isset($atts['preview'])) {
            $preview = 1;
        } else {
            $preview = (int) $atts['preview'];
        }

        if (!isset($atts['width'])) {
            $width = 500;
        } else {
            $width = (int) $atts['width'];
        }

        $startList = 'ol';
        if (isset($atts['liststyle']) && in_array($atts['liststyle'], array('disc','armenian','circle','cjk-ideographic','decimal','decimal-leading-zero','georgian','hebrew','hiragana','hiragana-iroha','katakana','katakana-iroha','lower-alpha','lower-greek','lower-latin','lower-roman','none','square','upper-alpha','upper-greek','upper-latin','upper-roman','initial','inherit'))) {
            switch ($atts['liststyle']) {
                case 'disk':
                case 'circle':
                case 'square':
                    $startList = 'ul';
                    break;
                default:
                    $startList = 'ol';
                    break;
            }
            $liststyle = $atts['liststyle'];
        } else {
            $liststyle = 'none';
        }

        // Check permission on categories
        if ($categories === 'all' || $categoryOperator === 'NOT IN') {
            $allCats = array();
            $allCat = get_terms(
                array(
                    'taxonomy' => 'wpfd-category',
                    'hide_empty' => 1
                )
            );
            if (!is_wp_error($allCat)) {
                foreach ($allCat as $cat) {
                    $allCats[] = $cat->term_id;
                }
            }

            // If not have any category, return
            if (empty($allCats)) {
                return '';
            }
        }

        $args = array(
            'post_type' => 'wpfd_file',
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'order_by' => $fileOrder,
            'order' => $orderDirection
        );

        if (isset($userIds) && !empty($userIds)) {
            $args['author__in'] = $userIds;
        }
        // Get categories and check current user have permission to see the files
        if ($categoryOperator === 'NOT IN') {
            $taxQuery = array(
                array (
                    'taxonomy' => 'wpfd-category',
                    'fields' => 'term_id',
                    'terms' => $categories,
                    'operator' => $categoryOperator
                )
            );
        } else {
            $taxQuery = array(
                array (
                    'taxonomy' => 'wpfd-category',
                    'fields' => 'term_id',
                    'terms' => isset($allCats) ? $allCats : $categories,
                    'operator' => $categoryOperator
                )
            );
        }
        $args['relation'] = 'AND';
        $args['tax_query'] = $taxQuery;

        // Fix conflict plugin Go7 Pricing Table
        remove_all_filters('posts_fields');
        remove_filter('the_posts', array($this, 'wpfdGetMeta'), 0);
        $query = new WP_Query($args);
        $posts = $query->get_posts();

        if (is_wp_error($posts)) {
            return '';
        }

        $latestFiles = array();
        $countPost = 0;

        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($posts) && count($posts)) {
            $totalFiles = count($posts);
            foreach ($posts as $post) {
                if ($totalFiles === $countPost) {
                    break;
                }
                if ($countPost < $limit) {
                    $file = $this->wpfdCheckAccess($post, $user);
                    if (false !== $file) {
                        $latestFiles[] = $file;
                        $countPost++;
                    }
                } else {
                    break;
                }
            }
            wp_reset_postdata();
        } else {
            return '';
        }
        //$latestFiles = array_reverse($latestFiles);


        if ($style === 1) {
            wp_enqueue_style('wpfd-google-icon', plugins_url('app/admin/assets/ui/fonts/material-icons.min.css', WPFD_PLUGIN_FILE));
            wp_enqueue_style(
                'wpfd-material-design',
                plugins_url('app/site/assets/css/material-design-iconic-font.min.css', WPFD_PLUGIN_FILE),
                array(),
                WPFD_VERSION
            );
        }
        $content = '<' . $startList . ' style="list-style-type: ' . $liststyle . '; width: ' . $width . 'px" class="wpfd_files">';

        foreach ($latestFiles as $file) {
            // Download button
            $dHtml = '';
            if ($download) {
                $dHtml .= '<a style="float: right;box-shadow: 0 0 0 0;" class="wpfd_files_download" href="' . $file->linkdownload . '">';
                $dHtml .= '&nbsp;<i class="zmdi zmdi-cloud-download"></i></a>';
            }

            // Preview button
            $pHtml = '';
            if ($preview) {
                if (isset($file->openpdflink)) {
                    $pHtml .= '<a style="float: right;box-shadow: 0 0 0 0;width:16px;" class="wpfd_files_preview" target="_blank" href="' . $file->openpdflink . '">';
                    $pHtml .= '<img style="display:inline;margin-right: 5px;" src="' . plugins_url('/app/site/assets/images/open_242.png', WPFD_PLUGIN_FILE) . '" title="' . esc_html__('Open', 'wpfd') . '"/></a>';
                } else {
                    $pHtml .= '<a style="float: right;box-shadow: 0 0 0 0;width:16px;" class="wpfd_files_preview" target="_blank" href="' . $file->viewerlink . '">';
                    $pHtml .= '<img style="display:inline;margin-right: 5px;" src="' . plugins_url('/app/site/assets/images/open_242.png', WPFD_PLUGIN_FILE) . '" title="' . esc_html__('Open', 'wpfd') . '"/></a>';
                }
            }

            $hHtml = '';
            if ($showhits) {
                $hHtml .= '(' . sprintf(esc_html__('Download %d times', 'wpfd'), $file->hits) . ')';
            }

            // Content
            $content .= '<li class="' . strtolower($file->ext) . '">';
            if ($download) {
                $content .= '<a class="wpfd_files_download" href="' . $file->linkdownload . '" style="box-shadow: 0 0 0 0;">';
            }
            $content .= $file->title . '.' . $file->ext;
            if ($download) {
                $content .= '</a>';
            }
            if ($showhits) {
                $content .= $hHtml;
            }

            if ($download) {
                $content .= $dHtml;
            }
            if ($preview) {
                $content .= $pHtml;
            }

            $content .=  '</li>';
        }
        $content .= '</' . $startList . '>';

        return $content;
    }

    /**
     * Single file shortcode
     *
     * @param array $atts Attribute
     *
     * @return string
     */
    public function singleFileShortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            if (isset($atts['catid'])) {
                $catid = $atts['catid'];
            } else {
                $term_list = wp_get_post_terms((int)$atts['id'], 'wpfd-category', array('fields' => 'ids'));
                if (empty($term_list)) {
                    return '';
                }
                $catid = $term_list[0];
            }
            $diplayName = false;
            if (isset($atts['name']) && $atts['name']) {
                $diplayName = $atts['name'];
            }
            return $this->callSingleFile($atts['id'], $catid, $diplayName);
        }
        return '';
    }

    /**
     * Get content of a single file
     *
     * @param mixed $file_id     File Id
     * @param mixed $catid       Category Id
     * @param null  $nameDisplay Name Display
     *
     * @return string
     */
    public function callSingleFile($file_id, $catid, $nameDisplay = null)
    {
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_script(
            'wpfd-frontend',
            plugins_url('app/site/assets/js/frontend.js', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_localize_script('wpfd-frontend', 'wpfdfrontend', array('pluginurl' => plugins_url('', WPFD_PLUGIN_FILE)));

        wp_enqueue_style(
            'wpfd-theme-default',
            plugins_url('app/site/themes/wpfd-default/css/style.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wp_enqueue_style(
            'wpfd-colorbox-viewer',
            plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );
        wpfd_enqueue_assets();
        $app = Application::getInstance('Wpfd');

        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;

        Application::getInstance('Wpfd');
        $modelConfig = Model::getInstance('config');
        $modelCategory = Model::getInstance('category');
        $modelFile = Model::getInstance('file');
        $modelTokens = Model::getInstance('tokens');

        $token = $modelTokens->getOrCreateNew();
        $category = $modelCategory->getCategory((int)$catid);
        if (!$category) {
            return '';
        }
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);
            if (empty($allows)) {
                return '';
            }
        }

        $params = $modelConfig->getConfig();
        $file_params = $modelConfig->getFileConfig();
        $config = $modelConfig->getGlobalConfig();
        $idFile = $file_id;

        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $catid) ;
        if ($categoryFrom === 'googleDrive') {
            $file = apply_filters('wpfdAddonGetGoogleDriveFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'dropbox') {
            $file = apply_filters('wpfdAddonGetDropboxFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'onedrive') {
            $file = apply_filters('wpfdAddonGetOneDriveFile', $idFile, $catid, $token);
        } elseif ($categoryFrom === 'onedrive_business') {
            $file = apply_filters('wpfdAddonGetOneDriveBusinessFile', $idFile, $catid, $token);
        } else {
            $file = $modelFile->getFile($idFile, $catid);
        }
        if (!$file) {
            return '';
        }
        if (isset($file->state) && (int) $file->state === 0) {
            return '';
        }
        if ((int) $config['restrictfile'] === 1) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $canview = isset($file->canview) ? $file->canview : 0;
            $canview = array_map('intval', explode(',', $canview));
            if ($user_id) {
                if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                    return '';
                }
            } else {
                if (!in_array(0, $canview)) {
                    return '';
                }
            }
        }
        $file = (object)$file;
        $file->social = isset($file->social) ? $file->social : 0;

        $bg_color    = WpfdBase::loadValue($file_params, 'singlebg', '#444444');
        $hover_color = WpfdBase::loadValue($file_params, 'singlehover', '#888888');
        $font_color  = WpfdBase::loadValue($file_params, 'singlefontcolor', '#ffffff');
        $showsize    = ((int) WpfdBase::loadValue($params, 'showsize', 1) === 1) ? true : false;
        $singleCss   = '.wpfd-single-file .wpfd_previewlink {margin-top: 10px;display: block;font-weight: bold;}';
        if ($bg_color !== '') {
            $singleCss .= '.wpfd-single-file .wpfd-file-link {background-color: ' . esc_html($bg_color) . ' !important;}';
        }
        if ($font_color !== '') {
            $singleCss .= '.wpfd-single-file .wpfd-file-link {color: ' . esc_html($font_color) . ' !important;}';
        }
        if ($hover_color !== '') {
            $singleCss .= '.wpfd-single-file .wpfd-file-link:hover {background-color: ' . esc_html($hover_color) . ' !important;}';
        }

        if (!$nameDisplay) {
            $nameDisplay = $file->title;
        }

        $variables = array(
            'file' => $file,
            'nameDisplay' => $nameDisplay,
            'showsize' => $showsize,
            'previewType' => WpfdBase::loadValue($config, 'use_google_viewer', 'lightbox'),
        );
        $html = wpfd_get_template_html('tpl-single.php', $variables);
        $html .= '<style>' . $singleCss . '</style>';
        if ((int) $file->social === 1 && defined('WPFDA_VERSION')) {
            return do_shortcode('[wpfdasocial]' . $html . '[/wpfdasocial]');
        } else {
            return $html;
        }
    }

    /**
     * Call category theme
     *
     * @param mixed   $param           Category id
     * @param boolean $shortcode_param Shortcode Param
     *
     * @return string
     */
    private function callTheme($param, $shortcode_param = false)
    {
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        $app = Application::getInstance('Wpfd');
        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;

        $modelConfig     = Model::getInstance('config');
        $modelFiles      = Model::getInstance('files');
        $modelCategories = Model::getInstance('categories');
        $modelCategory   = Model::getInstance('category');
        $modelTokens     = Model::getInstance('tokens');

        $global_settings = $modelConfig->getGlobalConfig();

        $category        = $modelCategory->getCategory($param);
        if (empty($category)) {
            return '';
        }
        $themename = $category->params['theme'];
        $params = $category->params;
        if (isset($global_settings['catparameters']) && (int) $global_settings['catparameters'] === 0) {
            $defaultTheme = $global_settings['defaultthemepercategory'];
            $defaultParams = $modelConfig->getConfig($defaultTheme);
            foreach ($params as $key => $value) {
                if (isset($defaultParams[$key])) {
                    $params[$key] = $defaultParams[$key];
                }
            }
        }
        $params['social'] = isset($params['social']) ? $params['social'] : 0;
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            $singleuser = false;

            if (isset($params['canview']) && $params['canview'] === '') {
                $params['canview'] = 0;
            }

            $canview = isset($params['canview']) ? (int) $params['canview'] : 0;

            if ((int) $global_settings['restrictfile'] === 1) {
                $user = wp_get_current_user();
                $user_id = $user->ID;

                if ($user_id) {
                    if ($canview === $user_id || $canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                } else {
                    if ($canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                }
            }
            if ($canview !== 0 && !count($category->roles)) {
                if ($singleuser === false) {
                    return '';
                }
            } elseif ($canview !== 0 && count($category->roles)) {
                if (!(!empty($allows) || ($singleuser === true))) {
                    return '';
                }
            } else {
                if (empty($allows)) {
                    return '';
                }
            }
        }
        /**
         * Get theme instance follow priority
         *
         * 1. /wp-content/wp-file-download/themes
         * 2. /wp-content/uploads/wpfd-themes
         * 3. /wp-content/plugins/wp-file-download/app/site/themes
         */
        $theme = wpfd_get_theme_instance($themename);

        // Set theme params, separator it to made sure theme can work well
        if (method_exists($theme, 'setAjaxUrl')) {
            $theme->setAjaxUrl(wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()));
        }

        if (method_exists($theme, 'setConfig')) {
            $theme->setConfig($global_settings);
        }

        if (method_exists($theme, 'setPath')) {
            $theme->setPath(Application::getInstance('Wpfd')->getPath());
        }

        if (method_exists($theme, 'setThemeName')) {
            $theme->setThemeName($themename);
        }



        $token = $modelTokens->getOrCreateNew();

        $tpl = null;
        $category = $modelCategory->getCategory($param);
        $orderCol = Utilities::getInput('orderCol', 'GET', 'none');
        $ordering = $orderCol !== null ? $orderCol : $category->ordering;
        $orderDir = Utilities::getInput('orderDir', 'GET', 'none');
        $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;

        $categories = $modelCategories->getCategories($param);

        $description = json_decode($category->description, true);
        $lstAllFile = null;

        if (!empty($description) && isset($description['refToFile'])) {
            if (isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir);
            }
        }

        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $param);
        if ($categoryFrom === 'googleDrive') {
            $tpl = 'googleDrive';
            $files = apply_filters(
                'wpfdAddonGetListGoogleDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );

            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'dropbox') {
            $tpl = 'dropbox';
            $files = apply_filters(
                'wpfdAddonGetListDropboxFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'onedrive') {
            $tpl = 'onedrive';
            $files = apply_filters(
                'wpfdAddonGetListOneDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );

            $categories = $modelCategories->getCategories($param);
        } elseif ($categoryFrom === 'onedrive_business') {
            $tpl = 'onedrive_business';
            $files = apply_filters(
                'wpfdAddonGetListOneDriveBusinessFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );

            $categories = $modelCategories->getCategories($param);
        } else {
            $files = $modelFiles->getFiles($param, $ordering, $orderingdir);
            if (!empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
                foreach ($files as $key => $file) {
                    $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                    $canview = isset($metadata['canview']) ? $metadata['canview'] : 0;
                    $files[$key]->canview = $canview;
                }
            }
        }

        // Check permissiong for User allow to access file feature
        if (!empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
            $user    = wp_get_current_user();
            $user_id = $user->ID;
            foreach ($files as $key => $file) {
                if (!isset($file->canview)) {
                    continue;
                }
                $canview = array_map('intval', explode(',', $file->canview));
                if ($user_id) {
                    if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                        unset($files[$key]);
                    }
                } else {
                    if (!in_array(0, $canview)) {
                        unset($files[$key]);
                    }
                }
            }
        }

        if ($lstAllFile && !empty($lstAllFile)) {
            $files = array_merge($lstAllFile, $files);
        }

        // Reorder for correct ordering
        $ordering_array = array(
            'created_time', 'modified_time', 'hits', 'size', 'ext', 'version', 'title', 'description', 'ordering');
        if (in_array($ordering, $ordering_array)) {
            switch ($ordering) {
                case 'created_time':
                    usort($files, array('WpfdFilter', 'cmpCreated'));
                    break;
                case 'modified_time':
                    usort($files, array('WpfdFilter', 'cmpUpdated'));
                    break;
                case 'hits':
                    usort($files, array('WpfdFilter', 'cmpHits'));
                    break;
                case 'size':
                    usort($files, array('WpfdFilter', 'cmpSize'));
                    break;
                case 'ext':
                    usort($files, array('WpfdFilter', 'cmpExt'));
                    break;
                case 'version':
                    usort($files, array('WpfdFilter', 'cmpVersionNumber'));
                    break;
                case 'description':
                    usort($files, array('WpfdFilter', 'cmpDescription'));
                    break;
                case 'ordering':
                    break;
                case 'title':
                default:
                    usort($files, array('WpfdFilter', 'cmpTitle'));
                    break;
            }
            if (strtoupper($orderingdir) === 'DESC') {
                $files = array_reverse($files);
            }
        }

        $limit = $global_settings['paginationnunber'];
        $total = ceil(count($files) / $limit);

        $page = Utilities::getInput('paged', 'POST', 'string');
        $page = $page !== '' ? $page : 1;
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        if ($theme->getThemeName() !== 'tree') {
            $files = array_slice($files, $offset, $limit);
        }

        $filesx = array();
        // Crop file titles
        if (is_array($files) && !empty($files)) {
            foreach ($files as $i => $file) {
                if (isset($file->state) && (int) $file->state === 0) {
                    continue;
                }
                $filesx[$i]             = $file;
                $filesx[$i]->crop_title = WpfdBase::cropTitle($params, $theme->getThemeName(), $file->post_title);
            }
            unset($files);
            $files = $filesx;
        }

        if ($shortcode_param && isset($shortcode_param['number']) &&
            !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) &&
                (int)$shortcode_param['number'] > 0)
        ) {
            $files = array_slice($files, 0, $shortcode_param['number']);
        }

        $options = array('files' => $files,
            'category' => $category,
            'categories' => $categories,
            'ordering' => $ordering,
            'orderingDirection' => $orderingdir,
            'params' => $params,
            'tpl' => $tpl);
        if ((int) $params['social'] === 1 && defined('WPFDA_VERSION')) {
            $content = do_shortcode(
                '[wpfdasocial]' . $theme->showCategory($options) . ($category->params['theme'] !== 'tree' ?
                    wpfd_category_pagination(
                        array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => $param)
                    ) : ''
                ) . '[/wpfdasocial]'
            );
        } else {
            $content = $theme->showCategory($options) . ($category->params['theme'] !== 'tree' ?
                    wpfd_category_pagination(
                        array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => $param)
                    ) : ''
                );
        }
        return $content;
    }

    /**
     * Get content all Category
     *
     * @param boolean $shortcode_param Shortcode params
     *
     * @return string
     */
    private function contentAllCat($shortcode_param = false)
    {
        wp_enqueue_style(
            'wpfd-front',
            plugins_url('app/site/assets/css/front.css', WPFD_PLUGIN_FILE),
            array(),
            WPFD_VERSION
        );

        $app = Application::getInstance('Wpfd');
        $allFiles = array();
        $files = array();
        if ($shortcode_param && isset($shortcode_param['number']) && !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) && (int)$shortcode_param['number'] > 0)
        ) {
            $param_number = $shortcode_param['number'];
        }
        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;
        $modelCategories = Model::getInstance('categories');
        $categories = $modelCategories->getLevelCategories();
        $modelConfig = Model::getInstance('config');
        $global_settings = $modelConfig->getGlobalConfig();

        foreach ($categories as $keyCat => $category) {
            $termId = $category->term_id;
            if (!is_numeric($termId) && isset($category->wp_term_id)) {
                $termId = $category->wp_term_id;
            }
            $allFile1 = $this->fileAllCat($termId, $shortcode_param);
            if (!empty($allFile1)) {
                foreach ($allFile1 as $key => $val) {
                    if (!empty($val)) {
                        $allFiles[] = $val;
                    }
                }
            }
        }

        $ordering = 'created_time';
        $orderingdir = 'desc';
        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }

        $ordering_array = array(
            'created_time', 'modified_time', 'hits', 'size', 'ext', 'version', 'title', 'description', 'ordering');
        if (in_array($ordering, $ordering_array)) {
            switch ($ordering) {
                case 'created_time':
                    usort($allFiles, array('WpfdFilter', 'cmpCreated'));
                    break;
                case 'modified_time':
                    usort($allFiles, array('WpfdFilter', 'cmpUpdated'));
                    break;
                case 'hits':
                    usort($allFiles, array('WpfdFilter', 'cmpHits'));
                    break;
                case 'size':
                    usort($allFiles, array('WpfdFilter', 'cmpSize'));
                    break;
                case 'ext':
                    usort($allFiles, array('WpfdFilter', 'cmpExt'));
                    break;
                case 'version':
                    usort($allFiles, array('WpfdFilter', 'cmpVersionNumber'));
                    break;
                case 'description':
                    usort($allFiles, array('WpfdFilter', 'cmpDescription'));
                    break;
                case 'ordering':
                    usort($allFiles, array('WpfdFilter', 'cmpTitle'));
                    break;
                default:
                    usort($allFiles, array('WpfdFilter', 'cmpTitle'));
                    break;
            }
            if (strtoupper($orderingdir) === 'DESC') {
                $allFiles = array_reverse($allFiles);
            }
        }

        $modelCategory = Model::getInstance('category');
        if (is_array($categories) && is_countable($categories) && count($categories) === 0) {
            return '';
        }
        $termId = $categories[0]->term_id;
        if (!is_numeric($termId) && isset($categories[0]->wp_term_id)) {
            $termId = $categories[0]->wp_term_id;
        }
        $category = $modelCategory->getCategory($termId);

        // Show categories or not on all categories
        if (isset($shortcode_param['show_categories']) && ((int) $shortcode_param['show_categories'] === 1)) {
            $categories = array_filter($categories, function ($category) {
                if ($category->parent === 0) {
                    return true;
                }
            });
            $categories = array_values($categories);
        } else {
            $categories = array();
        }

        // Global theme parameter
        $modelConfig = Model::getInstance('config');
        $main_config = $modelConfig->getGlobalConfig();
        $defaultTheme = $main_config['defaultthemepercategory'];
        $params = $modelConfig->getConfig($defaultTheme);

        $prefix = '';
        if ($defaultTheme !== 'default') {
            $prefix = $defaultTheme . '_';
        }
        // Disable breadcrumb
        $params[$prefix . 'showbreadcrumb'] = '0';
        // Disable category name
        $params[$prefix . 'showcategorytitle'] = '0';
        // Remove wpfd-categories element
        $params['show_categories'] = '0';
        if (!class_exists('WpfdTheme')) {
            $themeclass = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'templates';
            $themeclass .= DIRECTORY_SEPARATOR . 'wpfd-theme.class.php';
            require_once $themeclass;
        }
        /**
         * Get theme instance follow priority
         *
         * 1. /wp-content/wp-file-download/themes
         * 2. /wp-content/uploads/wpfd-themes
         * 3. /wp-content/plugins/wp-file-download/app/site/themes
         */
        $theme = wpfd_get_theme_instance($defaultTheme);

        // Set theme params, separator it to made sure theme can work well
        if (method_exists($theme, 'setAjaxUrl')) {
            $theme->setAjaxUrl(wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()));
        }

        $global_settings['download_selected'] = 0; // Not allow download categories here
        $global_settings['download_category'] = 0;
        if (method_exists($theme, 'setConfig')) {
            $theme->setConfig($global_settings);
        }

        if (method_exists($theme, 'setPath')) {
            $theme->setPath(Application::getInstance('Wpfd')->getPath());
        }

        if (method_exists($theme, 'setThemeName')) {
            $theme->setThemeName($defaultTheme);
        }

        $files = $allFiles;
        if (isset($param_number) && $param_number) {
            $files = array_slice($files, 0, $param_number);
        }

        $limit = $global_settings['paginationnunber'];
        $total = ceil(count($files) / $limit);

        $page = Utilities::getInput('paged', 'POST', 'string');
        $page = $page !== '' ? $page : 1;

        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        if ($theme->getThemeName() !== 'tree') {
            $files = array_slice($files, $offset, $limit);
        }

        $filesx = array();
        // Crop file titles
        if (is_array($files) && !empty($files)) {
            foreach ($files as $i => $file) {
                if (isset($file->state) && (int) $file->state === 0) {
                    continue;
                }
                $filesx[$i]             = $file;
                $filesx[$i]->crop_title = WpfdBase::cropTitle($params, $theme->getThemeName(), $file->post_title);
            }
            unset($files);
            $files = $filesx;
        }
//        $category->term_id = 'all-' . $category->term_id;
        $category->name = esc_html__('All Categories', 'wpfd');
        $category->slug = sanitize_title($category->name);
        $category->term_id = 'all_0';
        $options = array(
            'files' => $files,
            'category' => $category,
            'categories' => $categories,
            'ordering' => $ordering,
            'orderingDirection' => $orderingdir,
            'params' => $params,
            'tpl' => null,
            'latest' => false // True: Disable show categories
        );
        $pagination = '';

        if (isset($category->params['theme']) && $category->params['theme'] !== 'tree') {
            $pagination = wpfd_category_pagination(
                array('base' => '', 'format' => '', 'current' => max(1, $page), 'total' => $total, 'sourcecat' => 0)
            );
        }
        // We need to disable pagination on content all cat so temporary
        // todo: fix pagination for content all cat
        $content = $theme->showCategory($options) /*. $pagination*/;
        return $content;
    }

    /**
     * Get files all cat
     *
     * @param mixed   $param           Category id
     * @param boolean $shortcode_param Shortcode param
     *
     * @return array|mixed
     */
    private function fileAllCat($param, $shortcode_param = false)
    {
        Application::getInstance('Wpfd');
        $modelConfig = Model::getInstance('config');
        $modelCategory = Model::getInstance('category');
        $global_settings = $modelConfig->getGlobalConfig();
        $category = $modelCategory->getCategory($param);
        $param_number = null;
        if ($shortcode_param && isset($shortcode_param['number']) && !empty($shortcode_param['number']) &&
            (is_numeric($shortcode_param['number']) && (int)$shortcode_param['number'] > 0)
        ) {
            $param_number = $shortcode_param['number'];
        }

        if (empty($category)) {
            return '';
        }
        //$themename = $category->params['theme'];
        $params = $category->params;
        $params['social'] = isset($params['social']) ? $params['social'] : 0;
        if ((int) $category->access === 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            $singleuser = false;

            if (isset($params['canview']) && $params['canview'] === '') {
                $params['canview'] = 0;
            }

            $canview = isset($params['canview']) ? (int) $params['canview'] : 0;

            if ((int) $global_settings['restrictfile'] === 1) {
                $user = wp_get_current_user();
                $user_id = (int) $user->ID;

                if ($user_id) {
                    if ($canview === $user_id || $canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                } else {
                    if ($canview === 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                }
            }

            if ($canview !== 0 && !count($category->roles)) {
                if ($singleuser === false) {
                    return '';
                }
            } elseif ($canview !== 0 && count($category->roles)) {
                if (empty($allows) && !$singleuser) {
                    return '';
                }
            } else {
                if (empty($allows)) {
                    return '';
                }
            }
        }
        Application::getInstance('Wpfd');
        $modelFiles = Model::getInstance('files');
        $modelCategory = Model::getInstance('category');
        $modelTokens = Model::getInstance('tokens');

        $token = $modelTokens->getOrCreateNew();

        $tpl = null;
        $category = $modelCategory->getCategory($param);
        $orderCol = Utilities::getInput('orderCol', 'GET', 'none');
        $ordering = $orderCol !== null ? $orderCol : $category->ordering;
        $orderDir = Utilities::getInput('orderDir', 'GET', 'none');
        $orderingdir = $orderDir !== null ? $orderDir : $category->orderingdir;

        $description = json_decode($category->description, true);
        $lstAllFile = null;
        if ($shortcode_param && isset($shortcode_param['order']) && !empty($shortcode_param['order'])) {
            $ordering = $shortcode_param['order'];
        }
        if ($shortcode_param && isset($shortcode_param['direction']) && !empty($shortcode_param['direction'])) {
            $orderingdir = $shortcode_param['direction'];
        }

        if (!empty($description) && isset($description['refToFile'])) {
            if (isset($description['refToFile'])) {
                $listCatRef = $description['refToFile'];
                $lstAllFile = $this->getAllFileRef($modelFiles, $listCatRef, $ordering, $orderingdir, $param);
            }
        }
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $param);
        if ($categoryFrom === 'googleDrive') {
            $files = apply_filters(
                'wpfdAddonGetListGoogleDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'dropbox') {
            $files = apply_filters(
                'wpfdAddonGetListDropboxFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'onedrive') {
            $files = apply_filters(
                'wpfdAddonGetListOneDriveFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } elseif ($categoryFrom === 'onedrive_business') {
            $files = apply_filters(
                'wpfdAddonGetListOneDriveBusinessFile',
                $param,
                $ordering,
                $orderingdir,
                $category->slug,
                $token
            );
        } else {
            $files = $modelFiles->getFiles($param, $ordering, $orderingdir);

            if (!empty($files) && ((int) $global_settings['restrictfile'] === 1)) {
                $user = wp_get_current_user();
                $user_id = $user->ID;
                foreach ($files as $key => $file) {
                    $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                    $canview = isset($metadata['canview']) ? $metadata['canview'] : 0;
                    $canview = array_map('intval', explode(',', $canview));
                    if ($user_id) {
                        if (!(in_array($user_id, $canview) || in_array(0, $canview))) {
                            unset($files[$key]);
                        }
                    } else {
                        if (!in_array(0, $canview)) {
                            unset($files[$key]);
                        }
                    }
                }
            }
        }

        if ($lstAllFile && !empty($lstAllFile)) {
            $files = array_merge($lstAllFile, $files);
        }

        if ($param_number) {
            $files = array_slice($files, 0, $param_number);
        }

        return $files;
    }


    /**
     * Get all files reference category
     *
     * @param object  $model             File model
     * @param array   $listCatRef        List Categories
     * @param string  $ordering          Ordering
     * @param string  $orderingDirection Ordering direction
     * @param integer $refCatId          Ref cat id
     *
     * @return array
     */
    public function getAllFileRef($model, $listCatRef, $ordering, $orderingDirection, $refCatId = null)
    {
        $lstAllFile = array();
        foreach ($listCatRef as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $lstFile    = $model->getFiles($key, $ordering, $orderingDirection, $value, $refCatId);
                $lstAllFile = array_merge($lstFile, $lstAllFile);
            }
        }
        return $lstAllFile;
    }

    /**
     * Method to compare by property
     *
     * @param object $a        First object
     * @param object $b        Second object
     * @param string $property Property to sort
     * @param string $type     Type
     *
     * @return boolean|integer
     */
    private function compareByProperty($a, $b, $property, $type = 'string')
    {
        switch ($type) {
            case 'datetime':
                $result = (strtotime($a->{$property}) < strtotime($b->{$property})) ? -1 : 1;
                break;
            case 'number':
                $result = ($a->{$property} > $b->{$property});
                break;
            case 'string':
            default:
                $result = strnatcmp($a->{$property}, $b->{$property});
                break;
        }
        return $result;
    }

    /**
     * Method to compare created
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpCreated($a, $b)
    {
        return $this->compareByProperty($a, $b, 'created_time', 'datetime');
    }

    /**
     * Method to compare updated
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpUpdated($a, $b)
    {
        return $this->compareByProperty($a, $b, 'modified_time', 'datetime');
    }

    /**
     * Method to compare hits
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpHits($a, $b)
    {
        return $this->compareByProperty($a, $b, 'hits', 'number');
    }

    /**
     * Method to compare size
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpSize($a, $b)
    {
        return $this->compareByProperty($a, $b, 'size', 'number');
    }

    /**
     * Method to compare ext
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpExt($a, $b)
    {
        return $this->compareByProperty($a, $b, 'ext', 'string');
    }

    /**
     * Method to compare version
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpVersionNumber($a, $b)
    {
        return $this->compareByProperty($a, $b, 'versionNumber', 'string');
    }


    /**
     * Method to compare Description
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpDescription($a, $b)
    {
        return $this->compareByProperty($a, $b, 'description', 'string');
    }

    /**
     * Method to compare title
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return boolean|integer
     */
    private function cmpTitle($a, $b)
    {
        return $this->compareByProperty($a, $b, 'post_title', 'string');
    }

    /**
     * Function acf filter to replace plugin holder place
     *
     * @param string  $value   Value load from database
     * @param integer $post_id Id of current post
     * @param string  $field   Name of current field
     *
     * @return string
     */
    public function wpfdAcfLoadValue($value, $post_id, $field)
    {
        if (is_string($value)) {
            $value = $this->wpfdReplace($value);
        }

        return $value;
    }
}
