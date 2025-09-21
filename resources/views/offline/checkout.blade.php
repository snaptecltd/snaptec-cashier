@extends('layouts.core.empty', [
    'subscriptionPage' => true,
])

@section('title', trans('messages.subscriptions'))

@section('content')
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-md-6">
                <h2>{!! trans('cashier::messages.pay_invoice') !!}</h2>  

                <div class="alert alert-info bg-grey-light">
                    {!! $service->getPaymentInstruction() !!}
                </div>
                <hr>
                
                <div class="d-flex align-items-center flex-column">
                    <form method="POST" action="{{ \Snaptec\Cashier\Cashier::lr_action('\Snaptec\Cashier\Controllers\OfflineController@claim', [
                            'invoice_uid' => $invoice->uid
                        ]) }}">
                        {{ csrf_field() }}
                        <div class="form-group mb-3">
                            <div class="alert alert-info">
                                <span><i class="bi bi-info-circle-fill me-2" style="font-size: 1.2em;"></i></span>
                                <span>{{ trans('cashier::messages.offline.upload_instruction.default') }}</span>
                            </div>
                            <label for="payment_attachment">
                                {{ trans('cashier::messages.offline.payment_attachment') }}
                                @if($service->getAttachmentRequired())
                                    <span class="text-danger">*</span>
                                @endif
                                <small class="text-muted d-block">
                                    {{ trans('cashier::messages.offline.attachment_formats') }}: PDF, JPEG, JPG, PNG, WEBP, HEIC, HEIF, CR2, NEF, ARW
                                </small>
                            </label>
                            <input
                                type="file"
                                class="form-control"
                                id="payment_attachment"
                                name="payment_attachment"
                                accept=".pdf,image/jpeg,image/jpg,image/png,image/webp,image/heic,image/heif,image/x-canon-cr2,image/x-nikon-nef,image/x-sony-arw"
                                @if($service->getAttachmentRequired()) required @endif
                            >
                            <div id="attachmentPreview" class="mt-2"></div>
                        </div>

                        <script>
                            document.getElementById('payment_attachment').addEventListener('change', function(e) {
                                const file = e.target.files[0];
                                const preview = document.getElementById('attachmentPreview');
                                preview.innerHTML = '';
                                if (!file) return;

                                const imageTypes = [
                                    'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/heic', 'image/heif'
                                ];
                                if (imageTypes.includes(file.type)) {
                                    const reader = new FileReader();
                                    reader.onload = function(evt) {
                                        const img = document.createElement('img');
                                        img.src = evt.target.result;
                                        img.style.maxWidth = '200px';
                                        img.style.maxHeight = '200px';
                                        img.className = 'img-thumbnail';
                                        img.style.cursor = 'pointer';
                                        img.title = 'Click to preview';
                                        img.addEventListener('click', function() {
                                            showLargePreview(evt.target.result);
                                        });
                                        preview.appendChild(img);
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    preview.textContent = file.name;
                                }
                            });

                            function showLargePreview(src) {
                                // Create overlay
                                const overlay = document.createElement('div');
                                overlay.style.position = 'fixed';
                                overlay.style.top = 0;
                                overlay.style.left = 0;
                                overlay.style.width = '100vw';
                                overlay.style.height = '100vh';
                                overlay.style.background = 'rgba(0,0,0,0.7)';
                                overlay.style.display = 'flex';
                                overlay.style.alignItems = 'center';
                                overlay.style.justifyContent = 'center';
                                overlay.style.zIndex = 9999;
                                overlay.style.cursor = 'zoom-out';

                                // Create large image
                                const largeImg = document.createElement('img');
                                largeImg.src = src;
                                largeImg.style.maxWidth = '90vw';
                                largeImg.style.maxHeight = '90vh';
                                largeImg.style.boxShadow = '0 0 20px #000';
                                largeImg.style.borderRadius = '8px';
                                overlay.appendChild(largeImg);

                                // Remove overlay on click
                                overlay.addEventListener('click', function() {
                                    document.body.removeChild(overlay);
                                });

                                document.body.appendChild(overlay);
                            }
                        </script>
                        
                        <div class="d-flex align-items-center">
                            <button class="btn btn-primary mr-10">{{ trans('cashier::messages.offline.claim_payment') }}</button>
                            <form id="cancelForm" method="POST" action="{{ action('SubscriptionController@cancelInvoice', [
                                'invoice_uid' => $invoice->uid,]) }}">
                                {{ csrf_field() }}
                                <a href="{{ Billing::getReturnUrl() }}" class="ms-2 btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i> {{ trans('cashier::messages.go_back') }}
                                </a>
                            </form>
                        </div>
                    </form>
                </div>
                
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-4">
                <div class="card shadow-sm rounded-3 px-2 py-2 mb-4">
                    <div class="card-body p-4">
                        @include('invoices.bill', [
                            'bill' => $invoice->getBillingInfo(),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection