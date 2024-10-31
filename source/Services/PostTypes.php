<?php
namespace Setka\Workflow\Services;

class PostTypes
{
    /**
     * @return array List of available post types without some non related.
     */
    public static function getPostTypes()
    {
        // Allow developers to overwrite this list of post types.
        $postTypes = apply_filters('setka_workflow_available_post_types', null);

        if ($postTypes) {
            return $postTypes;
        }

        $postTypes = get_post_types();

        unset($postTypes['attachment']);
        unset($postTypes['revision']);
        unset($postTypes['nav_menu_item']);
        unset($postTypes['custom_css']);
        unset($postTypes['customize_changeset']);
        unset($postTypes['oembed_cache']);
        unset($postTypes['amp_validated_url']);

        return $postTypes;
    }
}
