<?php

namespace Modules\Eventbrite\Entities;

class Mailbox extends \App\Mailbox
{
    public function eventbriteSetting()
    {
        return $this->hasOne(EventbriteSetting::class, 'mailbox_id', 'id');
    }
}
