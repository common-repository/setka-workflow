<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use GuzzleHttp\Exception\GuzzleException;
use Setka\Workflow\AJAX\AbstractAction;
use Setka\Workflow\AJAX\Exceptions\UnauthorizedException;
use Setka\Workflow\Services\SyncTerms\Exceptions\OutdatedWordPressException;
use Setka\Workflow\Services\SyncTerms\Exceptions\TermsNotFoundException;
use Setka\Workflow\Services\SyncTerms\SyncTerms;
use Setka\WorkflowSDK\Exceptions\SetkaWorkflowSDKException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

class ExportCategoriesToWorkflowAction extends AbstractAction
{
    /**
     * @var SyncTerms
     */
    protected $syncTerms;

    /**
     * CreateCategoryInWorkflowAction constructor.
     */
    public function __construct()
    {
        $this
            ->setName(self::class)
            ->setEnabledForLoggedIn(true);
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function handleRequest()
    {
        $data     = array();
        $response = $this->getResponse();

        if (!current_user_can('manage_options')) {
            throw new UnauthorizedException();
        }

        try {
            $resultsOfExport = $this->syncTerms
                ->setTaxonomy('category')
                ->exportFirstAvailableTermToWorkflow();
        } catch (OutdatedWordPressException $exception) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolations()
                 ->add(new OutdatedWordPressViolation());
        } catch (GuzzleException $exception) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolations()->add(new ConnectionViolation());
        } catch (SetkaWorkflowSDKException $exception) {
            // SDK exceptions.
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolations()
                 ->add(new WorkflowResponseViolation());
        } catch (TermsNotFoundException $exception) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            $data['stat'] = $this->syncTerms->getTermsStat();

            if ($data['stat']['total'] == 0) {
                // Terms not exists at all.
                $violation = new CategoriesNotFoundViolation();
            } else {
                // Some terms exists but all of it already exported.
                $violation = new AllCategoriesExportedViolation();
            }
            $this->getViolations()->add($violation);
            unset($violation);
        } catch (\Exception $exception) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolations()
                 ->add(new UnknownViolation());
        } finally {
            // Some catch statements already save stat results because they depends on this data.
            if (!isset($data['stat'])) {
                $data['stat'] = $this->syncTerms->getTermsStat();
            }
        }

        if (isset($resultsOfExport)) {
            $data['entity'] = $resultsOfExport;
        }

        if ($this->getViolations()->count()) {
            $data['errors'] = array();
            foreach ($this->getViolations() as $violation) {
                /**
                 * @var $violation ConstraintViolation
                 */
                $data['errors'][] = array(
                  'message' => $violation->getMessage(),
                  'code'    => $violation->getCode(),
                );
            }
        }

        $this->getResponse()
             ->setData($data)
             ->setStatusCode(Response::HTTP_OK);

        return $this;
    }

    /**
     * @return SyncTerms
     */
    public function getSyncTerms()
    {
        return $this->syncTerms;
    }

    /**
     * @param SyncTerms $syncTerms
     *
     * @return $this For chain calls.
     */
    public function setSyncTerms(SyncTerms $syncTerms)
    {
        $this->syncTerms = $syncTerms;
        return $this;
    }
}
