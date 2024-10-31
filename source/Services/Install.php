<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Options\OptionInterface;

/**
 * Class Install
 */
class Install
{
    /**
     * @var OptionInterface
     */
    protected $autoCreatedPostsAuthorIdOption;

    /**
     * Install constructor.
     *
     * @param OptionInterface $autoCreatedPostsAuthorIdOption
     */
    public function __construct(OptionInterface $autoCreatedPostsAuthorIdOption)
    {
        $this->autoCreatedPostsAuthorIdOption = $autoCreatedPostsAuthorIdOption;
    }

    /**
     * Run installation.
     *
     * @return $this For chain calls.
     */
    public function run()
    {
        $this->setupAutoCreatedPostsAuthorId();
        return $this;
    }

    /**
     * Setup AutoCreatedPostsAuthorIdOption value if it not previously saved.
     *
     * @return $this For chain calls.
     */
    public function setupAutoCreatedPostsAuthorId()
    {
        try {
            $userId = $this->autoCreatedPostsAuthorIdOption->get();
            $userId = (int) $userId;
            $user   = get_user_by('ID', $userId);
        } catch (\Exception $exception) {
            $user = false;
            $this->autoCreatedPostsAuthorIdOption->delete();
        }

        if ($user) {
            return $this;
        }

        if (current_user_can('edit_posts')) {
            $user = wp_get_current_user();
            $this->autoCreatedPostsAuthorIdOption->updateValue($user->ID);
        }

        return $this;
    }
}
