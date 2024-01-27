<?php

namespace Modules\Eventbrite\Api;

use Illuminate\Support\Facades\Cache;
use Modules\Eventbrite\Api\EventbriteClient;

class Eventbrite
{
    protected string $orgId, $secretKey;
    protected EventbriteClient $eventbrite;

    protected $cacheExpiryTime = '';
    public function __construct(string $secretKey, string $orgId)
    {
        $this->secretKey = $secretKey;
        $this->orgId = $orgId;
        $this->cacheExpiryTime = config('eventbrite.cache_expiry_time', 60);
        $this->eventbrite = EventbriteClient::init($this->secretKey, $this->orgId);
    }

    public function clearCache($email) {
      Cache::forget("eventbrite_orders_{$this->orgId}_{$email}");
      Cache::forget("eventbrite_tickets_{$this->orgId}_{$email}");
    }


    /**
     * get orders of user
     * @param mixed $email
     * @return mixed
     */
    public function getOrders($email)
    {
            $key = "eventbrite_orders_{$this->orgId}_{$email}";

            if (Cache::has($key)) {
                return Cache::get($key);
            } else {
                $response = $this->eventbrite->getCustomerOrders($email);
                $orders = json_decode($response->getBody());
                Cache::put($key, $orders, $this->cacheExpiryTime);

                return $orders;
            }
    }

    /**
     * Get tickets of user
     * @param mixed $email
     * @return mixed
     */
    public function getTickets($email)
    {
            $key = "eventbrite_tickets_{$this->orgId}_{$email}";

            if (Cache::has($key)) {
                return Cache::get($key);
            } else {
                $response = $this->eventbrite->getCustomerTickets($email);
                $tickets = json_decode($response->getBody());

                Cache::put($key, $tickets, $this->cacheExpiryTime);

                return $tickets;
            }
    }

    public function getUrls($email)
    {
      return [
        'orders' =>
            "https://www.eventbrite.com/organizations/orders?" .
            http_build_query([
              'q' => $email,
              'by' => 'buyer',
              'months' => '12'
            ]),
        'tickets' =>
            "https://www.eventbrite.com/organizations/orders?" .
            http_build_query([
              'q' => $email,
              'by' => 'attendee',
              'months' => '12'
            ]),
        ];
    }
}
