<?php
namespace Setka\Workflow\Services;

/**
 * Class UserQuery
 */
class UserQuery
{
    /**
     * @return \WP_User_Query
     */
    public static function createLast10()
    {
        return new \WP_User_Query(array(
            'orderby' => 'ID',
            'order' => 'DESC',
            'number' => 10,
        ));
    }

    /**
     * @param $search string Search request.
     *
     * @return \WP_User_Query
     */
    public static function search20($search)
    {
        return new \WP_User_Query(array(
            'search' => $search,
            'search_columns' => array(
                'ID',
                'user_login',
                'user_nicename',
                'user_email',
                'user_url',
            ),
            'number' => 20,
        ));
    }
}
