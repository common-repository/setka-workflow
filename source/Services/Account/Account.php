<?php
namespace Setka\Workflow\Services\Account;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Workflow\Services\Account\Exceptions\APIException;
use Setka\Workflow\Services\Account\Exceptions\InvalidResponseException;
use Setka\Workflow\Services\TermMetaUtils;
use Setka\WorkflowSDK\Actions\Spaces\GetSpaceAction;
use Setka\WorkflowSDK\API;
use Setka\WorkflowSDK\Exceptions\NotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Account
 */
class Account
{
    /**
     * @var OptionInterface
     */
    protected $tokenOption;

    /**
     * @var OptionInterface
     */
    protected $currentSpaceOption;

    /**
     * @var API
     */
    protected $api;

    /**
     * @var TermMetaUtils
     */
    protected $termMetaUtils;

    /**
     * Account constructor.
     *
     * @param $tokenOption OptionInterface
     * @param $currentSpaceOption OptionInterface
     * @param $api API
     * @param $termMetaUtils TermMetaUtils
     */
    public function __construct(
        OptionInterface $tokenOption,
        OptionInterface $currentSpaceOption,
        API $api,
        TermMetaUtils $termMetaUtils
    ) {
        $this->tokenOption        = $tokenOption;
        $this->currentSpaceOption = $currentSpaceOption;
        $this->api                = $api;
        $this->termMetaUtils      = $termMetaUtils;
    }

    /**
     * @param $token string a new token which should be used to Sign In
     *
     * @throws \Exception Different exceptions.
     *
     * @return $this For chain calls.
     */
    public function signIn($token)
    {
        $this->api->getAuth()->setToken($token);
        $action = new GetSpaceAction($this->api);

        try {
            $actionDetails = $action->configureDetails(array(
                'options' => array(
                    'http_errors' => false,
                ),
            ));

            $space = $action
                ->setDetails($actionDetails)
                ->request()
                ->handleResponse();
        } catch (\Exception $exception) {
            throw new APIException('', 0, $exception);
        }

        $currentSpaceValue = array(
            'id' => $space->getId(),
            'name' => $space->getName(),
            'short_name' => $space->getShortName(),
            'active' => $space->isActive(),
            'created_at' => $space->getCreatedAt(),
            'updated_at' => $space->getUpdatedAt(),
        );

        try {
            $result = $this->currentSpaceOption->validateValue($currentSpaceValue);
        } catch (\Exception $exception) {
            throw new InvalidResponseException('', 0, $exception);
        }
        if (count($result) != 0) {
            throw new InvalidResponseException();
        }

        // Authentication completed! Save the results.

        $this->currentSpaceOption->updateValue($currentSpaceValue);
        unset($currentSpaceValue);

        $this->tokenOption->updateValue($token);

        return $this;
    }

    /**
     * Checks if site admin already signed in.
     *
     * @return bool True if signed in.
     */
    public function isSignedIn()
    {
        $token = $this->tokenOption->get();
        if ($token) {
            return true;
        }
        return false;
    }

    /**
     * Sign out from current API token.
     *
     * @return $this For chain calls.
     */
    public function signOut()
    {
        $this->tokenOption->delete();
        $this->currentSpaceOption->delete();

        try {
            $this->termMetaUtils
                ->deleteTermMetas()
                ->resetCache();
        } catch (\Exception $exception) {
            // Do nothing.
        }

        return $this;
    }

    /**
     * @return OptionInterface
     */
    public function getTokenOption()
    {
        return $this->tokenOption;
    }

    public function getSpaceShortName()
    {
        $value = $this->currentSpaceOption->get();
        return $value['short_name'];
    }

    /**
     * Compare token with saved into WordPress.
     *
     * @param $token string Token which you want to compare.
     *
     * @return bool True if tokens identical.
     */
    public function compareToken($token)
    {
        $saved = $this->getTokenOption()->get();

        if ($token === $saved) {
            return true;
        }

        return false;
    }
}
