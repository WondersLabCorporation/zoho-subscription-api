<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;
use Zoho\Subscription\Common\Factory;

/**
 * Plan.
 *
 * @author Tristan Perchec <tristan.perchec@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#plans
 */
class Plan extends Record
{
    protected $command = 'plans';
    protected $module = 'plan';
    
    public $name;
    public $recurring_price;
    public $interval;
    public $interval_unit;
    public $billing_cycles;
    public $trial_period;
    public $setup_fee;
    public $product_id;
    public $tax_id;
    public $plan_code;
    public $end_of_term;
    public $prorate;
    public $price;
    public $status;
    public $description;
            
    protected function getId()
    {
        return $this->plan_code;
    }
    
    protected function setId($id)
    {
        $this->plan_code = $id;
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
     * Get reccurent addons for given plan.
     *
     * @return Record[]|null
     */
    public function getAddons()
    {
        
        $addons = Factory::getEntityList('Addon', [], $this->client, false);
        if ($addons === null) {
            return null;
        }
        return array_filter($addons, function($addon){
            foreach ($addon->plans as $plan){
                if ($plan['plan_code'] == $this->plan_code){
                    return true;
                }
            }
            return false;
        });
    }
    
    /**
     * Delete an existing plan.
     * @return bollean 
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('plans/%s', $this->getId()));
        return !$this->hasError();
    }

    /**
     * Change the status of the plan to active.
     * @return bollean 
     */
    public function markActive()
    {
        $this->client->saveRecord('POST', sprintf('plans/%s/markasactive', $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the plan to inactive.
     * @return bollean 
     */
    public function markInactive()
    {
        $this->client->saveRecord('POST', sprintf('plans/%s/markasinactive', $this->getId()));
        return !$this->hasError();
    }
}
