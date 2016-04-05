<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class ContactPerson extends Record
{
    protected $command = 'contactpersons';
    protected $module = 'contactperson';
    
    public $contactperson_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;

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
     * @return bollean
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('customers/%s/contactpersons/%s', $this->customer_id, $this->getId()));
        return !$this->hasError();
    }
}
