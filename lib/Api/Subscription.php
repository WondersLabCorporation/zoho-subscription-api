<?php

namespace Zoho\Subscription\Api;

use Zoho\Subscription\Client\Client;

/**
 * Subscription.
 *
 * @author Elodie Nazaret <elodie@yproximite.com>
 *
 * @link https://www.zoho.com/subscriptions/api/v1/#subscriptions
 */
class Subscription extends Client
{
    /**
     * @param array $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function createSubscription($data)
    {
        $response = $this->request('POST', 'subscriptions', [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function updateSubscription($subscriptionId, $data)
    {
        $response = $this->request('PUT', sprintf('subscriptions/%s', $subscriptionId), [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return string
     */
    public function buyOneTimeAddonForASubscription($subscriptionId, $data)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/buyonetimeaddon', $subscriptionId), [
            'json' => json_encode($data),
        ]);

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     * @param string $couponCode     The coupon's code
     *
     * @throws \Exception
     *
     * @return array
     */
    public function associateCouponToASubscription($subscriptionId, $couponCode)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/coupons/%s', $subscriptionId, $couponCode));

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     *
     * @throws \Exception
     *
     * @return string
     */
    public function reactivateSubscription($subscriptionId)
    {
        $response = $this->request('POST', sprintf('subscriptions/%s/reactivate', $subscriptionId));

        return $this->processResponse($response);
    }

    /**
     * @param string $subscriptionId The subscription's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getSubscription($subscriptionId)
    {
        $cacheKey = sprintf('zoho_subscription_%s', $subscriptionId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->request('GET', sprintf('subscriptions/%s', $subscriptionId));

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            $subscription = $result['subscription'];

            $this->saveToCache($cacheKey, $subscription);

            return $subscription;
        }

        return $hit;
    }

    /**
     * @param string $customerId The customer's id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listSubscriptionsByCustomer($customerId)
    {
        $cacheKey = sprintf('zoho_subscriptions_%s', $customerId);
        $hit = $this->getFromCache($cacheKey);

        if (false === $hit) {
            $response = $this->request('GET', 'subscriptions', [
                'query' => ['customer_id' => $customerId],
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            $invoices = $result['subscriptions'];

            $this->saveToCache($cacheKey, $invoices);

            return $invoices;
        }

        return $hit;
    }
    
    /**
     *
     * @return iterator
     */
    public function listSubscriptions()
    {
        $page = 1;
        $subscriptions_result = [];
        do {
            $response = $this->request('GET', 'subscriptions',[
                'query' => ['page' => $page],
            ]);

            $result = $this->processResponse($response);
            if ($this->hasError()){
                return null;
            }
            $subscriptions = $result['subscriptions'];
 
            foreach ($subscriptions as $value) {
                $subscriptions_result[] = $value;
            }
            
            if ($result['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
            
        } while ($nextPage);
        return $subscriptions_result;
    }
}
