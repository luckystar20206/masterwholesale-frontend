<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smartwave\Megamenu\Model\Category;

/**
 * Class DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' =>
                [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' =>
                [
                    'image',
                    'description',
                    'landing_page',
                ],
            'display_settings' =>
                [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' =>
                [
                    'url_key',
                    'url_key_create_redirect',
                    'use_default.url_key',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' =>
                [
                ],
            'design' =>
                [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' =>
                [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'sw-menu' =>
                [
                    'sw_menu_hide_item',
                    'sw_menu_type',
                    'sw_menu_static_width',
                    'sw_menu_cat_columns',
                    'sw_menu_float_type',
                    'sw_menu_cat_label',
                    'sw_menu_icon_img',
                    'sw_menu_font_icon',
                    'sw_menu_block_top_content',
                    'sw_menu_block_left_width',
                    'sw_menu_block_left_content',
                    'sw_menu_block_right_width',
                    'sw_menu_block_right_content',
                    'sw_menu_block_bottom_content',
                ],
            'category_view_optimization' =>
                [
                ],
            'category_permissions' =>
                [
                ],
        ];
    }
}
