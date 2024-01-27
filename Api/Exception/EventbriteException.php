<?php

namespace Modules\Eventbrite\Api\Exception;

use GuzzleHttp\Exception\ClientException;

class EventbriteException extends ClientException implements \Throwable  {

  private \StdClass $jsonBody;
  protected ClientException $originalException;


  public function __construct(ClientException $e) {
    $this->originalException = $e;
  }

  public function getJsonBody() {
    if (!$this->originalException->hasResponse()) {
      return null;
    }

    return $this->jsonBody ?? $this->jsonBody = json_decode($this->originalException->getResponse()->getBody());
  }

  public function getErrorDescription(): string {
    $json = $this->getJsonBody();
    if (empty($json)) {
      return $this->originalException->getMessage();
    } else {
      return $json->error_description;
    }
  }


  public function getOriginal() {
    return $this->originalException;
  }
}