@extends('layouts.app')

@section('title_full', __('Eventbrite Settings') . ' - ' . $mailbox->name)

@section('content')


@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

<div class="section-heading">
    {{ __('Eventbrite Settings') }}
</div>

@include('partials/flash_messages')

<div class="row-container form-container">
    <div class="row">
        @if (Auth::user()->can('updateSettings', $mailbox))
            <div class="col-xs-12 col-md-12">
                <form class="form-horizontal margin-top" method="POST" action="" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <div class="form-group{{ $errors->has('eventbrite_secret_key') ? ' has-error' : '' }}">
                        <label for="eventbrite_secret_key"
                            class="col-sm-2 control-label">{{ __('Eventbrite Secret Key') }}</label>

                        <div class="col-sm-6">
                            <div class="flexy">
                                <input id="eventbrite_secret_key" type="password" class="form-control"
                                    name="eventbrite_secret_key"
                                    value="{{ old('eventbrite_secret_key', optional($mailbox->eventbriteSetting)->eventbrite_secret_key) }}"
                                    maxlength="255">
                            </div>

                            @include('partials/field_error', ['field' => 'eventbrite_secret_key'])
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('eventbrite_org_id') ? ' has-error' : '' }}">
                        <label for="eventbrite_org_id"
                            class="col-sm-2 control-label">{{ __('Eventbrite Org ID') }}</label>

                        <div class="col-sm-6">
                            <div class="flexy">
                                <input id="eventbrite_org_id" class="form-control" name="eventbrite_org_id"
                                    value="{{ old('eventbrite_org_id', optional($mailbox->eventbriteSetting)->eventbrite_org_id) }}"
                                    maxlength="255">
                            </div>

                            @include('partials/field_error', ['field' => 'eventbrite_org_id'])
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-6 col-sm-offset-2">
                            @if (optional($mailbox->eventbriteSetting)->eventbrite_secret_key == null)
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save') }}
                                </button>
                            @else
                                <button type="submit" class="btn btn-success">
                                    {{ __('Update') }}
                                </button>
                            @endif

                            <a href="javascript:void(0)" onclick="document.getElementById('deleteForm').submit();"
                                class="btn btn-danger">
                                {{ __('Remove') }}
                            </a>
                        </div>
                    </div>

                </form>
            </div>

            <form id="deleteForm" action="{{ route('eventbrite.settings.destroy', $mailbox->id) }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
            </form>
        @endif
    </div>
</div>
@endsection
