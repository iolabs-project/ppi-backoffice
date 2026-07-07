<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function datatable(Request $request, ContactService $contactService)
    {
        try {
            $data = $contactService->fetchContactTableData($request);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error Master/ContactController@datatable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data kontak',
            ], 500);
        }
    }

    public function store(Request $request, ContactService $contactService)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:contacts,code,NULL,id,company_id,' . config('context.selected_company_id'),
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_customer' => 'required|boolean',
            'is_supplier' => 'required|boolean',
            'is_employee' => 'required|boolean',
        ]);
        try {
            $contactService->storeContact($request);

            return response()->json([
                'message' => 'Kontak berhasil dibuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ContactController@store: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat kontak',
            ], 500);
        }
    }

    public function update(Request $request, ContactService $contactService, int $id)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:contacts,code,' . $id . ',id,company_id,' . config('context.selected_company_id'),
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_customer' => 'required|boolean',
            'is_supplier' => 'required|boolean',
            'is_employee' => 'required|boolean',
        ]);
        try {
            $contactService->updateContact($request, $id);

            return response()->json([
                'message' => 'Kontak berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ContactController@update: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui kontak',
            ], 500);
        }
    }

    public function status(Request $request, ContactService $contactService, int $id)
    {
        try {
            $contactService->toggleContactStatus($id);

            return response()->json([
                'message' => 'Status kontak berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ContactController@status: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui status kontak',
            ], 500);
        }
    }
    public function options(Request $request, ContactService $contactService)
    {
        try {
            $data = $contactService->fetchOptionData($request);

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error ContactController@options: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data kontak',
            ], 500);
        }
    }
}
