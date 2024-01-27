<?php

namespace Modules\Eventbrite\Entities;

use App\Conversation;
use Illuminate\Database\Eloquent\Model;

class EventbriteSetting extends Model
{
    protected $fillable = ['mailbox_id', 'eventbrite_secret_key', 'eventbrite_org_id'];

}
