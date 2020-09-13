<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0.3
 */
defined('ABSPATH') || die();

?>

<script>
    wpfdajaxurl = "<?php echo $ajaxUrl; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
    var filterData = null;
    var defaultAllTags = <?php echo ($allTagsFiles !== '' ? $allTagsFiles : '[]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc in view.php?>;
    var tagsLabel = {<?php
    foreach ($TagLabels as $key => $value) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc in view.php
        echo str_replace('-', '', 'wpfd' . esc_html($key)) . ' : "' . esc_html($value) . '",';
    }
    ?>};
    jQuery(document).ready(function () {
        jQuery('#filter_catid_chzn').removeAttr('style');
        jQuery('.chzn-search input').removeAttr('readonly');

        <?php if ((int) $args['tag_filter'] === 1 && $args['display_tag'] === 'searchbox') : ?>
        var defaultTags = [];
        var availTags = [];
            <?php if (isset($filters) && isset($filters['ftags'])) : ?>
        var ftags = '<?php echo esc_html($filters['ftags']);?>';
        defaultTags = ftags.split(',');
            <?php endif; ?>
            <?php if (!empty($allTagsFiles)) : ?>
        availTags = <?php echo $allTagsFiles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc in view.php?>;
            <?php endif; ?>
        jQuery(".input_tags").tagit({
            availableTags: availTags,
            allowSpaces: true,
            initialTags: defaultTags,
            autocomplete: { source: function( request, response ) {
                    var filter = request.term.toLowerCase();
                    response( jQuery.grep(availTags, function(element) {
                        return (element.toLowerCase().indexOf(filter) === 0);
                    }));
                }},
            beforeTagAdded: function(event, ui) {
                if (jQuery.inArray(ui.tagLabel, availTags) == -1) {
                    jQuery('span.error-message').css("display", "block").fadeOut(2000);
                    setTimeout(function() {
                        try {
                            jQuery(".input_tags").tagit("removeTagByLabel", ui.tagLabel, 'fast');
                        } catch (e) {
                            console.log(e);
                        }

                    }, 100);

                    return;
                }
                return true;
            }
        });
        <?php endif; ?>
        <?php if (!empty($filters)) : ?>
        filterData = <?php echo json_encode($filters);?>;
        <?php endif; ?>
        window.history.pushState(filterData, '', window.location);
    });
</script>

<form action="" id="adminForm" name="adminForm" method="post">
    <div id="loader" style="display:none; text-align: center">
        <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/searchloader.svg'); ?>" style="margin: 0 auto"/>
    </div>
    <div class="box-search-filter">
        <div class="searchSection">
            <?php if ((int) $args['cat_filter'] === 1) : ?>
                <div class="categories-filtering" >
                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/menu.svg'); ?>" class="material-icons cateicon"/>
                    <div class="cate-lab"><?php esc_html_e('FILES CATEGORY', 'wpfd'); ?></div>
                    <div class="ui-widget wpfd-listCate" style="display: none">
                        <input title="" type="hidden" value="<?php echo esc_html(isset($args['catid']) ? $args['catid'] : ''); ?>" id="filter_catid" class="chzn-select" name="catid"> </input>
                            <ul class="cate-list" id="cate-list">
                                <?php
                                if (count($categories) > 0) {
                                    $excludes = array();
                                    if (isset($args['exclude']) && $args['exclude'] !== '0') {
                                        $excludes = array_merge($excludes, explode(',', trim($args['exclude'])));
                                    }
                                    ?>
                                <li class="search-cate" >
                                    <input class="qCatesearch" id="wpfdCategorySearch" data-id="" placeholder="<?php esc_html_e('Search...', 'wpfd'); ?>">
                                </li>
                                <li class="cate-item" data-catid="">
                                    <span class="wpfd-toggle-expand"></span>
                                    <span class="wpfd-folder-search"></span>
                                    <label><?php esc_html_e('All', 'wpfd'); ?></label>
                                </li>
                                    <?php
                                    foreach ($categories as $key => $category) {
                                        if ($category->level > 1) {
                                            $downicon = '<span class="wpfd-toggle-expand child-cate"></span>';
                                        } else {
                                            $downicon = '<span class="wpfd-toggle-expand"></span>';
                                        }

                                        if (isset($args['exclude']) && $args['exclude'] !== '0') {
                                            // Remove exclude category and it children
                                            if (in_array((string) $category->term_id, $excludes) || in_array((string) $category->parent, $excludes)) {
                                                // Add it id to excludes array
                                                $excludes[] = (string) $category->term_id;
                                                continue;
                                            }
                                        }
                                        if (isset($filters['catid']) && (int) $filters['catid'] === $category->term_id) {
                                            $echo = '<li class="cate-item choosed" data-catid="'.esc_attr($category->term_id).'" data-catlevel="'. esc_attr($category->level) .'">'
                                                . '<span class="space-child">' . esc_html(str_repeat('-', $category->level - 1)) . '</span>'
                                                . $downicon
                                                . '<span class="wpfd-folder-search"></span>'
                                                . '<label>' . esc_html($category->name) .'</label>'
                                                . '</li>';
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc above
                                            echo $echo;
                                        } else {
                                            $echo = '<li class="cate-item" data-catid="'.esc_attr($category->term_id).'" data-catlevel="'. esc_attr($category->level) .'">'
                                                . '<span class="space-child">'. esc_html(str_repeat('-', $category->level - 1)) .'</span>'
                                                . $downicon
                                                . '<span class="wpfd-folder-search"></span>'
                                                . '<label>' . esc_html($category->name) .'</label>'
                                                . '</li>';
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc above
                                            echo $echo;
                                        }
                                    }
                                }
                                ?>
                            </ul>
                    </div>
                </div>

            <?php elseif ($args['catid'] !== '0') : ?>
                <input type="hidden" name="catid" value="<?php echo esc_html($filters['catid']); ?>" />
            <?php endif; ?>
            <div class="only-file input-group clearfix wpfd_search_input" id="Search_container">
                <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/search-24.svg'); ?>" class="material-icons wpfd-icon-search"/>
                <input type="text" class="pull-left required" name="q" id="txtfilename"
                       placeholder="<?php esc_html_e('Search files...', 'wpfd'); ?>"
                       value="<?php echo esc_html(isset($filters['q']) ? $filters['q'] : ''); ?>"
                />
                <button type="button" id="btnsearch" class="pull-left"><?php esc_html_e('Search', 'wpfd'); ?></button>
            </div>
        </div>

        <?php if ((isset($args['tag_filter']) && (int) $args['tag_filter'] === 1) ||
                  (isset($args['create_filter']) && (int) $args['create_filter'] === 1) ||
                  (isset($args['update_filter']) && (int) $args['update_filter'] === 1)) : ?>
            <div class="by-feature feature-border" id="Category_container">
                                                                            <?php if ((isset($args['tag_filter']) && (int) $args['tag_filter'] === 1) &&
                                                                            (isset($args['create_filter']) && (int) $args['create_filter'] === 1) &&
                                                                            (isset($args['update_filter']) && (int) $args['update_filter'] === 1)) : ?>
                <div class="wpfd_tab">
                    <button class="tablinks active" onclick="openSearchfilter(event, 'Filter')"><?php esc_html_e('FILTER', 'wpfd') ?></button>
                    <button class="tablinks" onclick="openSearchfilter(event, 'Tags')"><?php esc_html_e('TAGS', 'wpfd'); ?></button>
                    <span class="feature-toggle toggle-arrow-up-alt"></span>
                </div>
                                                                            <?php endif; ?>

                <div class="top clearfix">
                    <div class="pull-left"><p class="filter-lab"><?php esc_html_e('FILTER', 'wpfd') ?></p></div>
                    <div class="pull-right"><span class="feature-toggle toggle-arrow-up-alt"></span></div>
                </div>
                                                                            <?php
                                                                            $span = 'span3';
                                                                            if ((int) $args['tag_filter'] === 1 && (int) $args['display_tag'] === 'checkbox') {
                                                                                $span = 'span4';
                                                                            }
                                                                            ?>
                <div class="feature clearfix row-fluid wpfd_tabcontainer">

                        <!-- Tab content -->
                        <div id="Filter" class="wpfd_tabcontent active">
                                                                            <?php
                                                                            $date_class= 'date-filter';
                                                                            if ((int) $args['create_filter'] === 0 && (int) $args['update_filter'] === 0) {
                                                                                $date_class= 'wpfd-date-hidden';
                                                                            }
                                                                            ?>
                            <div class="<?php echo esc_attr($date_class); ?>">
                                                                            <?php if ((int) $args['create_filter'] === 1) : ?>
                                    <div class="creation-date">
                                        <p class="date-info"><?php esc_html_e('CREATION DATE', 'wpfd'); ?></p>
                                        <div class="create-date-container">
                                            <div>
                                                <span class="lbl-date"><?php esc_html_e('From', 'wpfd'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="cfrom" name="cfrom"
                                                           value="<?php echo esc_attr(isset($filters['cfrom']) ? $filters['cfrom'] : ''); ?>"
                                                           id="cfrom"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="cfrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="lbl-date"><?php esc_html_e('To', 'wpfd'); ?></span>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" data-min="cfrom" type="text" name="cto" id="cto"
                                                           value="<?php echo esc_attr(isset($filters['cto']) ? $filters['cto'] : ''); ?>"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="cto" data-min="cfrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                                                            <?php endif; ?>
                                                                            <?php if ((int) $args['update_filter'] === 1) : ?>
                                    <div class="update-date">
                                        <p class="date-info"><?php esc_html_e('UPDATE DATE', 'wpfd'); ?></p>
                                        <div class="update-date-container">
                                            <div><span class="lbl-date"><?php esc_html_e('From', 'wpfd'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="ufrom"
                                                           value="<?php echo esc_attr(isset($filters['ufrom']) ? $filters['ufrom'] : ''); ?>"
                                                           name="ufrom" id="ufrom"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="ufrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                            <div><span class="lbl-date"><?php esc_html_e('To', 'wpfd'); ?> </span>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="ufrom"
                                                           value="<?php echo esc_attr(isset($filters['uto']) ? $filters['uto'] : ''); ?>"
                                                           name="uto" id="uto"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="uto" data-min="ufrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                                                            <?php endif; ?>
                            </div>
                        </div>

                        <div id="Tags" class="wpfd_tabcontent mobilecontent">
                                                                            <?php if (!empty($allTagsFiles)) : ?>
                                                                                <?php if ((int) $args['tag_filter'] === 1 && $args['display_tag'] === 'searchbox') : ?>
                                    <div class="span12 tags-filtering">
                                        <p class="tags-info"><?php esc_html_e('TAGS', 'wpfd'); ?></p>
                                        <input title="" type="text" name="ftags" class="tagit input_tags"
                                               value="<?php echo esc_attr(isset($filters['ftags']) ? $filters['ftags'] : ''); ?>"/>
                                    </div>
                                    <span class="error-message"><?php esc_html_e('No tag matching the query', 'wpfd'); ?></span>
                                                                                <?php endif; ?>

                                                                                <?php if ((int) $args['tag_filter'] === 1 && $args['display_tag'] === 'checkbox') : ?>
                                    <div class="clearfix row-fluid">
                                        <div class="span12 chk-tags-filtering">
                                            <p class="tags-info" style="text-align:left;"><?php esc_html_e('TAGS', 'wpfd'); ?></p>
                                            <input type="hidden" name="ftags"
                                                   class="input_tags"
                                                   value="<?php echo esc_attr(isset($filters['ftags']) ? $filters['ftags'] : ''); ?>"/>
                                                                                    <?php
                                                                                    if (isset($filters['ftags'])) {
                                                                                        $selectedTags = explode(',', $filters['ftags']);
                                                                                    } else {
                                                                                        $selectedTags = array();
                                                                                    }
                                                                                    $allTags = str_replace(array('[', ']', '"'), '', $allTagsFiles);
                                                                                    if ($allTags !== '') {
                                                                                        $arrTags = explode(',', $allTags);
                                                                                        asort($arrTags);
                                                                                        echo '<ul>';
                                                                                            echo '<label class="labletags">';
                                                                                                esc_html_e('Filter by Tags', 'wpfd');
                                                                                            echo '</label>';
                                                                                        foreach ($arrTags as $key => $fileTag) {
                                                                                            ?>
                                                        <li class="tags-item">
                                                            <span><?php echo esc_html($TagLabels[$fileTag]); ?></span>
                                                            <input type="checkbox" name="chk_ftags[]" value="<?php echo esc_attr($fileTag);?>" class="ju-input chk_ftags" id="ftags<?php echo esc_attr($key); ?>">
                                                        </li>
                                                                                        <?php }
                                                                                        echo '</ul>';
                                                                                    }
                                                                                    ?>
                                        </div>
                                    </div>
                                                                                <?php endif; ?>

                                                                            <?php else : ?>
                                <div class="no-tags"></div>
                                                                            <?php endif; ?>
                        </div>
                    <div class="clearfix"></div>
                    <div class="box-btngroup-below">
                        <a href="#" class="btnsearchbelow" type="reset" id="btnReset">
                                                                            <?php esc_html_e('CLEAR ALL', 'wpfd'); ?>
                        </a>
                        <button id="btnsearchbelow" class="btnsearchbelow" type="button">
                                                                            <?php esc_html_e('CONTINUE', 'wpfd'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php elseif ($args['catid'] !== '0') : ?>
            <input type="hidden" name="catid" value="<?php echo esc_html($args['catid']); ?>" />
        <?php endif; ?>
        <?php if (isset($args['exclude']) && $args['exclude'] !== '0') : ?>
            <input type="hidden" name="exclude" value="<?php echo esc_html($filters['exclude']); ?>" />
        <?php endif; ?>
        <div id="wpfd-results" class="list-results"></div>
    </div>
</form>

