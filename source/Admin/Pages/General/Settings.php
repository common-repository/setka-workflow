<?php
namespace Setka\Workflow\Admin\Pages\General;

/**
 * Class Settings
 */
class Settings extends SignIn
{
    /**
     * @var boolean Flag which shows should we publish posts in Workflow or not.
     */
    protected $publishInWorkflow;

    /**
     * @var array List of post types.
     */
    protected $postTypes;

    /**
     * @var integer Id of user.
     */
    protected $postAuthorId;

    /**
     * @var boolean Flag which shows should we export terms after creation in WordPress or not.
     */
    protected $exportCategoriesAutomatically;

    /**
     * @return bool
     */
    public function isPublishInWorkflow()
    {
        return $this->publishInWorkflow;
    }

    /**
     * @param bool $publishInWorkflow
     *
     * @return $this
     */
    public function setPublishInWorkflow($publishInWorkflow)
    {
        $this->publishInWorkflow = $publishInWorkflow;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostTypes()
    {
        return $this->postTypes;
    }

    /**
     * @param array $postTypes
     *
     * @return $this
     */
    public function setPostTypes(array $postTypes)
    {
        $this->postTypes = $postTypes;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostAuthorId()
    {
        return $this->postAuthorId;
    }

    /**
     * @param int $postAuthorId
     *
     * @return $this
     */
    public function setPostAuthorId($postAuthorId)
    {
        $this->postAuthorId = $postAuthorId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportCategoriesAutomatically()
    {
        return $this->exportCategoriesAutomatically;
    }

    /**
     * @param bool $exportCategoriesAutomatically
     *
     * @return $this
     */
    public function setExportCategoriesAutomatically($exportCategoriesAutomatically)
    {
        $this->exportCategoriesAutomatically = $exportCategoriesAutomatically;
        return $this;
    }
}
