<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */


if (!function_exists('wpfd_sort_by_property')) {
    /**
     * Sort items by property
     *
     * @param array   $items    Files list
     * @param string  $property Property type
     * @param string  $key      Sort type
     * @param boolean $reverse  Reverse type
     *
     * @return array
     */
    function wpfd_sort_by_property(array $items, $property, $key = '', $reverse = false)
    {
        $sorted = array();
        $items_bk = $items;
        foreach ($items as $item) {
            $sorted[$item->$key] = $item->$property;
            $items_bk[$item->$key] = $item;
        }
        if ($reverse) {
            arsort($sorted);
        } else {
            asort($sorted);
        }
        $results = array();
        foreach ($sorted as $key2 => $value) {
            $results[] = $items_bk[$key2];
        }
        return $results;
    }
}

if (!function_exists('wpfd_getext')) {
    /**
     * Get extension of file
     *
     * @param string $file File name
     *
     * @return boolean|string
     */
    function wpfd_getext($file)
    {
        $dot = strrpos($file, '.') + 1;
        return substr($file, $dot);
    }
}
if (!function_exists('wpfd_remote_file_size')) {
    /**
     * Get size of file with remote url
     *
     * @param string $url Input url
     *
     * @return mixed|string
     */
    function wpfd_remote_file_size($url)
    {
        // Fix file size error on url have space
        $url = str_replace(' ', '%20', $url);
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_NOBODY => 1,
        ));
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $clen = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        if (!$clen || ($clen === -1)) {
            return 'n/a';
        }
        return $clen;
    }
}

if (!function_exists('wpfd_num')) {
    /**
     * Display select pages number
     *
     * @param integer $paged Page number
     *
     * @return void
     */
    function wpfd_num($paged = 5)
    {
        ?>
        <div class="wpfd-num">
            <?php
            $p_number = array(5, 10, 15, 20, 25, 30, 50, 100, -1);
            ?>
            <div class="limit pull-right">
                <?php esc_html_e('Display #', 'wpfd'); // phpcs:ignore ?>
                <select title="" id="limit" name="limit" class="" size="1">
                    <?php
                    foreach ($p_number as $num) {
                        $selected = $num === (int)$paged ? ' selected="selected"' : '';
                        ?>
                        <option value="<?php echo $num; // phpcs:ignore ?>"
                            <?php echo $selected; // phpcs:ignore ?>><?php echo $num === -1 ? esc_html__('All', 'wpfd') : $num; ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('wpfd_select')) {
    /**
     * Render a select html
     *
     * @param array   $options  Options array
     * @param string  $name     Name
     * @param string  $select   Select
     * @param string  $attr     Attr
     * @param boolean $disabled Disable
     *
     * @return string
     */
    function wpfd_select(array $options = array(), $name = '', $select = '', $attr = '', $disabled = false)
    {
        $html = '';
        $html .= '<select';
        if ($name !== '') {
            $html .= ' name="' . esc_attr($name) . '"';
        }
        if ($attr !== '') {
            $html .= ' ' . $attr;
        }
        $html .= '>';
        if (!empty($options)) {
            foreach ($options as $key => $value) {
                $select_option = '';
                if (is_array($select)) {
                    if (in_array($key, $select)) {
                        $select_option = 'selected="selected"';
                    } elseif ((string)$key === (string)$disabled) {
                        $select_option = disabled($disabled, $key, false);
                    } else {
                        $select_option = '';
                    }
                } else {
                    if ($disabled) {
                        $select_option = disabled($disabled, $key, false);
                    } else {
                        $select_option = selected($select, $key, false);
                    }
                }
                $html .= '<option value="' . esc_attr($key) . '" ' . $select_option . '>' . $value . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}
if (!function_exists('wpfd_pagination')) {
    /**
     * Display a custom pagination
     *
     * @param array  $args      Options args
     * @param string $form_name Form name
     *
     * @return array|string|boolean
     */
    function wpfd_pagination(array $args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;
        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);
        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';
        // URL base depends on permalink settings.
        $pagination_base = user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged');
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? $pagination_base : '?paged=%#%';

        $defaults = array(
            'base' => $pagenum_link,
            // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format,
            // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => esc_html__('&laquo; Previous', 'wpfd'),
            'next_text' => esc_html__('Next &raquo;', 'wpfd'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(),
            // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );

        $args = wp_parse_args($args, $defaults);
        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }
        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);
            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);
            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }
            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }
        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return false;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;
        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 === $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];
            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * param string $link The paginated link URL.
             */
            $page_link = "<a class='prev page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
            $page_link .= ($current - 1) . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['prev_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n === $current) :
                $page_link = "<span class='page-numbers current'>" . $args['before_page_number'];
                $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</span>';
                $page_links[] = $page_link;
                $dots = true;
            else :
                if ($args['show_all'] ||
                    ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size)
                        || $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 === $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args) {
                        $link = add_query_arg($add_args, $link);
                    }
                    $link .= $args['add_fragment'];
                    /**
 * This filter is documented in wp-includes/general-template.php
*/
                    $page_link = "<a class='page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
                    $page_link .= $n . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['before_page_number'];
                    $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</a>';
                    $page_links[] = $page_link;
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . esc_html__('&hellip;', 'wpfd') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 === $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
 * This filter is documented in wp-includes/general-template.php
*/
            $page_link = "<a class='next page-numbers' onclick='document." . esc_attr($form_name) . '.paged.value=';
            $page_link .= ($current + 1) . '; document.' . esc_attr($form_name) . ".submit();'>" . $args['next_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        switch ($args['type']) {
            case 'array':
                return $page_links;
            case 'list':
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;
            default:
                $r = join("\n", $page_links);
                break;
        }
        return $r;
    }
}

if (!function_exists('wpfd_category_pagination')) {
    /**
     * Display a custom pagination
     *
     * @param array  $args      Option args
     * @param string $form_name Form name
     *
     * @return array|string|boolean
     */
    function wpfd_category_pagination(array $args = array(), $form_name = '')
    {
        global $wp_query, $wp_rewrite;
        // Setting up default values based on the current URL.
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link);
        // Get max pages and current page out of the current query, if available.
        $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
        $current = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
        // Append the format placeholder to the base URL.
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';
        // URL base depends on permalink settings.
        $pagination_base = user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged');
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? $pagination_base : '?paged=%#%';
        $defaults = array(
            'base' => $pagenum_link,
            // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => $format,
            // ?page=%#% : %#% is replaced by the page number
            'total' => $total,
            'current' => $current,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => esc_html__('&laquo; Previous', 'wpfd'),
            'next_text' => esc_html__('Next &raquo;', 'wpfd'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => array(),
            // array of query args to add
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );
        $args = wp_parse_args($args, $defaults);
        if (!is_array($args['add_args'])) {
            $args['add_args'] = array();
        }
        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format = explode('?', str_replace('%_%', $args['format'], $args['base']));
            $format_query = isset($format[1]) ? $format[1] : '';
            wp_parse_str($format_query, $format_args);
            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);
            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }
            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }
        // Who knows what else people pass in $args
        $total = (int)$args['total'];
        if ($total < 2) {
            return false;
        }
        $current = (int)$args['current'];
        $end_size = (int)$args['end_size']; // Out of bounds?  Make it the default.
        if ($end_size < 1) {
            $end_size = 1;
        }
        $mid_size = (int)$args['mid_size'];
        if ($mid_size < 0) {
            $mid_size = 2;
        }
        $add_args = $args['add_args'];
        $r = '';
        $page_links = array();
        $dots = false;

        if ($args['prev_next'] && $current && 1 < $current) :
            $link = str_replace('%_%', 2 === $current ? '' : $args['format'], $args['base']);
            $link = str_replace('%#%', $current - 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
             * Filter the paginated links for the given archive pages.
             *
             * @since 3.0.0
             *
             * param string $link The paginated link URL.
             */
            $page_link = "<a class='prev page-numbers' data-page='" . ($current - 1) . "' data-sourcecat='" . $args['sourcecat'] . "'>";
            $page_link .= $args['prev_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        for ($n = 1; $n <= $total; $n++) :
            if ($n === $current) :
                $page_link = "<span class='page-numbers current'>" . $args['before_page_number'];
                $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</span>';
                $page_links[] = $page_link;
                $dots = true;
            else :
                if ($args['show_all'] ||
                    ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) ||
                        $n > $total - $end_size)) :
                    $link = str_replace('%_%', 1 === $n ? '' : $args['format'], $args['base']);
                    $link = str_replace('%#%', $n, $link);
                    if ($add_args) {
                        $link = add_query_arg($add_args, $link);
                    }
                    $link .= $args['add_fragment'];

                    /**
                     * This filter is documented in wp-includes/general-template.php
                    */
                    $page_link = "<a class='page-numbers' data-page='" . $n . "' data-sourcecat='" . $args['sourcecat'] . "'>" . $args['before_page_number'];
                    $page_link .= number_format_i18n($n) . $args['after_page_number'] . '</a>';
                    $page_links[] = $page_link;
                    $dots = true;
                elseif ($dots && !$args['show_all']) :
                    $page_links[] = '<span class="page-numbers dots">' . esc_html__('&hellip;', 'wpfd') . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;
        if ($args['prev_next'] && $current && ($current < $total || -1 === $total)) :
            $link = str_replace('%_%', $args['format'], $args['base']);
            $link = str_replace('%#%', $current + 1, $link);
            if ($add_args) {
                $link = add_query_arg($add_args, $link);
            }
            $link .= $args['add_fragment'];

            /**
             * This filter is documented in wp-includes/general-template.php
            */
            $page_link = "<a class='next page-numbers' data-page='" . ($current + 1) . "' data-sourcecat='" . $args['sourcecat'] . "'>";
            $page_link .= $args['next_text'] . '</a>';
            $page_links[] = $page_link;
        endif;
        switch ($args['type']) {
            case 'array':
                return $page_links;
            case 'list':
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;
            default:
                $r .= "<div class='wpfd-pagination'>\n\t";
                $r .= join("\n", $page_links);
                $r .= "\n</div>\n";
                break;
        }
        return $r;
    }
}

if (!function_exists('wpfd_esc_desc')) {
    /**
     * Escaping for HTML description blocks.
     *
     * @param string $text Text
     *
     * @return string
     */
    function wpfd_esc_desc($text)
    {
        $safe_text = wp_check_invalid_utf8($text);
        // Remove <script>
        $safe_text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $safe_text);
        return apply_filters('wpfd_esc_desc', $safe_text, $text);
    }
}

if (PHP_VERSION_ID < 70300) {
    if (!function_exists('is_countable')) {
        /**
         * Check countable variables from php version 7.3.0
         *
         * @param mixed $var Variable to check
         *
         * @return boolean
         */
        function is_countable($var)
        {
            return is_array($var) || $var instanceof Countable || $var instanceof ResourceBundle || $var instanceof SimpleXmlElement;
        }
    }
}

if (!function_exists('wpfd_sanitize_ajax_url')) {
    /**
     * Sanitize wp file download ajax url
     *
     * @param string $url Ajax url
     *
     * @return string
     */
    function wpfd_sanitize_ajax_url($url)
    {
        if (preg_match('/Wpfd/i', $url)) {
            $url = str_replace('action=Wpfd', 'action=wpfd', $url);
        }

        return apply_filters('wpfd_sanitize_ajax_url', $url);
    }
}
if (!function_exists('wpfd_locate_template')) {
    /**
     * Locate a template and return the path for inclusion.
     *
     * Loader order:
     *
     * wp-content/$content_path/$template_name
     * $default_path/app/site/themes/$template_name
     *
     * @param string $template_name Template name
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return string
     */
    function wpfd_locate_template($template_name, $content_path = '', $default_path = '')
    {

        if (!$content_path) {
            $content_path = WP_CONTENT_DIR . '/wp-file-download/templates/';
        }

        if (!$default_path) {
            $default_path = plugin_dir_path(WPFD_PLUGIN_FILE) . '/app/site/themes/templates/';
        }

        // Look into wp-content directory for template file - this is priority.
        $template = file_exists(trailingslashit($content_path) . $template_name) ? trailingslashit($content_path) . $template_name : '';

        // Get default template.
        if (!$template) {
            $template = trailingslashit($default_path) . $template_name;
        }

        /**
         * Filter on return found template path
         *
         * @param string Template path
         * @param string Template name
         * @param string Template path dir
         */
        return apply_filters('wpfd_locate_template', $template, $template_name, $content_path);
    }
}
if (!function_exists('wpfd_get_template')) {
    /**
     * Get templates
     *
     * @param string $template_name Template name.
     * @param array  $args          Template arguments
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return void
     */
    function wpfd_get_template($template_name, $args = array(), $content_path = '', $default_path = '')
    {
        if (!empty($args) && is_array($args)) {
            // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- use extract is ok
            extract($args);
        }

        $located = wpfd_locate_template($template_name, $content_path, $default_path);

        if (!file_exists($located)) {
            return;
        }

        /**
         * Allow 3rd party plugin filter
         *
         * @param string Template path
         * @param string Template name
         * @param array  Template variables
         * @param string Template path dir
         * @param string Default path dir
         *
         * @return string
         */
        $located = apply_filters('wpfd_get_template', $located, $template_name, $args, $content_path, $default_path);

        /**
         * Action fire before a template part called
         *
         * @param string Template name
         * @param string Template path dir
         * @param string Template path
         * @param array  Template variables
         */
        do_action('wpfd_before_template_part', $template_name, $content_path, $located, $args);

        include $located;

        /**
         * Action fire after a template part called
         *
         * @param string Template name
         * @param string Template path dir
         * @param string Template path
         * @param array  Template variables
         */
        do_action('wpfd_after_template_part', $template_name, $content_path, $located, $args);
    }
}

if (!function_exists('wpfd_get_template_html')) {
    /**
     * Get templates and return html
     *
     * @param string $template_name Template name
     * @param array  $args          Template arguments
     * @param string $content_path  Template path dir
     * @param string $default_path  Default path dir
     *
     * @return false|string
     */
    function wpfd_get_template_html($template_name, $args = array(), $content_path = '', $default_path = '')
    {
        ob_start();
        wpfd_get_template($template_name, $args, $content_path, $default_path);
        return ob_get_clean();
    }
}
/**
 * Locate theme file path
 *
 * @param string $theme        Theme name
 * @param string $file         Theme file to locate
 * @param string $content_path Template path dir
 * @param string $default_path Template default path dir
 * @param string $upload_path  Template old path dir
 *
 * @return mixed
 */
function wpfd_locate_theme($theme, $file, $content_path = '', $default_path = '', $upload_path = '')
{
    $ds = DIRECTORY_SEPARATOR;
    if ($content_path === '') {
        $content_path = WP_CONTENT_DIR . $ds .'wp-file-download' . $ds . 'themes' . $ds . 'wpfd-' . $theme . $ds;
    }
    if (!$upload_path) {
        $dir = wp_upload_dir();
        $upload_path = $dir['basedir'] . $ds . 'wpfd-themes' . $ds . 'wpfd-' . $theme . $ds;
    }
    if (!$default_path) {
        $default_path = plugin_dir_path(WPFD_PLUGIN_FILE) . $ds . 'app' . $ds . 'site' . $ds . 'themes' . $ds . 'wpfd-' . $theme . $ds;
    }

    if (file_exists(trailingslashit($content_path) . $file)) {
        $template = trailingslashit($content_path) . $file;
    } elseif (file_exists(trailingslashit($upload_path) . $file)) {
        $template = trailingslashit($upload_path) . $file;
    }

    // Get default template.
    if (!isset($template)) {
        $template = trailingslashit($default_path) . $file;
    }

    /**
     * Filter on return found template path
     *
     * @param string Template path
     * @param string Template name
     * @param string Template path dir
     */
    return apply_filters('wpfd_locate_theme', $template, $theme, $file, $content_path);
}

/**
 * Get theme instance by name
 *
 * @param string $theme        Theme name
 * @param string $content_path Template path dir
 * @param string $default_path Template default path dir
 * @param string $upload_path  Template old path dir
 *
 * @return WpfdTheme{NAME}
 */
function wpfd_get_theme_instance($theme, $content_path = '', $default_path = '', $upload_path = '')
{
    $file = 'theme.php';

    if (!class_exists('wpfdTheme')) {
        $wpfdTheme = plugin_dir_path(WPFD_PLUGIN_FILE) . '/app/site/themes/templates/wpfd-theme.class.php';
        include_once $wpfdTheme;
    }

    $located = wpfd_locate_theme($theme, $file, $content_path, $default_path, $upload_path);

    if (file_exists($located)) {
        include_once $located;
    } else {
        $themefile = plugin_dir_path(WPFD_PLUGIN_FILE) . '/app/site/themes/wpfd-default/theme.php';
        include_once $themefile;
        $theme = 'default';
    }
    $class = 'WpfdTheme' . ucfirst(str_replace('_', '', $theme));

    if (class_exists($class)) {
        $instance = new $class();
    } else {
        $instance = new WpfdThemeDefault;
    }

    return $instance;
}

/**
 * Get supported cloud platform
 *
 * @return array
 */
function wpfd_get_support_cloud()
{
    /**
     * Filter return supported cloud platform
     * Require to detect where categories/files from
     *
     * @param array Cloud platform list
     *
     * @return array
     */
    return apply_filters('wpfd_get_support_cloud', array('googleDrive', 'dropbox', 'onedrive', 'onedrive_business'));
}

/**
 * Get file url from real path
 *
 * @param string $path Real path
 *
 * @return string
 */
function wpfd_abs_path_to_url($path = '')
{
    $url = str_replace(
        wp_normalize_path(untrailingslashit(ABSPATH)),
        site_url(),
        wp_normalize_path($path)
    );

    return esc_url_raw($url);
}
/**
 * Check capability of current user to manage file
 *
 * @return boolean
 */
function wpfd_can_manage_file()
{
    /**
     * Filter check capability of current user to manage file
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_manage_file'), 'manage_file');
}

/**
 * Check capability of current user to edit category
 *
 * @return boolean
 */
function wpfd_can_edit_category()
{
    /**
     * Filter check capability of current user to edit category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_edit_category'), 'edit_category');
}

/**
 * Check capability of current user to edit own category
 *
 * @return boolean
 */
function wpfd_can_edit_own_category()
{
    /**
     * Filter check capability of current user to edit own category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_edit_own_category'), 'edit_own_category');
}

/**
 * Check capability of current user to delete category
 *
 * @return boolean
 */
function wpfd_can_delete_category()
{
    /**
     * Filter check capability of current user to delete category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_delete_category'), 'delete_category');
}

/**
 * Check capability of current user to create category
 *
 * @return boolean
 */
function wpfd_can_create_category()
{
    /**
     * Filter check capability of current user to create category
     *
     * @param boolean The current user has the given capability
     * @param string  Action name
     *
     * @return boolean
     *
     * @ignore Hook already documented
     */
    return apply_filters('wpfd_user_can', current_user_can('wpfd_create_category'), 'create_category');
}

if (!function_exists('wpfd_gutenberg_integration')) {
    /**
     * WP File Download gutenberg integration
     *
     * @return void
     */
    function wpfd_gutenberg_integration()
    {
        wp_enqueue_script(
            'wpfd-blocks',
            WPFD_PLUGIN_URL . 'app/admin/assets/blocks/wpfd-blocks.js',
            array('wp-blocks', 'wp-element', 'wp-components', 'wp-data')
        );
        wp_enqueue_style(
            'wpfd-category-style',
            WPFD_PLUGIN_URL . 'app/admin/assets/css/wpfd-blocks.css',
            array('wp-edit-blocks')
        );
    }
}

/**
 * Add WP File Download categories to blocks categories
 *
 * @param array   $categories Categories array
 * @param WP_Post $post       Post object
 *
 * @return array
 */
function wpfd_blocks_categories($categories, $post)
{
    // Display wpfd blocks in all post type
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'wp-file-download',
                'title' => __('WP File Download', 'wpfd'),
            ),
        )
    );
}

/**
 * Install clean statistics job
 *
 * @param string $task     Task name
 * @param string $interval Interval name
 *
 * @return void
 */
function wpfd_install_job($task, $interval)
{
    if (empty($interval) || empty($task)) {
        return;
    }

    if (!wp_next_scheduled($task)) {
        wp_schedule_event(time(), $interval, $task);
    }
}

/**
 * Destroy clean statistics job
 *
 * @param string $task Task name
 *
 * @return void
 */
function wpfd_destroy_job($task)
{
    if (empty($task)) {
        return;
    }

    $timestamp = wp_next_scheduled($task);
    wp_unschedule_event($timestamp, $task);
}

/**
 * Reinstall clean statistics job
 *
 * @param string $task     Task name
 * @param string $interval Interval name
 *
 * @return void
 */
function wpfd_reinstall_job($task, $interval)
{
    if (empty($interval) || empty($task)) {
        return;
    }

    wpfd_destroy_job($task);
    wpfd_install_job($task, $interval);
}

/**
 * Schedules
 *
 * @return void
 */
function wpfd_schedules()
{
    add_filter('cron_schedules', 'wpfd_get_schedules');
}

/**
 * Get all wpfd schedules
 *
 * @param array $schedules Schedules list
 *
 * @return array
 */
function wpfd_get_schedules($schedules)
{
    /**
     * Filter to add wpfd schedules task
     *
     * @param array $schedule An array for schedule task
     *
     * @internal
     *
     * @return array
     */
    $schedule = apply_filters('wpfd_get_schedules', array());

    if (!is_array($schedule)) {
        return $schedules;
    }

    $schedules = array_merge($schedules, $schedule);

    return $schedules;
}

/**
 * Get wpfd_remove_statistics interval
 *
 * @return integer
 */
function wpfd_get_remove_statistics_interval()
{
    $interval = 0;

    $config = get_option('_wpfd_global_config');

    if (!is_array($config) && !isset($config['statistics_storage_duration'])) {
        return $interval;
    }

    $duration = isset($config['statistics_storage_duration']) ? $config['statistics_storage_duration'] : 'forever';
    $times = isset($config['statistics_storage_times']) ? (int) $config['statistics_storage_times'] : 0;

    // Calculate interval
    if ($times !== 0) {
        switch ($duration) {
            case 'years':
                $interval = $times * 31104000;
                break;
            case 'months':
                $interval = $times * 2592000;
                break;
            case 'days':
                $interval = $times * 86400;
                break;
            case 'forever':
            default:
                break;
        }
    }

    return (int) $interval;
}

/**
 * Get wpfd_remove_statistics schedule
 *
 * @param array $schedule Schedule list
 *
 * @return array|boolean
 */
function wpfd_get_remove_statistics_schedule($schedule)
{
    $interval = wpfd_get_remove_statistics_interval();

    if ($interval === 0) {
        return false;
    }

    $schedule['wpfd_remove_statistics'] = array(
        'interval' => $interval,
        'display'  => esc_html__('WPFD Clean Statistics', 'wpfd'),
    );

    return $schedule;
}

/**
 * Remove statistics
 *
 * @return void
 */
function wpfd_remove_statistics()
{
    global $wpdb;

    // Check last time statistics is run to prevent it clear in first time.
    $lastCleanTime = get_option('wpfd_remove_statistics_time', 0);
    $interval = wpfd_get_remove_statistics_interval();

    // Do not clear in first running and update
    if ($lastCleanTime === 0 || ($lastCleanTime > 0 && (time() - $lastCleanTime) < $interval)) {
        if ($lastCleanTime === 0) {
            update_option('wpfd_remove_statistics_time', time());
        }
        return;
    }

    $ts = $lastCleanTime + $interval;
    $deletePoint = date('Y-m-d', $ts);
    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE `date` < "' . $deletePoint . '"');

    update_option('wpfd_remove_statistics_time', time());
}

/**
 * Reinstall remove statistics task
 *
 * @return void
 */
function wpfd_after_main_setting_save()
{
    wpfd_reinstall_job('wpfd_remove_statistics_tasks', 'wpfd_remove_statistics');
}

/**
 * Clean expiry tokens
 *
 * @return void
 */
function wpfd_remove_expiry_tokens()
{
    global $wpdb;

    /**
     * Filter to change token live time
     *
     * @param int Token live time in seconds
     *
     * @return int
     *
     * @ignore
     */
    $time = time() - apply_filters('wpfd_token_live_time', 3600);

    $wpdb->query(
        $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'wpfd_tokens WHERE created_at < %d', $time)
    );
}

/**
 * Get clean junks schedule
 *
 * @param array $schedule Schedule list
 *
 * @return array
 */
function wpfd_get_clean_junks_schedule($schedule)
{
    $interval = (defined('WPFD_CLEAN_INTERVAL') && WPFD_CLEAN_INTERVAL > 0) ? WPFD_CLEAN_INTERVAL : 43200; // 12 hours
    /**
     * Filter to change clean junks time, this will override WPFD_CLEAN_INTERVAL constance
     *
     * @param $interval Time in second
     */
    $interval = apply_filters('wpfd_clean_interval', $interval);
    $schedule['wpfd_clean_junks'] = array(
        'interval' => $interval,
        'display'  => esc_html__('WPFD Clean Junks', 'wpfd'),
    );
    return $schedule;
}

/**
 * Clean junks
 *
 * @return void
 */
function wpfd_clean_junks()
{
    $wp_upload_dir = wp_upload_dir();
    $dr = DIRECTORY_SEPARATOR;
    $uploadDir     = $wp_upload_dir['basedir'] . $dr . 'wpfd';
    $interval = (defined('WPFD_CLEAN_INTERVAL') && WPFD_CLEAN_INTERVAL > 0) ? WPFD_CLEAN_INTERVAL : 43200; // 12 hours

    // Check if upload dir exists. This folder was not created until a file uploaded
    if (file_exists($uploadDir)) {
        // Clean file upload junks
        $categories = glob($uploadDir . $dr . '*', GLOB_ONLYDIR);
        if (is_array($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $junkFolder = glob($category . $dr . '*', GLOB_ONLYDIR);
                // Check how old is this dir with WPFD_CLEAN_INTERVAL
                foreach ($junkFolder as $folderPath) {
                    $createdTime = filectime($folderPath);
                    if (!$createdTime || ($createdTime + $interval) > time()) {
                        continue;
                    }

                    rrmdir($folderPath);
                }
            }
        }

        // Clean category zip junks
        $objects = scandir($uploadDir);
        if (false !== $objects) {
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $filePath = $uploadDir . $dr . $object;
                    if (filetype($filePath) === 'file' && strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip') {
                        // Check how old is this file with WPFD_CLEAN_INTERVAL
                        $createdTime = filectime($filePath);
                        if (!$createdTime || ($createdTime + $interval) > time()) {
                            continue;
                        }

                        unlink($filePath);
                    }
                }
            }
        }
    }

    // Clean expiry tokens
    wpfd_remove_expiry_tokens();
}

if (!function_exists('rrmdir')) {
    /**
     * Remove a dir
     *
     * @param string $dir Directory path
     *
     * @return void
     */
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
/**
 * Convert php date format to moment.js date format
 *
 * @param string $format Php date format
 *
 * @return string
 */
function wpfdPhpToMomentDateFormat($format)
{
    $replacements = array(
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // no equivalent
        'L' => '', // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // deprecated since version 1.6.0 of moment.js
        'I' => '', // no equivalent
        'O' => '', // no equivalent
        'P' => '', // no equivalent
        'T' => '', // no equivalent
        'Z' => '', // no equivalent
        'c' => '', // no equivalent
        'r' => '', // no equivalent
        'U' => 'X',
    );
    $momentFormat = strtr($format, $replacements);

    return $momentFormat;
}


/**
 * Enqueue assets
 *
 * @return void
 */
function wpfd_enqueue_assets()
{
    wp_enqueue_style('jquery-ui-1.9.2');
    wp_enqueue_style('dashicons');
    wp_enqueue_script('jquery-ui-1.11.4');
    wp_enqueue_script('wpfd-colorbox');
    wp_enqueue_script('wpfd-colorbox-init');
    wp_enqueue_script('wpfd-videojs');
    wp_enqueue_style('wpfd-videojs');
    wp_enqueue_style('wpfd-colorbox');
    wp_enqueue_style('wpfd-viewer');
}

/**
 * Search assets
 *
 * @return void
 */
function wpfd_register_assets()
{
    wp_enqueue_script('jquery');
    wp_enqueue_style('dashicons');
    wp_register_style(
        'jquery-ui-1.9.2',
        plugins_url('app/admin/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css', WPFD_PLUGIN_FILE)
    );

    wp_register_script(
        'jquery-ui-1.11.4',
        plugins_url('app/admin/assets/js/jquery-ui-1.11.4.custom.min.js', WPFD_PLUGIN_FILE)
    );
    wp_register_script('wpfd-colorbox', plugins_url('app/site/assets/js/jquery.colorbox-min.js', WPFD_PLUGIN_FILE));
    wp_register_script(
        'wpfd-colorbox-init',
        plugins_url('app/site/assets/js/colorbox.init.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_localize_script(
        'wpfd-colorbox-init',
        'wpfdcolorboxvars',
        array(
            'preview_loading_message' => sprintf(esc_html__('The preview is still loading, you can %s it at any time...', 'wpfd'), '<span class="wpfd-loading-close">' . esc_html__('cancel', 'wpfd') . '</span>'),
        )
    );
    wp_register_script(
        'wpfd-videojs',
        plugins_url('app/site/assets/js/video.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_localize_script(
        'wpfd-colorbox',
        'wpfdcolorbox',
        array('wpfdajaxurl' => wpfd_sanitize_ajax_url(\Joomunited\WPFramework\v1_0_5\Application::getInstance('Wpfd')->getAjaxUrl()))
    );

    wp_register_style(
        'wpfd-videojs',
        plugins_url('app/site/assets/css/video-js.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_register_style(
        'wpfd-colorbox',
        plugins_url('app/site/assets/css/colorbox.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_register_style(
        'wpfd-viewer',
        plugins_url('app/site/assets/css/viewer.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
}


/**
 * Search access
 *
 * @return void
 */
function wpfd_assets_search()
{
    wp_enqueue_style('wpfd-jquery-tagit', plugins_url('app/admin/assets/css/jquery.tagit.css', WPFD_PLUGIN_FILE));

    wp_enqueue_style(
        'wpfd-daterangepicker-style',
        plugins_url('app/admin/assets/ui/css/daterangepicker.css', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_enqueue_script('wpfd-jquery-tagit', plugins_url('app/admin/assets/js/jquery.tagit.js', WPFD_PLUGIN_FILE));
    wp_enqueue_script(
        'wpfd-moment',
        plugins_url('app/admin/assets/ui/js/moment.min.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_enqueue_script(
        'wpfd-daterangepicker',
        plugins_url('app/admin/assets/ui/js/daterangepicker.min.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    wp_enqueue_script(
        'wpfd-search_filter',
        plugins_url('app/site/assets/js/search_filter.js', WPFD_PLUGIN_FILE),
        array(),
        WPFD_VERSION
    );
    \Joomunited\WPFramework\v1_0_5\Application::getInstance('Wpfd', WPFD_PLUGIN_FILE, 'site');
    $modelConfig = \Joomunited\WPFramework\v1_0_5\Model::getInstance('config');
    $globalConfig = $modelConfig->getGlobalConfig();
    $searchconfig = $modelConfig->getSearchConfig();
    $locale = substr(get_locale(), 0, 2);

    // Add translable for search form daterangepicker
    wp_add_inline_script('wpfd-search_filter', 'var wpfdLocaleSettings = {
            "format": "' . wpfdPhpToMomentDateFormat($globalConfig['date_format']) . '",
            "separator": " - ",
            "applyLabel": "' . esc_html__('Apply', 'wpfd') . '",
            "cancelLabel": "' . esc_html__('Cancel', 'wpfd') . '",
            "fromLabel": "' . esc_html__('From', 'wpfd') . '",
            "toLabel": "' . esc_html__('To', 'wpfd') . '",
            "customRangeLabel": "' . esc_html__('Custom', 'wpfd') . '",
            "weekLabel": "' . esc_html__('W', 'wpfd') . '",
            "daysOfWeek": [
                "' . esc_html__('Su', 'wpfd') . '",
                "' . esc_html__('Mo', 'wpfd') . '",
                "' . esc_html__('Tu', 'wpfd') . '",
                "' . esc_html__('We', 'wpfd') . '",
                "' . esc_html__('Th', 'wpfd') . '",
                "' . esc_html__('Fr', 'wpfd') . '",
                "' . esc_html__('Sa', 'wpfd') . '",
            ],
            "monthNames": [
                "' . esc_html__('January', 'wpfd') . '",
                "' . esc_html__('February', 'wpfd') . '",
                "' . esc_html__('March', 'wpfd') . '",
                "' . esc_html__('April', 'wpfd') . '",
                "' . esc_html__('May', 'wpfd') . '",
                "' . esc_html__('June', 'wpfd') . '",
                "' . esc_html__('July', 'wpfd') . '",
                "' . esc_html__('August', 'wpfd') . '",
                "' . esc_html__('September', 'wpfd') . '",
                "' . esc_html__('October', 'wpfd') . '",
                "' . esc_html__('November', 'wpfd') . '",
                "' . esc_html__('December', 'wpfd') . '",
            ],
            "firstDay": 1,
        }', 'before');

    wp_localize_script(
        'wpfd-search_filter',
        'wpfdvars',
        array(
            'basejUrl' => home_url('?page_id=' . $searchconfig['search_page']),
            'dateFormat' => wpfdPhpToMomentDateFormat($globalConfig['date_format']),
            'locale' => $locale,
            'msg_search_box_placeholder' => esc_html__('Input tags here...', 'wpfd'),
            'msg_file_category' => esc_html__('FILES CATEGORY', 'wpfd'),
            'msg_filter_by_tags' => esc_html__('Filter by Tags', 'wpfd'),
            'msg_no_tag_in_this_category_found' => esc_html__('No tags in this category found!', 'wpfd'),
        )
    );
}