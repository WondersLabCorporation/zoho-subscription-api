<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Card extends Record
{
    protected $command = 'cards';
    protected $module = 'card';
    
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
     * @param string $card_id
     * @throws SubscriptionException
     */
    public function delete($card_id = null)
    {
        $card_id = empty($card_id) ? $this->getId() : $card_id;
        $response = $this->request('DELETE', sprintf('customers/%s/cards/%s', $this['customer_id'], $card_id));
        $this->processResponse($response);
    }

}
