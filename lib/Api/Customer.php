<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;

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
     * @param integer $customer_id
     * @throws SubscriptionException
     */
    public function delete($customer_id = null)
    {
        if (empty($customer_id)){
            $customer_id = $this['customer_id'];
        }
        $response = $this->request('DELETE', sprintf('customers/%s', $this->getId()));
        $this->processResponse($response);
    }
    
    /**
     * Returns all customers as objects.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function getList() {
        $result = parent::getList();
        return $this->buildEntitiesFromArray($result);
    }
    
    /**
     * @param string $customer_email The customer's email
     *
     * @return array
     * @throws SubscriptionException
     */
    public function getListByEmail($customer_email)
    {
        if (empty($customer_email)){
            $customer_email = $this['email'];
        }
        $result = parent::getList(['email' => $customer_email]);
        return $this->buildEntitiesFromArray($result);
    }

    /**
     * @param string $customer_email
     *
     * @return array
     * @throws SubscriptionException
     */
    public function getByEmail($customer_email)
    {
        $customers = $this->getListByEmail($customer_email);

        return $this->load($customers[0]['customer_id']);
    }
}
