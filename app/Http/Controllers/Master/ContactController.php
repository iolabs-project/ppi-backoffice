<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ContactFormRequest;
use App\Services\Master\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
            $contact = $this->contactService->storeContact($request);

            return response()->json([
                'message' => 'Kontak berhasil dibuat',
                'data' => $contact->only(['id', 'code', 'name', 'transportation_cost']),
            ]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
        abort_if(!$contact, 404, 'Kontak tidak ditemukan.');

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
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
            $data = $this->contactService->fetchOptionData($request);

            return response()->json([
                'data' => $data,
            ]);
        } catch (ValidationException | HttpExceptionInterface $e) {
            throw $e;
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
