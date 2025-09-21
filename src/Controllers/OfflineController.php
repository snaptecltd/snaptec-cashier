<?php

namespace Snaptec\Cashier\Controllers;

use Snaptec\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Snaptec\Model\Setting;
use Snaptec\Library\Facades\Billing;
use Snaptec\Library\TransactionResult;
use Snaptec\Model\Invoice;

class OfflineController extends Controller
{
    public function settings(Request $request)
    {
        $gateway = Billing::getGateway('offline');

        if ($request->isMethod('post')) {
            // validate
            $this->validate($request, [
                'payment_instruction' => 'required',
                'attachment_enabled' => 'nullable',
                'attachment_required' => 'nullable',
            ]);

            // save settings
            Setting::set('cashier.offline.payment_instruction', $request->payment_instruction);
            Setting::set('cashier.offline.attachment_enabled', $request->has('attachment_enabled') && $request->attachment_enabled ? 'yes' : 'no');
            Setting::set('cashier.offline.attachment_required', $request->has('attachment_required') && $request->attachment_required ? 'yes' : 'no');

            // enable if not validate
            if ($request->enable_gateway) {
                Billing::enablePaymentGateway($gateway->getType());
            }

            return redirect()->action('Admin\PaymentController@index');
        }

        return view('cashier::offline.settings', [
            'gateway' => $gateway,
        ]);
    }

    public function __construct(Request $request)
    {
        \Carbon\Carbon::setToStringFormat('jS \o\f F');
    }
    
    /**
     * Get current payment service.
     *
     * @return \Illuminate\Http\Response
     **/
    public function getPaymentService()
    {
        return Billing::getGateway('offline');
    }
    
    /**
     * Subscription checkout page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function checkout(Request $request)
    {
        $service = $this->getPaymentService();
        $invoice = Invoice::findByUid($request->invoice_uid);

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }

        // free plan. No charge
        if ($invoice->total() == 0) {
            $invoice->checkout($service, function($invoice) {
                return new TransactionResult(TransactionResult::RESULT_DONE);
            });

            return redirect()->away(Billing::getReturnUrl());;
        }
        
        return view('cashier::offline.checkout', [
            'service' => $service,
            'invoice' => $invoice,
        ]);
    }
    
    /**
     * Claim payment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     **/
    public function claim(Request $request, $invoice_uid)
    {
        $service = $this->getPaymentService();
        $invoice = Invoice::findByUid($invoice_uid);

        // exceptions
        if (!$invoice->isNew()) {
            throw new \Exception('Invoice is not new');
        }
        
        // claim invoice
        $request = $request->only('payment_attachment');
        $invoice->checkout($service, function($invoice) {
            return new TransactionResult(TransactionResult::RESULT_PENDING);
        }, true, $request);
        
        return redirect()->away(Billing::getReturnUrl());;
    }
}
