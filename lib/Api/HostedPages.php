<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * HostedPages.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#hosted-pages
 */
class HostedPages extends Client
{
    
    protected $command = 'hostedpages';
    protected $module = 'hostedpage';
    
    protected function getUpdateMethod()
    {
        return 'POST';
    }
    
    protected function getId()
    {
        return $this['subscription_id'];
    }
    
    protected function setId($id)
    {
        $this['subscription_id'] = $id;
    }
    
    protected function beforPrepareData(array &$data)
    {
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
     * Create hosted page for updating card information for a subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function updateCard($data)
    {
        $response = $this->request('POST', 'hostedpages/updatecard', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }
    
    /**
     * Create hosted page for buying a one-time addon for a subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function onetimeAddon($data)
    {
        $response = $this->request('POST', 'hostedpages/buyonetimeaddon', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }
}
