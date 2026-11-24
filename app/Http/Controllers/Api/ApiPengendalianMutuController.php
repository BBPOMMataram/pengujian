<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterPenyelia;
use App\Models\PengendalianMutu;
use App\Models\PengendalianMutuChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiPengendalianMutuController extends Controller
{
    /**
     * Format an Eloquent PengendalianMutu model into frontend-friendly record.
     */
    private function formatRecord(PengendalianMutu $item): array
    {
        $checklistMap = [];
        foreach ($item->checklists as $chk) {
            $checklistMap[$chk->item_key] = [
                'status' => $chk->status,
                'note' => $chk->note ?? '',
            ];
        }

        return [
            'id' => (string) $item->id,
            'createdAt' => $item->created_at ? $item->created_at->toISOString() : '',
            'tanggalPengujian' => $item->tanggal_pengujian instanceof \DateTimeInterface 
                ? $item->tanggal_pengujian->format('Y-m-d') 
                : (string) $item->tanggal_pengujian,
            'jenisSampel' => is_array($item->jenis_sampel) ? $item->jenis_sampel : [],
            'parameterPengujian' => $item->parameter_pengujian,
            'metodePengujian' => $item->metode_pengujian,
            'statusAkhir' => $item->status_akhir,
            'checklist' => (object) $checklistMap,
            'catatanEvaluasi' => $item->catatan_evaluasi ?? '',
            'penyelia' => $item->penyelia_nama,
            'penyeliaId' => $item->penyelia_id,
            'pengujiNama' => $item->penguji_nama,
            'pengujiNip' => $item->penguji_nip,
            'pengujiSignaturePath' => $item->penguji_signature_path,
        ];
    }

    /**
     * Get active supervisors (penyelia) list.
     */
    public function getPenyeliaList()
    {
        try {
            $penyelias = MasterPenyelia::where('is_active', true)->orderBy('id')->get();
            return response()->json([
                'success' => true,
                'data' => $penyelias,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching penyelia list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of pengendalian mutu records.
     */
    public function index(Request $request)
    {
        try {
            $query = PengendalianMutu::with('checklists');

            if ($request->has('tanggal')) {
                $query->whereDate('tanggal_pengujian', $request->query('tanggal'));
            }
            if ($request->has('status_akhir')) {
                $query->where('status_akhir', $request->query('status_akhir'));
            }
            if ($request->has('metode_pengujian')) {
                $query->where('metode_pengujian', $request->query('metode_pengujian'));
            }

            $items = $query->orderByDesc('tanggal_pengujian')->orderByDesc('id')->get();

            $formatted = $items->map(function ($item) {
                return $this->formatRecord($item);
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching pengendalian mutu records: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pengendalian mutu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created pengendalian mutu record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggalPengujian' => 'required|date',
            'jenisSampel' => 'required|array|min:1',
            'parameterPengujian' => 'required|string|max:255',
            'metodePengujian' => 'required|string|in:molekuler,kultur',
            'statusAkhir' => 'required|string|in:valid,invalid',
            'penyelia' => 'required|string|max:255',
            'penyeliaId' => 'nullable|integer',
            'checklist' => 'required|array',
            'catatanEvaluasi' => 'nullable|string',
            'pengujiNama' => 'nullable|string|max:255',
            'pengujiNip' => 'nullable|string|max:50',
            'pengujiSignaturePath' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Find penyelia_id if provided or lookup by name
            $penyeliaId = $validated['penyeliaId'] ?? null;
            if (!$penyeliaId) {
                $foundPenyelia = MasterPenyelia::where('nama', $validated['penyelia'])->first();
                if ($foundPenyelia) {
                    $penyeliaId = $foundPenyelia->id;
                }
            }

            // User info from SSO request if available
            $ssoUser = $request->attributes->get('sso_user');
            $userId = $ssoUser->id ?? null;
            $pengujiNama = $validated['pengujiNama'] ?? ($ssoUser->name ?? '-');
            $pengujiSignaturePath = $validated['pengujiSignaturePath'] ?? ($ssoUser->signature_path ?? null);
            $pengujiNip = $validated['pengujiNip'] ?? ($ssoUser->employee->nip ?? null);

            $record = PengendalianMutu::create([
                'user_id' => $userId,
                'tanggal_pengujian' => $validated['tanggalPengujian'],
                'jenis_sampel' => $validated['jenisSampel'],
                'parameter_pengujian' => trim($validated['parameterPengujian']),
                'metode_pengujian' => $validated['metodePengujian'],
                'status_akhir' => $validated['statusAkhir'],
                'catatan_evaluasi' => trim($validated['catatanEvaluasi'] ?? ''),
                'penyelia_id' => $penyeliaId,
                'penyelia_nama' => trim($validated['penyelia']),
                'penguji_nama' => $pengujiNama,
                'penguji_nip' => $pengujiNip,
                'penguji_signature_path' => $pengujiSignaturePath,
            ]);

            // Save each checklist item to detail table
            $checklists = $validated['checklist'];
            foreach ($checklists as $key => $answer) {
                $status = is_array($answer) ? ($answer['status'] ?? null) : null;
                $note = is_array($answer) ? ($answer['note'] ?? null) : null;
                $sectionTitle = is_array($answer) ? ($answer['sectionTitle'] ?? '') : '';
                $itemName = is_array($answer) ? ($answer['itemName'] ?? $key) : $key;
                $criteria = is_array($answer) ? ($answer['criteria'] ?? null) : null;

                PengendalianMutuChecklist::create([
                    'pengendalian_mutu_id' => $record->id,
                    'section_title' => $sectionTitle ?: 'Checklist Pengujian',
                    'item_key' => $key,
                    'item_name' => $itemName,
                    'criteria' => $criteria,
                    'status' => $status,
                    'note' => $note,
                ]);
            }

            $record->load('checklists');

            return response()->json([
                'success' => true,
                'message' => 'Data pengendalian mutu berhasil disimpan.',
                'data' => $this->formatRecord($record),
            ], 201);
        });
    }

    /**
     * Display the specified record.
     */
    public function show($id)
    {
        try {
            $item = PengendalianMutu::with('checklists')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $this->formatRecord($item),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengendalian mutu tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * Remove the specified record.
     */
    public function destroy($id)
    {
        try {
            $item = PengendalianMutu::findOrFail($id);
            $item->delete(); // cascade deletes checklists

            return response()->json([
                'success' => true,
                'message' => 'Data pengendalian mutu berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pengendalian mutu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all master penyelia for admin management.
     */
    public function getAllMasterPenyelia(Request $request)
    {
        try {
            $query = MasterPenyelia::query();

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
                });
            }

            $penyelias = $query->orderBy('nama', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $penyelias,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching master penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data master penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store new master penyelia.
     */
    public function storeMasterPenyelia(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $penyelia = MasterPenyelia::create([
                'nama' => trim($validated['nama']),
                'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== '' 
                    ? trim($validated['jabatan']) 
                    : 'Penyelia Mikrobiologi',
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penyelia berhasil ditambahkan.',
                'data' => $penyelia,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error creating master penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing master penyelia.
     */
    public function updateMasterPenyelia(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $penyelia = MasterPenyelia::findOrFail($id);
            $penyelia->update([
                'nama' => trim($validated['nama']),
                'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== ''
                    ? trim($validated['jabatan'])
                    : $penyelia->jabatan,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $penyelia->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penyelia berhasil diperbarui.',
                'data' => $penyelia,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error updating master penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a master penyelia.
     */
    public function destroyMasterPenyelia($id)
    {
        try {
            $penyelia = MasterPenyelia::findOrFail($id);
            $penyelia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data penyelia berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error deleting master penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
