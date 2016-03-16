<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * HostedPages.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#hosted-pages
 */
class HostedPages extends Client
{
    /**
     * Details of a specific hosted page.
     * 
     * @param integer $hostedageID
     * @param array $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getHostedPage($hostedageID, $data)
    {
        $response = $this->request('POST', sprintf('hostedpages/%s', $hostedageID), [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        
        return $this->processResponse($response);
    }
    
    /**
     * Create a hosted page for a new subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function createSubscription($data)
    {
        $response = $this->request('POST', 'hostedpages/newsubscription', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        
        $result = $this->processResponse($response);
        if ($this->hasError()) {
            return null;
        }
        return $result['hostedpage'];

    }
    
    /**
     * Create hosted page for updating a subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function updateSubscription($data)
    {
        $response = $this->request('POST', 'hostedpages/updatesubscription', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        $result = $this->processResponse($response);
        if ($this->hasError()) {
            return null;
        }
        return $result['hostedpage'];
    }
    
    /**
     * Create hosted page for updating card information for a subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function updateCard($data)
    {
        $response = $this->request('POST', 'hostedpages/updatecard', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }
    
    /**
     * Create hosted page for buying a one-time addon for a subscription.
     * 
     * @param array $data
     *
     * @return string
     */
    public function onetimeAddon($data)
    {
        $response = $this->request('POST', 'hostedpages/buyonetimeaddon', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }
}
