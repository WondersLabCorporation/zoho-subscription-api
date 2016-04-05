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
}
