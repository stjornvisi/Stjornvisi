<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 07/11/14
 * Time: 10:00
 */

namespace Stjornvisi\Form;

use Stjornvisi\Service\Company;

use Stjornvisi\Service\Values;
use Zend\Form\Form;

class NewUserCompanySelect extends Form
{
    private $domainList = [];

    public function __construct(Company $company)
    {
        $companies = $company->fetchAll([
            Values::COMPANY_TYPE_PERSON,
            Values::COMPANY_TYPE_UNIVERSITY,
        ]);
        $options = array();
        foreach ($companies as $item) {
            $options[$item->id] = $item->name;
            $domain = $this->parseDomain($item->website);
            if ($domain) {
                $this->domainList[$domain] = $item->id;
            }
        }

        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'company-select',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'placeholder' => 'Nafn...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Fyrirtæki',
                'empty_option' => 'Veldu fyrirtæki',
                'value_options' => $options
            ),
        ));

        $this->add(array(
            'name' => 'submit-company-select',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Submit',
            ),
            'options' => array(
                'label' => 'Submit',
            ),
        ));

    }

    public function detectFromEmail($email)
    {
        $id = $this->findCompanyFromEmail($email);
        if ($id) {
            $this->get('company-select')->setValue($id);
        }
    }

    private function findCompanyFromEmail($email)
    {
        if ($email) {
            $domain = preg_replace('/.*@/', '', $email);
            if ($domain && isset($this->domainList[$domain])) {
                return $this->domainList[$domain];
            }
        }
        return null;
    }

    private function parseDomain($website)
    {
        if ($website) {
            $parsed = parse_url($website);
            if ($parsed) {
                $host = null;
                if (isset($parsed['scheme']) && isset($parsed['host'])) {
                    $host = $parsed['host'];
                }
                else if (isset($parsed['path']) && !isset($parsed['scheme'])) {
                    $host = preg_replace('@/.*@', '', $parsed['path']);
                }
                if ($host) {
                    return preg_replace('@^(www\.)?@', '', $host);
                }
            }
        }
        return null;
    }
}
