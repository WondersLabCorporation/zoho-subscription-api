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
    
     /**
     * Returns all Invoices as objects.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function getList() {
        $result = parent::getList();
        return $this->buildEntitiesFromArray($result);
    }
    
    
     /**
     * Returns all Invoices as objects by customer_id.
     * 
     * @return array
     * @throws SubscriptionException
     */
    public function listByCustomer($customer_id)
    {
        $result = parent::getList(['customer_id' => $customer_id]);
        return $this->buildEntitiesFromArray($result);
    }

    /**
     * @param string $invoiceId The invoice's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getInvoicePdf($invoiceId)
    {
        $response = $this->client->request('GET', sprintf('invoices/%s', $invoiceId), [
            'query' => ['accept' => 'pdf'],
        ]);

        return $response;
    }
    
    /**
     * Charge a customer for an invoice.
     * @param integer $invoice_id
     * @throws SubscriptionException
     */
    public function collect($invoice_id = null)
    {
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/collect', $invoice_id), [
            'content-type' => 'application/json',
        ]);
        $this->processResponse($response);
    }
    
    /**
     * Making an invoice void.
     * @param integer $invoice_id
     * @throws SubscriptionException
     */
    public function convertVoid($invoice_id = null)
    {
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/void', $invoice_id), [
            'content-type' => 'application/json',
        ]);
        $this->processResponse($response);
    }
    
    /**
     * Change the status of the invoice to open.
     * @param integer $invoice_id
     * @throws SubscriptionException
     */
    public function convertOpen($invoice_id = null)
    {
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/convertoopen', $invoice_id), [
            'content-type' => 'application/json',
        ]);
        $this->processResponse($response);
    }
    
    /**
     * Write off a payment.
     * @param integer $invoice_id
     * @throws SubscriptionException
     */
    public function writeOff($invoice_id = null)
    {
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/writeoff', $invoice_id), [
            'content-type' => 'application/json',
        ]);
        $this->processResponse($response);
    }
    
    /**
     * Revert write off performed for a payment
     * @param integer $invoice_id
     * @throws SubscriptionException
     */
    public function cancelWriteOff($invoice_id = null)
    {
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/cancelwriteoff', $invoice_id), [
            'content-type' => 'application/json',
        ]);
        $this->processResponse($response);
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
     * @throws SubscriptionException
     */
    public function emailInvoice($from_mail_id, array $to_mail_ids, array $cc_mail_ids, $subject, $body, $invoice_id = null)
    {
        $request = [];
        $request['from_mail_id'] = $from_mail_id;
        $request['to_mail_ids'] = $to_mail_ids;
        $request['cc_mail_ids'] = $cc_mail_ids;
        $request['subject'] = $subject;
        $request['body'] = $body;
        $invoice_id = empty($invoice_id) ? $this->getId() : $invoice_id;
        $response = $this->request('POST', sprintf('invoices/%s/email', $invoice_id), [
            'content-type' => 'application/json',
            'body' => $request,
        ]);
        $this->processResponse($response);
    }
}
