<div class="eventbrite-container">
    <div class="eventbrite-heading">
        <div class="eventbrite-title">
            <h4>
                {{ __('Eventbrite') }}
                <form id="eventbriteCacheForm" style="float: right;"
                    action="{{ route('eventbrite.refresh', ['mailbox' => $mailbox->id, 'customer' => $customer->id]) }}"
                    method="POST">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button><i class="glyphicon glyphicon-refresh"></i></button>
                </form>
            </h4>
        </div>
    </div>

    <div class="eventbrite-orders">
        <h5>
            {{ __('Orders') }}
            <a href="{{ $urls['orders'] }}" target="_blank">
                <i class="glyphicon glyphicon-search"></i>
            </a>
        </h5>
        @if (isset($orders) && (is_array($orders->orders) && !empty($orders->orders)))
            <div class="tabs">
                @foreach ($orders->orders as $order)
                    <div class="tab">
                        <input type="checkbox" id="eventbriteOrders-{{ $order->id }}">
                        <label class="tab-label" for="eventbriteOrders-{{ $order->id }}">
                            <span class="tab-order-summary">
                                <span class="order-date">{{ App\User::dateFormat($order->created, 'd-M-Y') }}</span>
                                <span class="order-tickets">{{ count($order->attendees) }} tix</span>
                            </span>
                        </label>
                        <div class="tab-content">
                            <div class="tab-content-header">
                                <p class="subscription-title">Order #{{ $order->id }}
                                <p>
                                    <a class="eventbrite-link" target="_blank"
                                        href="https://eventbrite.com/organizations/orders/{{ $order->id }}">
                                        <i class="glyphicon glyphicon-new-window"> </i>
                                    </a>
                                </p>
                            </div>
                            <div class="tab-content-header" style="flex-direction: column;">
                                <span class="eventbrite-dateblock-horiz">
                                    <span>
                                        {{ App\User::dateFormat($order->event->start->utc, 'M-d-Y @ h:ia') }}
                                    </span>
                                </span>
                                <span class="order-event">{{ $order->event->name->text }}</span>
                            </div>
                            @if (isset($order->attendees) && is_array($order->attendees))
                                <div class="attendees">
                                    @forelse ($order->attendees as $attendee)
                                        <div class="attendee">
                                            @if ($attendee->checked_in)
                                                <span class="status checked">
                                                    <i class="glyphicon glyphicon-ok"></i>
                                                </span>
                                            @else
                                                <span class="status">
                                                    <i class="glyphicon glyphicon-unchecked"></i>
                                                </span>
                                            @endif
                                            <span>{{ $attendee->profile->name }}</span>
                                        </div>
                                    @empty
                                        <p>No attendees</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>{{ __('No orders found') }}</p>
        @endif
    </div>
    <div class="eventbrite-tickets">
        <h5>
            {{ __('Tickets') }}
            <a href="{{ $urls['tickets'] }}" target="_blank">
                <i class="glyphicon glyphicon-search"></i>
            </a>
        </h5>
        @if (isset($tickets) && is_array($tickets->attendees) && !empty($tickets))
            @foreach ($tickets->attendees as $ticket)
                <div class="eventbrite-ticket">
                    @if ($ticket->checked_in)
                        <span class="status checked"><i class="glyphicon glyphicon-ok"></i></span>
                    @else
                        <span class="status"><i class="glyphicon"></i></span>
                    @endif
                    <span>
                        <span class="eventbrite-dateblock" style="width: 45px;">
                            <span> {{ App\User::dateFormat($ticket->event->start->local, 'M') }}</span>
                            <span> {{ App\User::dateFormat($ticket->event->start->local, 'd') }}</span>
                            <span> {{ App\User::dateFormat($ticket->event->start->local, 'Y') }}</span>
                        </span>
                    </span>

                    <span style="font-weight: normal"> {{ $ticket->event->description->text }}
                    </span>
                    <span>
                        <a class="eventbrite-link" target="_blank"
                            href="https://eventbrite.com/organizations/orders/{{ $ticket->order->id }}">
                            <i class="glyphicon glyphicon-new-window"> </i>
                        </a>
                    </span>
                </div>
            @endforeach
        @else
            <p>{{ __('No tickets found') }}</p>
        @endif
    </div>
</div>
