<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;


class Payment extends Record
{
    protected $command = 'payments';
    protected $module = 'payment';
    
    public $customer_id;
    public $payment_id;
    public $amount;
    public $date;
    public $payment_mode;
    public $description;
    public $reference_number;
    public $exchange_rate;
    public $invoices;
    
    protected $base_template = [
        'customer_id',
        'amount',
        'date',
        'payment_mode',
        'description',
        'reference_number',
        'exchange_rate',
        'invoices' => [
            'invoice_id',
            'amount_applied',
        ],
    ];
    
    /**
     * Delete an existing payment.
     * @return boolean
     */
    public function delete()
    {
        $this->client->saveRecord('DELETE', sprintf('payments/%s', $this->getId()));
        return !$this->hasError();
    }

}
