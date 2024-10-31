<?php
namespace Setka\Workflow\Admin\Pages\General;

/**
 * Class SignIn
 */
class SignIn
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $nonce;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     *
     * @return $this
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
        return $this;
    }
}
