<?php

namespace Stjornvisi\Notify;

use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Company as CompanyService;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\Service\Values;

/**
 * Handler to send a welcome message to new users
 *
 * @package Stjornvisi\Notify
 */
class Welcome extends AbstractNotifier
{
    protected function getRequiredData()
    {
        return ['user_id'];
    }

    /**
     * Send notification to what ever media or outlet
     * required by the implementer.
     *
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //USER
        //	get the user.
        $userId = $this->params->user_id;
        $user = $this->getUser($userId);
        if (!$user) {
            throw new NotifyException("User [$userId] not found");
        }

        $companyId = (property_exists($this->params, 'created_company_id'))
            ? $this->params->created_company_id
            : null;

        $company = ($companyId)
            ? $this->getCompany($companyId)
            : null;

        if ($company === false) {
            throw new NotifyException("Company [{$companyId}] not found");
        }

        if ($company) {
            $type = $company->business_type;
            if ($type == Values::COMPANY_TYPE_PERSON
                || $type == Values::COMPANY_TYPE_UNIVERSITY
            ) {
                // The company is only used when a new Company is created
                // and that Company can never by a person or university
                $company = null;
            }
        }

        $this->logger->debug("User welcome email [{$user->email}]");

        $body = $this->createEmailBody('user-welcome', [
            'user'    => $user,
            'company' => $company,
        ]);

        $mail = new Mail([
            'name'    => $user->name,
            'email'   => $user->email,
            'subject' => 'Velkomin/nn í Stjórnvísi',
            'body'    => $body,
        ]);

        $this->sendEmail($mail);

        return $this;
    }

    /**
     * @param int $userId
     *
     * @return bool|\stdClass
     */
    private function getUser($userId)
    {
        return $this->getServiceLocator()->get(UserService::class)
            ->get((int)$userId);
    }

    private function getCompany($id)
    {
        $id = (int)$id;
        if ($id < 1) {
            return null;
        }
        return $this->getServiceLocator()->get(CompanyService::class)
            ->get($id);
    }
}
