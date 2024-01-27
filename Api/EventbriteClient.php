<?php

namespace Modules\Eventbrite\Api;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Modules\Eventbrite\Api\Exception\EventbriteException;

class EventbriteClient
{
    protected string $secretKey;
    protected string $orgId;
    protected Client $client;

    /**
     * Constructor
     *
     * @param string $secretKey
     * @param string $orgId
     * @param array $config Options for Guzzle
     */
    public function __construct(Client $client, string $orgId)
    {
       $this->client = $client;
       $this->orgId = $orgId;
    }

    /**
     * Initialize a client.
     *
     * @param string $secretKey
     * @param string $orgId
     * @param array $config
     * @return EventbriteClient
     */
    public static function init(string $secretKey, string $orgId, array $config = [])
    {
      $config = array_merge($config, [
        'base_uri' => 'https://www.eventbriteapi.com/v3/',
        'headers' => [
          'Authorization' => 'Bearer ' . $secretKey
        ]
        ]);

      return new self(new Client($config), $orgId);
    }

    public function __call($method, $args) {
      try {
        return $this->client->{$method}(...$args);
      } catch (ClientException $e) {
        throw new EventbriteException($e);
      }
    }

    /**
     * Check that values provided are correct.
     *
     * @return bool
     */
    public function checkConnection(): bool
    {
      $response = $this->get("organizations/{$this->orgId}/members");

      return $response->getStatusCode() === 200;
    }

    /**
     * Get customer orders
     *
     * @param string $email
     * @return Response
     */
    public function getCustomerOrders($email): Response {
      $response = $this->get("organizations/{$this->orgId}/orders/search", [
        'query' => [
          'expand' => 'event,attendees',
          'search_term' => $email
        ]
      ]);

      return $response;
    }

    /**
     * Get customer tickets
     *
     * @param string $email
     * @return Response
     */
    public function getCustomerTickets($email): Response {
      $response = $this->post("attendees/search/", [
        'query' => [
          'expand' => 'event,order',
        ],
        'json'=> [
          'organization_ids' => [$this->orgId],
          'query' => $email,
          'created_after' => Carbon::now()->subYear(1)->toIso8601ZuluString()
        ],
      ]);

      return $response;
    }


}
