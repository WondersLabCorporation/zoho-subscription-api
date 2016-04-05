<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;
use Zoho\Subscription\Common\Factory;

/**
 * @author Hang Pham <thi@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link   https://www.zoho.com/subscriptions/api/v1/#customers
 */
class Customer extends Record
{
    protected $command = 'customers';
    protected $module = 'customer';
    
    public $customer_id;
    public $display_name;
    public $first_name;
    public $last_name;
    public $email;
    public $company_name;
    public $phone;
    public $mobile;
    public $website;
    public $billing_address;
    public $shipping_address;
    public $currency_code;
    public $ach_supported;
    public $notes;
    public $custom_fields;
                
    protected $base_template = [
        'display_name',
        'first_name',
        'last_name',
        'email',
        'company_name',
        'phone',
        'mobile',
        'website',
        'billing_address' => [
            'street',
            'city',
            'price',
            'state',
            'zip',
            'country',
            'fax',
        ],
        'shipping_address' => [
            'street',
            'city',
            'price',
            'state',
            'zip',
            'country',
            'fax',
        ],
        'currency_code',
        'ach_supported',
        'notes',
        'custom_fields',
    ];
    
    
    /**
     * Delete an existing customer.
     */
    public function delete()
    {
        $this->saveRecord('DELETE', sprintf('customers/%s', $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Get all subscriptions of customer
     * @return Record[]
     * @throws SubscriptionException
     */
    public function getSubscriptions()
    {
        return Factory::getEntityList('Subscription', ['query' => ['customer_id' => $this->getId()]], $this->client, false);
    }
    
    /**
     * Get subscriptions of customer by reference_id
     * @param string $reference_id
     * @return Record|null
     */
    public function getSubscriptionByReference($reference_id)
    {
        $subscriptions = $this->getSubscriptions();
        if ($subscriptions !== null) {
            foreach ($subscriptions as $subscription) {
                if ($subscription->reference_id == $reference_id) {
                    return $subscription;
                }
            }
        }
        return null;
    }
    
    /**
     * Create customer's card entity
     * @param array $params
     * @return Card
     */
    public function createCard($params)
    {
        $params['customer_id'] = $this->getId();
        return Factory::createEntity('Card', $params, $this->client, false);
    }
    
    /**
     * Get customer's card entity from Zoho
     * @param integer $card_id
     * @return Card
     */
    public function getCard($card_id)
    {
        return Factory::getEntity('Card', ['customer_id' => $this->getId(), 'card_id' => $card_id], $this->client, false);
    }
    
    /**
     * Delete an existing card.
     * @param integer $card_id
     * @return boolean
     */
    public function deleteCard($card_id)
    {
        $entity = $this->createCard(['card_id' => $card_id]);
        return $entity->delete();
    }
    
    /**
     * Create customer's contact person entity.
     * @param array $params
     * @return ContactPerson
     */
    public function createContactPerson($params)
    {
        $params['customer_id'] = $this->getId();
        return Factory::createEntity('ContactPerson', $params, $this->client, false);
    }
    
    /**
     * Get customer's contact person entity from Zoho.
     * @param integer $contactPerson_id
     * @return ContactPerson
     */
    public function getContactPerson($contactPerson_id)
    {
        return Factory::getEntity('ContactPerson', ['customer_id' => $this->getId(), 'contactperson_id' => $contactPerson_id], $this->client, false);
    }
    
    /**
     * Delete an existing contact person.
     * @param integer $contactPerson_id
     * @return boolean
     */
    public function deleteContactPerson($contactPerson_id)
    {
        $entity = $this->createContactPerson(['contactperson_id' => $contactPerson_id]);
        return $entity->delete();
    }
    
    /**
     * Get customer's contact persons list
     * @return ContactPerson[]
     */
    public function getContactPersonsList()
    {
        $entities_data = $this->client->getList(sprintf('customers/%s/contactpersons', $this->getId()));
        if ($this->hasError()) {
            return null;
        }
        return Factory::getEntitiesFromArray($entities_data, 'ContactPerson', $this->client, false);
    }
    
    
}
