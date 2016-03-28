<?php

namespace Zoho\Subscription\Client;

use yii\base\InvalidConfigException;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use \Zoho\Subscription\Common\SubscriptionException;

class Client
{
    /**
     * @var String
     */
    protected $subscriptionsToken;

    /**
     * @var String
     */
    protected $organizationId;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var string
     */
    protected $error;

    /**
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        if (empty($params['subscriptionsToken']) || empty($params['organizationId'])){
            throw new \Exception('Token and Organization ID are required');
        }
        $this->subscriptionsToken = $params['subscriptionsToken'];
        $this->organizationId = $params['organizationId'];
        $this->client = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Zoho-authtoken '.$this->subscriptionsToken,
                'X-com-zoho-subscriptions-organizationid' => $this->organizationId,
            ],
            'base_uri' => 'https://subscriptions.zoho.com/api/v1/',
        ]);
    }
    
    /**
     * @param Response $response
     * @throws SubscriptionException
     *
     * @return array
     */
    protected function processResponse(Response $response = null)
    {
        if ($this->error){
              return null;
        }
        if ($response === null) {
            $this->error = 'Zoho Api subscription error : null data in processResponse';
            return null;
        } 
        if ($response->getStatusCode() > 201) {
            $this->error = 'Zoho Api subscription error : '.$response->getReasonPhrase();
            return null;
        }
        $data = json_decode($response->getBody(), true);
        if ($data['code'] != 0) {
            $this->error = 'Zoho Api subscription error : '.$data['message'];
            return null;
        }
        return $data;
    }
    
    /**
     * @param $key
     *
     * @throws \LogicException
     *
     * @return bool|mixed
     */
    protected function getFromCache($key)
    {
        // If the results are already cached
        if ($this->cache !== null and $this->cache->contains($key)) {
            return unserialize($this->cache->fetch($key));
        }

        return false;
    }

    /**
     * @param string $key
     * @param mixed  $values
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function saveToCache($key, $values)
    {
        if ($this->cache === null){
            return true;
        }
        if (null === $key) {
            throw new \LogicException('If you want to save to cache, an unique key must be set');
        }

        return $this->cache->save($key, serialize($values), $this->ttl);
    }

    /**
     * @param string $key
     * 
     * @return boolean
     */
    protected function deleteCacheByKey($key)
    {
        if ($this->cache === null){
            return true;
        }
        return $this->cache->delete($key);
    }
    
    public function saveRecord($method, $command, $data = [])
    {
        $response = $this->request($method, $command, [
            'content-type' => 'application/json',
            'body' => json_encode($data),
        ]);
        return $this->processResponse($response);
    }

    /**
     * Return data of single Entity from Zoho
     * @param array $query
     * @param integer $page
     * @param string $command
     * @return array
     * @throws SubscriptionException
     */
    public function getRecord($command, array $query = [], $page = null)
    {
        $cacheKey = sprintf('zoho_%s_%s_%s', $command, implode('', array_keys($query)), $page);
        
        $hit = $this->getFromCache($cacheKey);
        
        if ($hit === false){
            if ($page !== null){
                $query['page'] = $page;
            }
            $command = empty($command) ? $command : $command;
            $response = $this->request('GET', $command, [
                'content-type' => 'application/json',
                'query' => $query,
            ]);
            
            $result = $this->processResponse($response);
            
            $this->saveToCache($cacheKey, $result);
            
            return $result;
        }
        
        return $hit;
    }
    
    /**
     * Returns list of all Entities from Zoho
     * @param array $query Custom query
     * @param string $command
     * @return array
     * @throws SubscriptionException
     */
    public function getList($command, array $query = []){
        $page = 1;
        $result = [];
        do {
            $page_data = $this->getRecord($command, $query, $page);
            if ($this->hasError()){
                return null;
            }
            
            $result = array_merge($result, $page_data[$command]);
            
            if ($page_data['page_context']['has_more_page']){
                $page++;
                $nextPage = $page;
            } else {
                $nextPage = false;
            }
        } while($nextPage);
        return $result;
    }
    
    /**
     * Non exception wrapper for Guzzle client
     * 
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return Response
     */
    protected function request($method, $uri = null, array $options = [])
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch(\Exception $e){
            $this->error = $e->getMessage();
        }
    }
     
    public function hasError()
    {
        return !empty($this->error);
    }
    
    public function getError()
    {
        return $this->error;
    }
}
