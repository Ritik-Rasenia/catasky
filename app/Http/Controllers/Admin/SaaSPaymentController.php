<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class SaaSPaymentController extends Controller
{
    /**
     * Display a listing of B2B payment transactions.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user.subscriberProfile', 'plan']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', '%' . $search . '%')
                  ->orWhere('gateway', 'like', '%' . $search . '%')
                  ->orWhereHas('user.subscriberProfile', function($qp) use ($search) {
                      $qp->where('company_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $payments = $query->latest()->paginate(15);
        $totalRevenue = Payment::where('status', 'success')->sum('amount');

        return view('admin.saas.payments.index', compact('payments', 'totalRevenue'));
    }

    /**
     * Display a listing of generated B2B Invoices.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with(['user.subscriberProfile', 'subscription.plan']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user.subscriberProfile', function($qp) use ($search) {
                      $qp->where('company_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $invoices = $query->latest()->paginate(15);

        return view('admin.saas.invoices.index', compact('invoices'));
    }

    /**
     * Download or view a specific B2B Subscriber Invoice PDF.
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load('subscription.plan', 'payment');
        $user = $invoice->user;
        $profile = $user->subscriberProfile;

        return view('subscriber-panel.subscription.invoice-pdf', compact('invoice', 'user', 'profile'));
    }
}
