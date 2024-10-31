<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\Special\NumericOption;
use Setka\Workflow\Plugin;

/**
 * Class AutoCreatedPostsAuthorIdOption
 */
class AutoCreatedPostsAuthorIdOption extends NumericOption
{
    /**
     * AutoCreatedPostsAuthorIdOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_auto_created_posts_author_id');
    }
}
