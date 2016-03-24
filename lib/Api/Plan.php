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
        return $this->buildEntitiesFromArray($result);
    }

    /**
     * Get reccurent addons for given plan.
     *
     * @return array
     * @throws SubscriptionException
     */
    public function getAddons()
    {
        $addonApi = new Addon($this->subscriptionsToken, $this->organizationId, $this->cache, $this->ttl);
        $result = $addonApi->getList();
        $addons = $this->buildEntitiesFromArray($result, 'Addon');
        return array_filter($addons, function($addon){
            foreach ($addon['plans'] as $plan){
                if ($plan['plan_code'] == $this['plan_code']){
                    return true;
                }
            }
            return false;
        });
    }
    
    /**
     * Delete an existing plan.
     * @param string $plan_code
     * @throws SubscriptionException
     */
    public function delete($plan_code = null)
    {
        if (empty($plan_code)){
            $plan_code = $this['plan_code'];
        }
        $response = $this->request('DELETE', sprintf('plans/%s', $this->getId()));
        $this->processResponse($response);
    }
    
    /**
     * List of all plans created for a particular product.
     * @param integer $product_id
     * @return array
     * @throws SubscriptionException
     */
    public function getListByProduct($product_id)
    {
        $result = parent::getList(['product_id' => $product_id]);
        return $this->buildEntitiesFromArray($result);
    }

    /**
     * Change the status of the plan to active.
     * @throws SubscriptionException
     */
    public function markActive()
    {
        $response = $this->request('POST', sprintf('plans/%s/markasactive', $this->getId()));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the plan to inactive.
     * @throws SubscriptionException
     */
    public function markInactive()
    {
        $response = $this->request('POST', sprintf('plans/%s/markasinactive', $this->getId()));
        $this->processResponse($response);
    }
}
