<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;

/**
 * HostedPages.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#hosted-pages
 */
class HostedPages extends Record
{
    
    protected $command = 'hostedpages';
    protected $module = 'hostedpage';
    
    public $plan;
    public $addons;
    public $reference_id;
    public $starts_at;
    public $additional_param;
    public $redirect_url;
    public $customer_id;
    public $coupon_code;
    public $salesperson_name;
    public $custom_fields;
    public $subscription_id;
    public $url;
    public $contactpersons;
    
    protected function getUpdateMethod()
    {
        return 'POST';
    }
    
    protected function getId()
    {
        return $this->subscription_id;
    }
    
    protected function setId($id)
    {
        $this->subscription_id = $id;
    }
    
    protected function beforeSave(array &$data)
    {
        if (isset($data['subscription_id'])){
            unset($data['customer_id']);
        }
        if (isset($data['plan']) and !isset($data['plan']['plan_code'])){
            unset($data['plan']);
        }
        if (isset($data['addons'])){
            $ok = 0;
            foreach ($data['addons'] as $addon){
                $ok += isset($addon['addon_code']);
            }
            if(!$ok or $ok != len($data['addons'])){
                unset($data['addons']);
            }
        }
    }
    
    protected $base_template = [
        // dont work with contact persons, dont know why
//        'contactpersons' => [
//            '*' => ['contactperson_id',],
//        ],
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
        ],
        'addons'=> [
            '*' => [
                'addon_code',
                'quantity',
                'price',
            ],
        ],
        'reference_id',
        'starts_at',
        'additional_param',
        'redirect_url',
        
    ];
        
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, [
            'customer_id',
            'coupon_code',
            'salesperson_name',
            'custom_fields',
        ]);
    }
    
    protected function getUpdateTemplate()
    {
        return array_merge($this->base_template, [
            'subscription_id',
        ]);
    }
    
    protected function getCommandCreate()
    {
        return 'hostedpages/newsubscription';
    }
    
    protected function getCommandUpdate()
    {
        return 'hostedpages/updatesubscription';
    }
    
    protected function getCommandRetrieve()
    {
        if ($this['hostedpage_id'] === null){
            throw new Exception('hostedpage_id is not set');
        }
        return $this->command.'/'.$this['hostedpage_id'];
    }
    
     /**
     * Load Zoho record by ID
     * @param string|int $id
     * @return boolean
     */
    public function load($id = null)
    {
        if ($id !== null){
            $this->setId($id);
        }
        $response = $this->client->getRecord($this->getCommandRetrieve());
        if ($this->hasError()){
            return false;
        }
        $this->setAttributes($response[$this->module]);
        $this->setAttribute('url', $response['url']);
        return true;
    }

    /**
     * Create hosted page for updating card information for a subscription.
     * 
     * @param string $subscription_id Required
     * @param string $additional_param Optional
     * @param string $redirect_url Optional
     * @return null
     */
    public function updateCard($subscription_id, $additional_param = null, $redirect_url = null)
    {
        $data = ['subscription_id' => $subscription_id];
        if ($additional_param !== null){
            $data['additional_param'] = $additional_param;
        }
        if ($redirect_url){
            $data['redirect_url'] = $redirect_url;
        }
        $response = $this->request('POST', 'hostedpages/updatecard', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        $this->processResponseAndSave($response);
    }

    /**
     * Create hosted page for buying a one-time addon for a subscription.
     * 
     * @param iteger $subscription_id Required
     * @param string $addon_code Required
     * @param integer $quantity Optional
     * @param float $price Optional
     * @param string $additional_param Optional
     * @param string $redirect_url Optional
     * @return null
     */
    public function onetimeAddon($subscription_id, $addon_code, $quantity = null, $price = null, $additional_param = null, $redirect_url = null)
    {
        $data = ['subscription_id' => $subscription_id];
        $addon = ['addon_code' => $addon_code];
        if ($quantity !== null){
            $addon['quantity'] = $quantity;
        }
        if ($price !== null){
            $addon['price'] = $price;
        }
        $data['addons'] = [$addon,];
        if ($additional_param !== null){
            $data['additional_param'] = $additional_param;
        }
        if ($redirect_url){
            $data['redirect_url'] = $redirect_url;
        }
        $response = $this->request('POST', 'hostedpages/buyonetimeaddon', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        $this->processResponseAndSave($response);
    }
    
    /**
     * Create hosted page for buying a one-time addon for a subscription.
     * 
     * @param integer $subscription_id
     * @param array $addons
     * @param string $additional_param
     * @param string $redirect_url
     * @return null
     */
    public function onetimeAddons($subscription_id, $addons, $additional_param = null, $redirect_url = null)
    {
        $template = [
            'addon_code',
            'quantity',
            'price',
        ];
        $data = ['subscription_id' => $subscription_id];
        $data['addons'] = [];
        foreach ($addons as $value) {
            $data['addons'][] = $this->prepareData($value, $template);
        }
        if ($additional_param !== null){
            $data['additional_param'] = $additional_param;
        }
        if ($redirect_url){
            $data['redirect_url'] = $redirect_url;
        }
        $response = $this->request('POST', 'hostedpages/buyonetimeaddon', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        $this->processResponseAndSave($response);
    }
}
