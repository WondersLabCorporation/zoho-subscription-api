<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;


class Payment extends Client
{
    protected $command = 'payments';
    protected $module = 'payment';
    
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
     * @param string $payment_id
     * @throws SubscriptionException
     */
    public function delete($payment_id = null)
    {
        $payment_id = empty($payment_id) ? $this->getId() : $payment_id;
        $response = $this->request('DELETE', sprintf('plans/%s', $payment_id));
        $this->processResponse($response);
    }

}
