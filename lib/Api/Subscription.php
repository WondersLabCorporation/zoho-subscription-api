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
     * @param array $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function createSubscription($data)
    {
        $response = $this->request('POST', 'subscriptions', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function updateSubscription($subscriptionId, $data)
    {
        $response = $this->request('PUT', sprintf('subscriptions/%s', $subscriptionId), [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function buyOneTimeAddonForASubscription($subscriptionId, $data)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/buyonetimeaddon', $subscriptionId), [
            'json' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param string $couponCode     The coupon's code
     *
     * @throws \Exception
     *
     * @return array
     */
    public function associateCouponToASubscription($subscriptionId, $couponCode)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/coupons/%s', $subscriptionId, $couponCode));

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     *
     * @return string
     */
    public function reactivateSubscription($subscriptionId)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/reactivate', $subscriptionId));

        return $this->processResponse($response);
    }

    /**
     * @param string $customerId The customer's id
     *
     * @return array
     */
    public function listSubscriptionsByCustomer($customerId)
    {
        $cacheKey = sprintf('zoho_subscriptions_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->request('GET', 'subscriptions', [
                'query' => ['customer_id' => $customerId],
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            $invoices = $result['subscriptions'];

            $this->saveToCache($cacheKey, $invoices);

            return $invoices;
        }

        return $hit;
    }
    
    /**
     *
     * @return iterator
     */
    public function listSubscriptions()
    {
        $page = 1;
        $subscriptions_result = [];
        do {
            $response = $this->request('GET', 'subscriptions',[
                'query' => ['page' => $page],
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            $subscriptions = $result['subscriptions'];

            foreach ($subscriptions as $value) {
                $subscriptions_result[] = $value;
            }
            
            if ($result['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
            
        } while ($nextPage);
        return $subscriptions_result;
    }
}
