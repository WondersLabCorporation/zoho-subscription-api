<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;


class Coupon extends Client
{
    protected $command = 'coupons';
    protected $module = 'coupon';
    
    protected function getId()
    {
        return $this['coupon_code'];
    }
    
    protected function setId($id)
    {
        $this['coupon_code'] = $id;
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
     * @param string $coupon_code
     * @throws SubscriptionException
     */
    public function delete($coupon_code = null)
    {
        $coupon_code = empty($coupon_code) ? $this->getId() : $coupon_code;
        $response = $this->request('DELETE', sprintf('coupons/%s', $this['customer_id'], $coupon_code));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the coupon to active.
     * @param integer $coupon_code
     * @throws SubscriptionException
     */
    public function markActive($coupon_code = null)
    {
        $coupon_code = empty($coupon_code) ? $this>getId() : $coupon_code;
        $response = $this->request('POST', sprintf('coupons/%s/markasactive', $coupon_code));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the coupon to inactive.
     * @param integer $coupon_code
     * @throws SubscriptionException
     */
    public function markInactive($coupon_code = null)
    {
        $coupon_code = empty($coupon_code) ? $this>getId() : $coupon_code;
        $response = $this->request('POST', sprintf('coupons/%s/markasinactive', $coupon_code));
        $this->processResponse($response);
    }


}
