<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Card extends Record
{
    protected $command = 'cards';
    protected $module = 'card';
    
    public $card_id;
    public $card_number;
    public $cvv_number;
    public $expiry_month;
    public $expiry_year;
    public $first_name;
    public $last_name;
    public $city;
    public $state;
    public $zip;
    public $country;
    
    protected $base_template = [
        'card_number',
        'cvv_number',
        'expiry_month',
        'expiry_year',
        'first_name',
        'last_name',
        'city',
        'state',
        'zip',
        'country',
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
        return sprintf('customers/%s/cards/%s', $this['customer_id'], $this->getId());
    }
    
    protected function getCommandCreate()
    {
        return sprintf('customers/%s/cards', $this['customer_id']);
    }
    
    protected function getCommandUpdate()
    {
        return sprintf('customers/%s/cards/%s', $this['customer_id'], $this->getId());
    }
    
    /**
     * Delete an existing card.
     * @return bollean 
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('customers/%s/cards/%s', $this['customer_id'], $this->getId()));
        return !$this->hasError();
    }

}
