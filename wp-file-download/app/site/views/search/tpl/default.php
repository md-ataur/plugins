<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Utilities;

$app = Application::getInstance('Wpfd');

// Set default value for variables use in search template
$result_limit = Utilities::getInput('limit', 'POST', 'string');

if ($result_limit === '') {
    $result_limit = $this->searchConfig['file_per_page'];
}
$variables         = array(
    'files'      => isset($this->files) ? $this->files : null,
    'ordering'   => isset($this->ordering) ? $this->ordering : 'type',
    'dir'        => isset($this->dir) ? $this->dir : 'asc',
    'args'       => isset($this->searchConfig) ? $this->searchConfig : array(),
    'config'     => isset($this->config) ? $this->config : null,
    'categories' => isset($this->categories) ? $this->categories : array(),
    'filters'    => isset($this->filters) ? $this->filters : array(),
    'viewer'     => WpfdBase::loadValue($this->config, 'use_google_viewer', 'no'),
    'limit'      => $result_limit,
    'baseurl'    => $app->getBaseUrl()
);

if ($variables['viewer'] === 'lightbox') {
    wp_enqueue_script('wpfd-colorbox', plugins_url('app/site/assets/js/colorbox.init.js', WPFD_PLUGIN_FILE), array('jquery'), WPFD_VERSION, true);
}
// Include search template
wpfd_get_template('tpl-search-results.php', $variables);
