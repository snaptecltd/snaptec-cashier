@extends('layouts.core.backend')

@section('title', trans('cashier::messages.offline'))

@section('head')
	<script type="text/javascript" src="{{ URL::asset('core/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('core/js/editor.js') }}"></script>

    <script src="{{ URL::asset('core/js/UrlAutoFill.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li class="breadcrumb-item"><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ action("Admin\PaymentController@index") }}">{{ trans('messages.payment_gateways') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('messages.update') }}</li>
        </ul>
        <h1>
            <span class="text-semibold"><i class="icon-credit-card2"></i> {{ trans('cashier::messages.offline') }}</span>
        </h1>
    </div>

@endsection

@section('content')
		<div class="row">
			<div class="col-md-6">
				<p>
					{!! $gateway->getDescription() !!}
				</p>
			</div>
		</div>
			
		<h3>{{ trans('cashier::messages.payment.options') }}</h3>

        <form enctype="multipart/form-data" action="{{ $gateway->getSettingsUrl() }}" method="POST" class="form-validate-jquery">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-6">
                    @php
                        // For attachment_enabled
                        $attachment_enabled_options = [0, 1];
                        $attachment_enabled_value = old(
                            'attachment_enabled',
                            $gateway->getAttachmentEnabled() ? 1 : 0
                        );

                        // For attachment_required
                        $attachment_required_options = [0, 1];
                        $attachment_required_value = old(
                            'attachment_required',
                            $gateway->getAttachmentRequired() ? 1 : 0
                        );
                    @endphp
        <br>
        
                    @include('helpers.form_control', [
                        'type' => 'checkbox',
                        'name' => 'attachment_enabled',
                        'value' => $attachment_enabled_value,
                        'options' => $attachment_enabled_options,
                        'label' => trans('cashier::messages.offline.enable_attachment_upload'),
                        'help_class' => 'payment',
                    ])

        <br>
                    @include('helpers.form_control', [
                        'type' => 'checkbox',
                        'name' => 'attachment_required',
                        'value' => $attachment_required_value,
                        'options' => $attachment_required_options,
                        'label' => trans('cashier::messages.offline.attachemnt_required'),
                        'help_class' => 'payment',
                    ])
        <br>
        
                    @include('helpers.form_control', [
                        'type' => 'textarea',
                        'class' => 'setting-editor',
                        'name' => 'payment_instruction',
                        'value' => $gateway->getPaymentInstruction(),
                        'label' => trans('cashier::messages.offline.payment_instruction'),
                        'help_class' => 'payment',
                        'rules' => ['payment_instruction' => 'required'],
                    ])
                </div>
            </div>


            <hr>
            <div class="text-left">
                @if ($gateway->isActive())
                    @if (!\Snaptec\Library\Facades\Billing::isGatewayEnabled($gateway))
                        <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.save_and_enable') }}" />
                        <button class="btn btn-default me-1">{{ trans('messages.save') }}</button>
                    @else
                        <button class="btn btn-primary me-1">{{ trans('messages.save') }}</button>
                    @endif
                @else
                    <input type="submit" name="enable_gateway" class="btn btn-primary me-1" value="{{ trans('cashier::messages.connect') }}" />
                @endif
                <a class="btn btn-default" href="{{ action('Admin\PaymentController@index') }}">{{ trans('messages.cancel') }}</a>
            </div>

        </form>

@endsection