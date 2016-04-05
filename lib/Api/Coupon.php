<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Coupon extends Record
{
    protected $command = 'coupons';
    protected $module = 'coupon';
    
    public $coupon_code;
    public $name;
    public $description;
    public $max_redemption;
    public $expiry_at;
    public $type;
    public $discount_by;
    public $discount_value;
    public $product_id;
    public $apply_to_plans;
    public $plans;
    public $apply_to_addons;
    public $addons;

    protected function getId()
    {
        return $this->coupon_code;
    }
    
    protected function setId($id)
    {
        $this->coupon_code = $id;
    }
    
    protected $base_template = [
        'name',
        'description',
        'max_redemption',
        'expiry_at',
    ];
    
    protected function getCreateTemplate()
    {
        return array_merge($this->base_template, [
            'coupon_code',
            'type',
            'discount_by',
            'discount_value',
            'product_id',
            'apply_to_plans',
            'plans' => [
                'plan_code',
            ],
            'apply_to_addons',
            'addons' => [
                'addon_code'
            ],
        ]);
    }
    
    /**
     * Delete an existing coupon.
     * @return boolean
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('coupons/%s', $this->customer_id, $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the coupon to active.
     * @return boolean
     */
    public function markActive()
    {
        $this->client->saveRecord('POST', sprintf('coupons/%s/markasactive', $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the coupon to inactive.
     * @return boolean
     */
    public function markInactive()
    {
        $this->client->saveRecord('POST', sprintf('coupons/%s/markasinactive', $this->getId()));
        return !$this->hasError();
    }


}
