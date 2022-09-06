<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\invoices;
use App\Models\invoices_attachments_table;
use App\Models\invoices_details;
use App\Models\sections;
use App\Models\User;
use App\Notifications\add_invoice;
use App\Notifications\add_invoices_admin;
use Illuminate\Http\Request;

//use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notifiable;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $invoices = invoices::all();
        return view('invoices.invoices', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = sections::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        invoices::create([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
        ]);
        $invoice_id = invoices::latest()->first()->id;
        invoices_details::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => (Auth::user()->name)
        ]);
        if ($request->hasFile('pic')) {

            $invoice_id = Invoices::latest()->first()->id;
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new invoices_attachments_table();
            $attachments->file_name = $file_name;
            $attachments->invoices_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move('Attachments/' . $invoice_number, $imageName);
        }
        //for email notification
//        $user = User::first();
//        //Notification::send($user, new add_invoice($invoice_id));
//        $user->notify(new add_invoice($invoice_id));


        //for admin notification
        $_user = User::find(1);
        $invoices = invoices::latest()->first();
        $_user->notify(new add_invoices_admin($invoices));
        session()->flash('Add', 'تم اضافة الفاتورة بنجاح');
        return redirect('/invoices');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\invoices $invoices
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\invoices $invoices
     * @return \Illuminate\Http\Response
     */

    public function edit_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        $sections = sections::all();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\invoices $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $invoices = invoices::findOrFail($request->invoice_id);
        $inNumber = invoices::select('invoice_number')->where('id', $request->invoice_id)->first();

        $invoices->update([
            'invoice_number' => $inNumber->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);

        session()->flash('edit', 'تم تعديل الفاتورة بنجاح');
        return back();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\invoices $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->invoice_id;
        $invoice = invoices::where('id', $id)->first();
        $invoice_attachment = invoices_attachments_table::where('invoice_id', $id)->get();
        $id_page = $request->id_page;
        if (!$id_page == 2) {
            if (!empty($invoice_attachment)) {
                foreach ($invoice_attachment as $attach) {
                    storage::disk('Attachments')->delete($attach->invoices_number . '/' . $attach->file_name);
                }
            }
            $invoice->forceDelete(); //delete completely
            //$invoice->Delete();//delete with deleted_at
            session()->flash('delete_invoice');
            return redirect('/invoices');
        } else
            $invoice->delete();
        session()->flash('delete_invoices');
        return redirect('/Archive');
    }

    public function getproducts($id)
    {
        $states = DB::table('products')->where('section_id', $id)->pluck('Product_name', 'id');
        return json_encode($states);
    }

    public function Status_Update($id, Request $request)
    {
        $invoices = invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        } else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            invoices_Details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }

    public function Invoice_Paid()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid', compact('invoices'));
    }

    public function Invoice_unPaid()
    {
        $invoices = Invoices::where('Value_Status', 2)->get();
        return view('invoices.invoices_unpaid', compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoices::where('Value_Status', 3)->get();
        return view('invoices.invoices_Partial', compact('invoices'));
    }

    public function Print_invoice($id)
    {
        $invoices = invoices::where('id', $id)->first();
        return view('invoices.Print_invoice', compact('invoices'));
    }

    public function export()
    {

        return Excel::download(new InvoicesExport(), 'invoices.xlsx');
    }
    public function MarkAsRead_all (Request $request)
    {

        $userUnreadNotification= auth()->user()->unreadNotifications;

        if($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return back();
        }


    }


    public function unreadNotifications_count()

    {
        return auth()->user()->unreadNotifications->count();
    }

    public function unreadNotifications()

    {
        foreach (auth()->user()->unreadNotifications as $notification){

            return $notification->data['title'];

        }

    }
}
