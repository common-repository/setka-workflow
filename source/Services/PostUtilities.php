<?php
namespace Setka\Workflow\Services;

/**
 * Class PostUtilities
 */
class PostUtilities
{
    /**
     * Returns post edit url.
     *
     * Since get_edit_post_link() not returns url if user can't edit the post we created our own url generator.
     * For example this method used in webhooks by HTTP request.
     *
     * @see get_edit_post_link
     *
     * @throws \Exception If something goes wrong.
     *
     * @param $id int Post id.
     *
     * @return string Url to page where you can edit the post.
     */
    public static function getEditUrl($id)
    {
        $postTypeObject = get_post_type_object('post');

        if (!$postTypeObject) {
            throw new \Exception();
        }

        if (!property_exists($postTypeObject, '_edit_link')) {
            throw new \Exception();
        }

        return admin_url(sprintf($postTypeObject->_edit_link . '&action=edit', $id));
    }

    /**
     * Returns post view url.
     *
     * @param $id int Post id.
     *
     * @throws \Exception If something goes wrong.
     *
     * @return string Url to page where you can view the post.
     */
    public static function getViewGuidUrl($id)
    {
        $url = get_the_guid($id);

        if (is_string($url) && !empty($url)) {
            return $url;
        } else {
            throw new \Exception();
        }
    }

    /**
     * Returns pretty post view url (permalink).
     *
     * @param $id int Post id.
     *
     * @return string Post view url.
     * @throws \RuntimeException In case of error.
     */
    public static function getViewUrl($id)
    {
        $url = get_permalink($id);

        if (is_string($url) && !empty($url)) {
            return $url;
        } else {
            throw new \RuntimeException();
        }
    }
}
