<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ContactFormRequest;
use App\Services\Master\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    private ContactService $contactService;
    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }
    public function datatable(Request $request)
    {
        try {
            $data = $this->contactService->fetchContactTableData($request);

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

    public function store(ContactFormRequest $request)
    {
        try {
            $this->contactService->storeContact($request);

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
    public function show(int $id)
    {
        $contact = $this->contactService->fetchContactById($id);
        $data = [
            'currentPage'      => 'master',
            'breadcrumb'       => [
                ['label' => 'Master Data', 'url' => route('master.index')],
                ['label' => $contact->name],
            ],
            'contact'          => $contact,
        ];

        return view('master.contact.show', $data);
    }
    public function update(ContactFormRequest $request, int $id)
    {
        try {
            $this->contactService->updateContact($request, $id);

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

    public function status(Request $request, int $id)
    {
        try {
            $this->contactService->toggleContactStatus($id);

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
    public function options(Request $request)
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
