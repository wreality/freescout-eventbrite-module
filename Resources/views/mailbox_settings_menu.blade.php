@if (Auth::user()->isAdmin() || Auth::user()->hasManageMailboxPermission($mailbox->id, App\Mailbox::ACCESS_PERM_AUTO_REPLIES))
    <li @if (Route::currentRouteName() == 'eventbrite.settings')class="active"@endif><a href="{{ route('eventbrite.settings', ['id'=>$mailbox->id]) }}"><i class="glyphicon glyphicon-refresh"></i> {{ __('Eventbrite Settings') }}</a></li>
@endif