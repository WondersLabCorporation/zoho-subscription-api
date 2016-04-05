<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Common\Record;

/**
 * @author Hang Pham <thi@yproximite.com>
 * @author Tristan Bessoussa <tristan.bessoussa@gmail.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#invoices
 */
class Invoice extends Record
{
    protected $command = 'invoices';
    protected $module = 'invoice';
    protected $base_template = null;
    
    public $invoice_id;
    public $number;
    public $status;
    public $invoice_date;
    public $due_date;
    public $customer_id;
    public $customer_name;
    public $email;
    public $invoice_items;
    public $coupons;
    public $total;
    public $payment_made;
    public $credits_applied;
    public $write_off_amount;
    public $payments;
    public $currency_code;
    public $currency_symbol;
    public $from_mail_id;
    public $to_mail_ids;
    public $cc_mail_ids;
    public $subject;
    public $body;
    

    /**
     * @param string $invoiceId The invoice's id
     * @return array
     */
    public function getInvoicePdf()
    {
        $response = $this->client->getRecord(sprintf('invoices/%s', $this->getId()), [
            'query' => ['accept' => 'pdf'],
        ]);
        return $response;
    }
    
    /**
     * Charge a customer for an invoice.
     * @return boolean
     */
    public function collect()
    {
        $response = $this->client->saveRecord('POST', sprintf('invoices/%s/collect', $this->getId()));
        return $this->processResponse($response);
    }
    
    /**
     * Making an invoice void.
     * @return boolean
     */
    public function convertVoid()
    {
        $response = $this->client->saveRecord('POST', sprintf('invoices/%s/void', $this->getId()));
        return $this->processResponse($response);
    }
    
    /**
     * Change the status of the invoice to open.
     * @return boolean
     */
    public function convertOpen()
    {
        $response = $this->request('POST', sprintf('invoices/%s/convertoopen', $this->getId()));
        return $this->processResponse($response);
    }
    
    /**
     * Write off a payment.
     * @return boolean
     */
    public function writeOff()
    {
        $response = $this->client->saveRecord('POST', sprintf('invoices/%s/writeoff', $this->getId()));
        return $this->processResponse($response);
    }
    
    /**
     * Revert write off performed for a payment
     * @return boolean
     */
    public function cancelWriteOff($invoice_id = null)
    {
        $response = $this->client->saveRecord('POST', sprintf('invoices/%s/cancelwriteoff', $this->getId()));
        return $this->processResponse($response);
    }
    
    /**
     * Email an invoice.
     * 
     * The email ID from which the invoice is to be mailed.
     * @param email $from_mail_id
     * The email IDs to which the invoice is to be mailed.
     * @param array $to_mail_ids
     * The email IDs that have to be copied when the invoice is to be mailed.
     * @param array $cc_mail_ids
     * Subject of the email.
     * @param string $subject 
     * Body of the email.
     * @param string $body 
     * Invoice Id
     * @param integer $invoice_id
     * @return boolean
     */
    public function emailInvoice($from_mail_id, array $to_mail_ids, array $cc_mail_ids, $subject, $body)
    {
        $request = [];
        $request['from_mail_id'] = $from_mail_id;
        $request['to_mail_ids'] = $to_mail_ids;
        $request['cc_mail_ids'] = $cc_mail_ids;
        $request['subject'] = $subject;
        $request['body'] = $body;
        $response = $this->client->saveRecord('POST', sprintf('invoices/%s/email', $this->getId()), $request);
        return $this->processResponse($response);
    }
}
