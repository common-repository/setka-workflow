<?php
namespace Setka\Workflow\Admin\MetaBoxes\WorkflowID;

/**
 * Class WorkflowId
 */
class WorkflowId
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}
