<?php
namespace Setka\Workflow\Admin\Services;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SavePostRunner implements RunnerInterface
{
    /**
     * @var ContainerInterface Container with services.
     */
    protected static $container;

    /**
     * @inheritdoc
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * @inheritdoc
     */
    public static function setContainer(ContainerInterface $container = null)
    {
        self::$container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function run()
    {
    }

    /**
     * @param int $id Post ID.
     * @param \WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public static function savePost($id, $post, $update)
    {
        /**
         * @var $savePost SavePost
         */
        $savePost = self::getContainer()->get(SavePost::class);
        $savePost->handlePost($id, $post, $update);
    }
}
