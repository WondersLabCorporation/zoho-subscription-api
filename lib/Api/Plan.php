<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * Plan.
 *
 * @author Tristan Perchec <tristan.perchec@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#plans
 */
class Plan extends Client
{
    protected $command = 'plans';
    protected $module = 'plan';
    
    protected function getId()
    {
        return $this['plan_code'];
    }
    
    protected function setId($id)
    {
        $this['plan_code'] = $id;
    }
    
    protected $base_template = [
        'name',
        'recurring_price',
        'interval',
        'interval_unit',
        'billing_cycles',
        'trial_period',
        'setup_fee',
        'product_id',
        'tax_id',
    ];
    
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, [
            'plan_code',
        ]);
    }
    
    protected function getUpdateTemplate()
    {
        return array_merge($this->base_template, [
            'end_of_term',
            'prorate',
        ]);
    }
    
    public static $addonTypes = [
        'recurring',
        'one_time',
    ];

    /**
     * Returns all plans as objects.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function getList() {
        $result = parent::getList();
        $plans = [];
        foreach ($result as $value) {
            $plan = self::createEntity('Plan', $this, [$this->cache, $this->ttl]);
            $plans[] = $value;
            array_push($plans, $plan);
        }
        return $plans;
    }

    /**
     * get reccurent addons for given plan.
     *
     * @return array
     * @throws SubscriptionException
     */
    public function getAddons()
    {
        $addonApi = self::createEntity('Addon', $this, [$this->cache, $this->ttl]);
        $result = $addonApi->getList();
        $addons = [];
        foreach ($result as $value) {
            foreach ($value['plans'] as $plan) {
                if ($plan['plan_code'] == $this['plan_code']){
                    $addon = self::createEntity('Addon', $this, [$this->cache, $this->ttl]);
                    $addon[] = $result;
                    $addons[] = $addon;
                    break;
                }
            }
        }
        return $addons;
    }
}
