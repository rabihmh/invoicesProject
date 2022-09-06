<?php

namespace App\Http\Controllers;

use App\Models\invoices;
use App\Models\invoices_attachments_table;
use App\Models\invoices_details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InvoicesDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $invoices = invoices::where('id', $id)->first();
        $details = invoices_Details::where('id_Invoice', $id)->get();
        $attachments = invoices_attachments_table::where('invoice_id', $id)->get();

        return view('invoices.invoices_details', compact('invoices', 'details', 'attachments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\invoices_details $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function show(invoices_details $invoices_details)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\invoices_details $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function edit(invoices_details $invoices_details)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\invoices_details $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, invoices_details $invoices_details)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\invoices_details $invoices_details
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $invoices = invoices_attachments_table::findOrFail($request->id_file);
        $folder = base_path() . '/public/Attachments/' . $request->invoice_number;
        $file = $folder . '/' . $request->file_name;
        File::delete($file);
        $invoices->delete();
        session()->flash('delete', 'تم حذف المرفق بنجاح');
        return back();
    }

    public function get_file($invoice_number, $file_name)

    {
        //$contents = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number . '/' . $file_name);
        return response()->download(base_path() . '/public/Attachments/' . $invoice_number . '/' . $file_name);
    }


    public function open_file($invoice_number, $file_name)

    {
        //$files = Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($invoice_number . '/' . $file_name);
        //$files = File::get('Attachments/' . $invoice_number . '/' . $file_name);
        //$pathToFile = storage_path('Attachments\\' . $invoice_number . '\\' . $file_name);

        return response()->file(base_path() . '/public/Attachments/' . $invoice_number . '/' . $file_name);
    }

}
