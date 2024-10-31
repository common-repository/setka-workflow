<?php
namespace Setka\Workflow\Services;

use Setka\Workflow\Services\Account\Account;

/**
 * Class WorkflowTicket
 */
class WorkflowTicketUtilities
{
    const TICKED_URL_PATTERN = '/%1$s/tickets/%2$s';

    /**
     * @var string Url from Setka Workflow.
     *
     * Example: https://workflow.setka.io/space-short-name/tickets/81604?d=2017-10-19&view=week
     */
    protected $url;

    /**
     * @var string Url generated from Ticket Id.
     */
    protected $urlGenerated;

    /**
     * @var string Base url for generating Url.
     */
    protected $baseUrl;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var array Result of parsing Url.
     */
    protected $urlParts;

    /**
     * @var integer Ticket Id.
     */
    protected $ticketId;

    /**
     * WorkflowTicket constructor.
     *
     * @param string $baseUrl
     * @param Account $account
     */
    public function __construct($baseUrl, Account $account)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->account = $account;
    }

    /**
     * Parses the url.
     *
     * @throws \Exception If Url is not valid.
     *
     * @return $this For chain calls.
     */
    public function parseUrl()
    {
        $this->urlParts = null;
        $this->ticketId = null;

        $this->urlParts = parse_url($this->url);

        if (!$this->urlParts) {
            throw new \Exception();
        }

        if (!isset($this->urlParts['path'])) {
            throw new \Exception();
        }

        $result = preg_match('/\/([^\/]+)\/tickets\/(\d+)\/{0,1}$/s', $this->urlParts['path'], $matches);

        // Found or not?
        if ($result !== 1) {
            throw new \Exception();
        }
        unset($result);

        // Verify ticket id.
        $matches[2] = (int) $matches[2];
        if ($matches[2] <= 0) {
            throw new \Exception();
        }

        // Verify space short name.
        if ($matches[1] !== $this->account->getSpaceShortName()) {
            throw new \Exception();
        }

        $this->setTicketId($matches[2]);

        return $this;
    }

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
     * @return $this For chain calls.
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getTicketId()
    {
        return $this->ticketId;
    }

    /**
     * @param int $ticketId
     *
     * @return $this For chain calls.
     */
    public function setTicketId($ticketId)
    {
        $this->ticketId = $ticketId;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlGenerated()
    {
        return $this->urlGenerated;
    }

    /**
     * @param string $urlGenerated
     *
     * @return $this For chain calls.
     */
    public function setUrlGenerated($urlGenerated)
    {
        $this->urlGenerated = $urlGenerated;
        return $this;
    }

    /**
     * @throws \Exception
     * @return $this
     */
    public function generateUrl()
    {
        $this->urlGenerated = null;

        if (!isset($this->ticketId)) {
            throw new \Exception();
        }

        $url = sprintf(
            self::TICKED_URL_PATTERN,
            $this->account->getSpaceShortName(),
            $this->ticketId
        );

        $this->urlGenerated = $this->baseUrl . $url;

        return $this;
    }
}
