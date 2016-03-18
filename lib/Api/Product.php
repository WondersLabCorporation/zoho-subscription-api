<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;


class Product extends Client
{
    protected $command = 'products';
    protected $module = 'product';
    
    protected $base_template = [
        'name',
        'description',
        'email_ids',
        'redirect_url',
    ];
    
    /**
     * Delete an existing product.
     * @param string $product_id
     * @throws SubscriptionException
     */
    public function delete($product_id = null)
    {
        $product_id = empty($product_id) ? $this->getId() : $product_id;
        $response = $this->request('DELETE', sprintf('coupons/%s', $this['customer_id'], $product_id));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the product to active.
     * @param integer $product_id
     * @throws SubscriptionException
     */
    public function markActive($product_id = null)
    {
        $product_id = empty($product_id) ? $this>getId() : $product_id;
        $response = $this->request('POST', sprintf('coupons/%s/markasactive', $product_id));
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the product to inactive.
     * @param integer $product_id
     * @throws SubscriptionException
     */
    public function markInactive($product_id = null)
    {
        $product_id = empty($product_id) ? $this>getId() : $product_id;
        $response = $this->request('POST', sprintf('coupons/%s/markasinactive', $product_id));
        $this->processResponse($response);
    }


}
