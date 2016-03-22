<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;


class ContactPerson extends Client
{
    protected $command = 'contactpersons';
    protected $module = 'contactperson';
    
    protected $base_template = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'phone',
    ];
    
    public function load($id = null)
    {
        assert(isset($this['customer_id']), 'You must specify customer_id before load an entity.');
        parent::load($id);
    }
    
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, ['payment_gateway']);
    }
    
    protected function getCommandRetrieve()
    {
        return sprintf('customers/%s/contactpersons/%s', $this['customer_id'], $this->getId());
    }
    
    protected function getCommandCreate()
    {
        return sprintf('customers/%s/contactpersons', $this['customer_id']);
    }
    
    protected function getCommandUpdate()
    {
        return sprintf('customers/%s/contactpersons/%s', $this['customer_id'], $this->getId());
    }
    
    /**
     * Delete an existing contact person.
     * @param string $contactperson_id
     * @throws SubscriptionException
     */
    public function delete($contactperson_id = null)
    {
        $contactperson_id = empty($contactperson_id) ? $this->getId() : $contactperson_id;
        $response = $this->request('DELETE', sprintf('customers/%s/contactpersons/%s', $this['customer_id'], $contactperson_id));
        $this->processResponse($response);
    }

    /**
     * Returns all contact persons as objects.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function getList() {
        $result = parent::getList([], sprintf('customers/%s/contactpersons', $this['customer_id']));
        return $this->buildEntitiesFromArray($result);
    }
}
