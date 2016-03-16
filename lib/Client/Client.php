<?php

namespace Zoho\Subscription\Client;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

class Client
{
    /**
     * @var String
     */
    protected $token;

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
     * @param string                            $token
     * @param int                               $organizationId
     * @param \Doctrine\Common\Cache\Cache|null $cache
     * @param int                               $ttl
     */
    public function __construct($token, $organizationId, Cache $cache = null, $ttl = 7200)
    {
        $this->token = $token;
        $this->organizationId = $organizationId;
        $this->ttl = $ttl;
        $this->cache = $cache;
        $this->client = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Zoho-authtoken '.$token,
                'X-com-zoho-subscriptions-organizationid' => $organizationId,
            ],
            'base_uri' => 'https://subscriptions.zoho.com/api/v1/',
        ]);
    }

    /**
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function processResponse(Response $response=null)
    {
        if ($response === null){
            $this->error = 'Zoho Api subscription error : null data in processResponse';
            return null;
        }
        if ($response->getStatusCode() > 201){
            $this->error = 'Zoho Api subscription error : '.$response->getReasonPhrase();
            return null;
        }
        $data = json_decode($response->getBody(), true);
        if ($data['code'] != 0) {
            $this->setError('Zoho Api subscription error : '.$data['message']);
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
    public function getFromCache($key)
    {
        // If the results are already cached
        if ($this->cache and $this->cache->contains($key)) {
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
    public function saveToCache($key, $values)
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
     */
    public function deleteCacheByKey($key)
    {
        if ($this->cache === null){
            return true;
        }
        $this->cache->delete($key);
    }
    
    /**
     * @return boolean
     */
    public function hasError()
    {
        return !empty($this->error);
    }
    
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
   
    /**
     * Non exception wrapper for client->request
     */
    public function request($method, $uri = null, array $options = [])
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch(\Exception $e){
            $this->error = $e->getMessage();
        }
    }
}
