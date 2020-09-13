<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdViewCategories
 */
class WpfdViewCategories extends View
{
    /**
     * Display categories
     *
     * @param string $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        /* @var WpfdModelCategories $modelCats */
        $modelCats         = $this->getModel('categories');
        /* @var WpfdModelCategory $modelCat */
        $modelCat          = $this->getModel('category');
        $categoryId = Utilities::getInput('id', 'GET', 'string');
        $content           = new stdClass();
        $content->category = new stdClass();
        if ($categoryId === 'all_0') {
            $categories = $modelCats->getCategories(0);
        } else {
            $categories = $modelCats->getCategories(Utilities::getInt('id'));
        }
        $category = $modelCat->getCategory(Utilities::getInt('id'), Utilities::getInt('top'));

        if (Utilities::getInput('top', 'GET', 'string') === 'all_0') {
            if (empty($category)) {
                $category = new StdClass;
                $category->term_id = 0;
                $category->access = 0;
            }
            $category->parent = 'all_0';
        }

        $content->categories = $categories;
        $app                 = Application::getInstance('Wpfd');
        $path_wpfdhelper     = $app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'helpers';
        $path_wpfdhelper     .= DIRECTORY_SEPARATOR . 'WpfdHelper.php';
        require_once $path_wpfdhelper;
        if (WpfdHelper::checkCategoryAccess($category)) {
            $content->category = $category;
        }
        if (Utilities::getInt('id') === Utilities::getInput('top', 'GET', 'string')) {
            $content->category->parent = false;
            $content->category->slug = 'top';
        }
        echo wp_json_encode($content);
        die();
    }
}
