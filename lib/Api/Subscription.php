<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * Subscription.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#subscriptions
 */
class Subscription extends Client
{
    protected $command = 'subscriptions';
    protected $module = 'subscription';
    
    protected function beforPrepareData(array &$data)
    {
        if (isset($data['card_id'], $data['card']))
        {
            unset($data['card']);
        }
        if (isset($data['card'])){
            
            $card = $data['card'];
            if (!isset($card['card_number'], 
                $card['cvv_number'], 
                $card['expiry_month'],
                $card['expiry_year'],
                $card['payment_gateway'],
                $card['street'],
                $card['city'],
                $card['state'],
                $card['zip'],
                $card['country']
            )){
                $this->warnings[] = "You must fill all required fields for a 'Card'";
                unset($data['card']);
            }
        }
        if (isset($data['plan']) and !isset($data['plan']['plan_code'])){
            $data->warnings[] = "You must fill 'plan_code' field for a 'Plan'";
            unset($data['plan']);
        }
        if (isset($data['addons'])){
            $ok = 0;
            foreach ($data['addons'] as $addon){
                $ok += isset($addon['addon_code']);
            }
            if(!$ok or $ok != len($data['addons'])){
                $this->warnings[] = "You must fill 'addon_code' field for all 'Addons'";
                unset($data['addons']);
            }
        }
    }
    
    protected $base_template = [
        // dont work with contact persons, dont know why
//        'contactpersons' => [
//            '*' => ['contactperson_id',],
//        ],
        'card_id',
        'card' => [
            'card_number',
            'cvv_number',
            'expiry_month',
            'expiry_year',
            'payment_gateway',
            'first_name',
            'last_name',
            'street',
            'city',
            'state',
            'zip',
            'country',
        ],
        'exchange_rate',
        'plan' => [
            'plan_code',
            'quantity',
            'price',
            'plan_description',
            'exclude_trial',
            'exclude_setup_fee',
            'trial_days',
            'setup_fee',
            'billing_cycles',
            'tax_id',
            'setup_fee_tax_id',
        ],
        'addons'=> [
            '*' => [
                'addon_code',
                'quantity',
                'price',
                'tax_id',
            ],
        ],
        'reference_id',
    ];
    
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, [
            'customer_id',
            'coupon_code',
            'auto_collect',
            'starts_at',
            'salesperson_name',
            'custom_fields',
        ]);
    }
    
    protected function getUpdateTemplate()
    {
        return array_merge($this->base_template, [
            'end_of_term',
            'prorate',
        ]);
    }
    
    /**
     * Include a one-time addon in the subscription.
     * 
     * @param string $addon_code Required
     * @param integer $quantity Optional
     * @param float $price Optional
     * @param float $tax_id Optional
     * @param float $exchange_rate Optional
     * @return boolean
     */
    public function buyOneTimeAddon($addon_code, $quantity = null, $price = null, $tax_id = null, $exchange_rate = null)
    {
        
        $addon = ['addon_code' => $addon_code];
        if ($quantity !== null){
            $addon['quantity'] = $quantity;
        }
        if ($price !== null){
            $addon['price'] = $price;
        }
        if ($tax_id !== null){
            $addon['tax_id'] = $tax_id;
        }
        $request_data = ['addons' => [
                $addon,
            ],
        ];
        if ($exchange_rate !== null){
            $request_data['exchange_rate'] = $exchange_rate;
        }
        $response = $this->request('POST', sprintf('subscriptions/%s/buyonetimeaddon', $this->getId()), [
            'json' => json_encode($request_data),
        ]);
        $this->processResponseAndSave($response);
    }

    /**
     * Include a one-time addons in the subscription.
     * 
     * @param array $addons ['addon_code' (required), 'quantity' (optional), 'price' (optional), 'tax_id' (optional)]
     * @param float $exchange_rate Oprional
     * @return boolean
     */
    public function buyOneTimeAddons(array $addons, $exchange_rate = null)
    {
        $template = [
            'addon_code',
            'quantity',
            'price',
            'tax_id',
        ];
        $request_data = ['addons' => []];
        foreach ($addons as $value) {
            $request_data['addons'][] = $this->prepareData($value, $template);
        }
        if ($exchange_rate !== null){
            $request_data['exchange_rate'] = $exchange_rate;
        }
        $response = $this->request('POST', sprintf('subscriptions/%s/buyonetimeaddon', $this->getId()), [
            'json' => json_encode($request_data),
        ]);
        $this->processResponseAndSave($response);
    }
    
    /**
     * Apply a coupon to a subscription which has been already created.
     * 
     * @param string $coupon_code
     * @return null
     */
    public function associateCoupon($coupon_code)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/coupons/%s', $this->getId(), $coupon_code));
        $this->processResponseAndSave($response);
    }

    /**
     * @param string $customer_id The customer's id
     *
     * @return array Array of Subscription objects
     */
    public function getListByCustomer($customer_id = null)
    {
        if ($customer_id === null){
            $customer_id = $this['customer']['customer_id'];
        }
        if ($customer_id === null){
            return null;
        }
        $page = 1;
        $subscriptions = [];
        do {
            $response = $this->request('GET', 'subscriptions',[
                'query' => ['customer_id' => $customer_id, 'page' => $page],
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            foreach ($result['subscriptions'] as $value) {
                $subscription = self::createEntity('Subscription', $this, [$this->cache, $this->ttl]);
                $subscription[] = $value;
                array_push($subscriptions, $subscription);
            }

            if ($result['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
            
        } while ($nextPage);
        return $subscriptions;
    }
    
    /**
     * @param string $customer_id The customer's id
     * 
     * @return array Array of Subscription objects
     */
    public function getList($customer_id = null)
    {
        $page = 1;
        $subscriptions = [];
        $query = [];
        if ($customer_id !== null){
            $query += ['customer_id' => $customer_id];
        }
        do {
            $response = $this->request('GET', 'subscriptions',[
                'query' => ($query + ['page' => $page]),
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            
            foreach ($result['subscriptions'] as $value) {
                $subscription = self::createEntity('Subscription', $this, [$this->cache, $this->ttl]);
                $subscription[] = $value;
                array_push($subscriptions, $subscription);
            }

            if ($result['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
            
        } while ($nextPage);
        return $subscriptions;
    }
}
