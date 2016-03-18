<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * Addon.
 *
 * @author Tristan Perchec <tristan.perchec@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#addons
 */
class Addon extends Client
{
    protected $command = 'addons';
    protected $module = 'addon';
    
    protected function getId()
    {
        return $this['addon_code'];
    }
    
    protected function setId($id)
    {
        $this['addon_code'] = $id;
    }
    
    protected $base_template = [
        'name',
        'unit_name',
        'pricing_scheme',
        'price_brackets' => [
            '*' => [
                'start_quantity',
                'end_quantity',
                'price',
            ],
        ],
        'type',
        'interval_unit',
        'applicable_to_all_plans',
        'plans' => [
            '*' => [
                'plan_code',
            ],
        ],
        'description',
        'tax_id',
    ];
    
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, [
            'addon_code',
            'product_id',
        ]);
    }
    
    /**
     * Returns all addons as objects.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function getList() {
        $result = parent::getList();
        return $this->buildEntitiesFromArray($result);
    }
    
    /**
     * @param array $filters associative array of filters
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listAddons($filters = [])
    {
        $cacheKey = 'addons';
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->client->request('GET', $cacheKey);

            $addons = $this->processResponse($response);
            $hit = $addons['addons'];

            $this->saveToCache($cacheKey, $hit);
        }

        foreach ($filters as $key => $filter) {
            if (array_key_exists($key, current($hit))) {
                $hit = array_filter($hit, function ($element) use ($key, $filter) {
                    return $element[$key] == $filter;
                });
            }
        }

        return $hit;
    }
    
    /**
     * Delete an existing addon.
     * @param string $addon_code
     * @throws SubscriptionException
     */
    public function delete($addon_code = null)
    {
        if (empty($addon_code)){
            $addon_code = $this['addon_code'];
        }
        $response = $this->request('DELETE', sprintf('addons/%s', $this->getId()));
        $this->processResponse($response);
    }

    /**
     * Change the status of the addon to active.
     * @throws SubscriptionException
     */
    public function markActive()
    {
        $response = $this->request('POST', sprintf('addons/%s/markasactive', $this->getId()));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the addon to inactive.
     * @throws SubscriptionException
     */
    public function markInactive()
    {
        $response = $this->request('POST', sprintf('addons/%s/markasinactive', $this->getId()));
        $this->processResponse($response);
    }
}
