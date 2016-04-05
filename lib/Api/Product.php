<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Product extends Record
{
    protected $command = 'products';
    protected $module = 'product';
    
    public $product_id;
    public $name;
    public $description;
    public $email_ids;
    public $redirect_url;
    public $status;
    public $created_time;
    public $updated_time;

    protected $base_template = [
        'name',
        'description',
        'email_ids',
        'redirect_url',
    ];
    
    /**
     * Delete an existing product.
     * @return boolean
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('products/%s', $this->customer_id, $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the product to active.
     * @return boolean
     */
    public function markActive()
    {
        $this->client->saveRecord('POST', sprintf('products/%s/markasactive', $this->getId()));
        return !$this->hasError();
    }
    
    /**
     * Change the status of the product to inactive.
     * @return boolean
     */
    public function markInactive()
    {
        $this->client->saveRecord('POST', sprintf('products/%s/markasinactive', $this->getId()));
        return !$this->hasError();
    }


}
