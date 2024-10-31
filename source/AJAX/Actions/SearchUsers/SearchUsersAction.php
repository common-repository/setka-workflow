<?php
namespace Setka\Workflow\AJAX\Actions\SearchUsers;

use Setka\Workflow\AJAX\AbstractAction;
use Setka\Workflow\AJAX\Exceptions\UnauthorizedException;
use Setka\Workflow\Services\UserQuery;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchUsersAction
 */
class SearchUsersAction extends AbstractAction
{
    /**
     * SearchUsersAction constructor.
     */
    public function __construct()
    {
        $this
            ->setName(self::class)
            ->setEnabledForLoggedIn(true);
    }

    public function handleRequest()
    {
        $data     = array();
        $response = $this->getResponse();

        if (!current_user_can('manage_options')) {
            throw new UnauthorizedException();
        }

        $searchTerm = $this->getRequest()->query->get('term');

        if (is_string($searchTerm) && !empty($searchTerm)) {
            $users = UserQuery::search20($searchTerm);
        } else {
            $users = UserQuery::createLast10();
        }

        if ($users->get_total() > 0) {
            $usersNew = array();
            foreach ($users->get_results() as $user) {
                /**
                 * @var $user \WP_User
                 */
                $usersNew[] = array(
                    'id' => $user->ID,
                    'text' => sprintf(
                        '%1$s <%2$s> [ID=%3$s]',
                        $user->data->display_name,
                        $user->data->user_email,
                        $user->data->ID
                    ),
                );
            }
            $data['results'] = $usersNew;
            unset($usersNew, $user);
        }

        if (!isset($data['results'])) {
            $data['results'] = array();
        }

        $this->getResponse()
             ->setData($data)
             ->setStatusCode(Response::HTTP_OK);

        return $this;
    }
}
