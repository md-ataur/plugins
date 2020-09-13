<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdViewSearch
 */
class WpfdViewSearch extends View
{

    /**
     * Display front search
     *
     * @param string|null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        $filters = array();
        $q       = Utilities::getInput('q', 'POST', 'string');
        $q       = preg_replace('/[-_]/', ' ', $q);
        if (!empty($q)) {
            $filters['q'] = $q;
        }
        $catid = Utilities::getInput('catid', 'POST', 'string');
        if (!empty($catid)) {
            $filters['catid'] = $catid;
        }

        $exclude = Utilities::getInput('exclude', 'POST', 'string');
        if (!empty($exclude)) {
            $filters['exclude'] = $exclude;
        }
        $ftags = Utilities::getInput('ftags', 'POST', 'none');
        if (is_array($ftags)) {
            $ftags = array_unique($ftags);
            $ftags = implode(',', $ftags);
        } else {
            $ftags = Utilities::getInput('ftags', 'POST', 'string');
        }

        if (!empty($ftags)) {
            $filters['ftags'] = $ftags;
        }
        $cfrom = Utilities::getInput('cfrom', 'POST', 'string');
        if (!empty($cfrom)) {
            $filters['cfrom'] = $cfrom;
        }
        $cto = Utilities::getInput('cto', 'POST', 'string');
        if (!empty($cto)) {
            $filters['cto'] = $cto;
        }
        $ufrom = Utilities::getInput('ufrom', 'POST', 'string');
        if (!empty($ufrom)) {
            $filters['ufrom'] = $ufrom;
        }
        $uto = Utilities::getInput('uto', 'POST', 'string');
        if (!empty($uto)) {
            $filters['uto'] = $uto;
        }
        $doSearch = false;
        if (!empty($filters)) {
            $doSearch = true;
        }
        $this->ordering    = Utilities::getInput('ordering', 'POST', 'string');
        $this->dir         = Utilities::getInput('dir', 'POST', 'string');
        $this->filters     = $filters;
        $modelCategories   = $this->getModel('categories');
        $model             = $this->getModel('search');
        $modelConfig       = $this->getModel('config');
        $this->categories = $modelCategories->getLevelCategories();

        $this->files       = $model->searchfile($filters, $doSearch);
        $this->config      = $modelConfig->getGlobalConfig();
        $this->searchConfig = $modelConfig->getSearchConfig();
        parent::render($tpl);
        wp_die();
    }
}
