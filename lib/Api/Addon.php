<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;

/**
 * Addon.
 *
 * @author Tristan Perchec <tristan.perchec@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#addons
 */
class Addon extends Record
{
    protected $command = 'addons';
    protected $module = 'addon';
    
    public $plans;
    public $name;
    public $unit_name;
    public $pricing_scheme;
    public $price_brackets;
    public $type;
    public $interval_unit;
    public $applicable_to_all_plans;
    public $description;
    public $tax_id;
    public $addon_code;
    public $product_id;


    protected function getId()
    {
        return $this->addon_code;
    }
    
    protected function setId($id)
    {
        $this->addon_code = $id;
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
     * Delete an existing addon.
     * @return bollean 
     * 
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('addons/%s', $this->getId()));
        return !$this->hasError();
    }

    /**
     * Change the status of the addon to active.
     * @return bollean 
     */
    public function markActive()
    {
        $this->client->saveRecord('POST', sprintf('addons/%s/markasactive', $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the addon to inactive.
     * @return bollean 
     */
    public function markInactive()
    {
        $this->client->saveRecord('POST', sprintf('addons/%s/markasinactive', $this->getId()));
        return !$this->hasError();
    }
}
