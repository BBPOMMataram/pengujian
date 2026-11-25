<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterKatim;
use App\Models\MasterPenyelia;
use App\Models\PengendalianMutu;
use App\Models\PengendalianMutuChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiPengendalianMutuController extends Controller
{
    private function sanitizeSignaturePath(?string $sig): ?string
    {
        if (!$sig) {
            return null;
        }
        $trimmed = trim($sig);
        if ($trimmed === '' || $trimmed === '-' || strtolower($trimmed) === 'null') {
            return null;
        }
        return $trimmed;
    }

    private function extractSsoUser(Request $request): array
    {
        $raw = $request->attributes->get('sso_user');
        $id = data_get($raw, 'id') ?? data_get($raw, 'sub');
        $name = data_get($raw, 'name');
        $nip = data_get($raw, 'employee.nip') ?? data_get($raw, 'nip');
        $sig = data_get($raw, 'signature_path');

        return [
            'id' => ($id !== null && trim((string) $id) !== '') ? trim((string) $id) : null,
            'name' => $name ? trim((string) $name) : '',
            'nip' => $nip ? trim((string) $nip) : null,
            'signature_path' => $this->sanitizeSignaturePath(is_string($sig) ? $sig : null),
        ];
    }

    private function isSamePerson(array $ssoUser, ?string $targetAuthUserId, ?string $targetNip, ?string $targetNama): bool
    {
        if ($targetAuthUserId && $ssoUser['id'] && trim($targetAuthUserId) === trim($ssoUser['id'])) {
            return true;
        }

        if ($targetNip && $ssoUser['nip'] && trim($targetNip) === trim($ssoUser['nip'])) {
            return true;
        }

        if (!$targetAuthUserId && $targetNama && $ssoUser['name']) {
            $normTarget = strtolower(trim($targetNama));
            $normUser = strtolower(trim($ssoUser['name']));
            if ($normTarget === $normUser || str_contains($normTarget, $normUser) || str_contains($normUser, $normTarget)) {
                return true;
            }
        }

        return false;
    }

    private function formatRecord(PengendalianMutu $item, ?MasterKatim $activeKatim = null): array
    {
        $checklistMap = [];
        foreach ($item->checklists as $chk) {
            $checklistMap[$chk->item_key] = [
                'status' => $chk->status,
                'note' => $chk->note ?? '',
            ];
        }

        $isKatimVerified = $item->katim_verified_at !== null;
        $katimNama = $isKatimVerified
            ? $item->katim_nama
            : ($activeKatim?->nama ?? $item->katim_nama);
        $katimNip = $isKatimVerified
            ? $item->katim_nip
            : ($activeKatim?->nip ?? $item->katim_nip);
        $katimAuthUserId = $isKatimVerified
            ? $item->katim?->auth_user_id
            : ($activeKatim?->auth_user_id ?? $item->katim?->auth_user_id);

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
            'penyeliaAuthUserId' => $item->penyelia?->auth_user_id,
            'penyeliaNip' => $item->penyelia?->nip,
            'penyeliaVerifiedAt' => $item->penyelia_verified_at?->toISOString(),
            'penyeliaVerifiedBy' => $item->penyelia_verified_by,
            'penyeliaSignaturePath' => $item->penyelia_signature_path,
            'katimId' => $item->katim_id ?? $activeKatim?->id,
            'katimNama' => $katimNama,
            'katimNip' => $katimNip,
            'katimAuthUserId' => $katimAuthUserId,
            'katimVerifiedAt' => $item->katim_verified_at?->toISOString(),
            'katimVerifiedBy' => $item->katim_verified_by,
            'katimSignaturePath' => $item->katim_signature_path,
            'pengujiNama' => $item->penguji_nama,
            'pengujiNip' => $item->penguji_nip,
            'pengujiSignaturePath' => $item->penguji_signature_path,
        ];
    }

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

    public function getActiveKatim()
    {
        try {
            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();
            return response()->json([
                'success' => true,
                'data' => $activeKatim,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching active katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data ketua tim aktif.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = PengendalianMutu::with(['checklists', 'penyelia', 'katim']);

            if ($request->has('tanggal')) {
                $query->whereDate('tanggal_pengujian', $request->query('tanggal'));
            }
            if ($request->has('status_akhir')) {
                $query->where('status_akhir', $request->query('status_akhir'));
            }
            if ($request->has('metode_pengujian')) {
                $query->where('metode_pengujian', $request->query('metode_pengujian'));
            }

            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();
            $items = $query->orderByDesc('tanggal_pengujian')->orderByDesc('id')->get();

            $formatted = $items->map(function ($item) use ($activeKatim) {
                return $this->formatRecord($item, $activeKatim);
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'activeKatim' => $activeKatim,
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
            $penyeliaId = $validated['penyeliaId'] ?? null;
            if (!$penyeliaId) {
                $foundPenyelia = MasterPenyelia::where('nama', $validated['penyelia'])->first();
                if ($foundPenyelia) {
                    $penyeliaId = $foundPenyelia->id;
                }
            }

            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();

            $ssoUser = $this->extractSsoUser($request);
            $userId = $ssoUser['id'];
            $pengujiNama = $validated['pengujiNama'] ?? ($ssoUser['name'] ?: '-');
            $pengujiSignaturePath = $this->sanitizeSignaturePath($validated['pengujiSignaturePath'] ?? $ssoUser['signature_path']);
            $pengujiNip = $validated['pengujiNip'] ?? $ssoUser['nip'];

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
                'katim_id' => $activeKatim?->id,
                'katim_nama' => $activeKatim?->nama,
                'katim_nip' => $activeKatim?->nip,
                'penguji_nama' => $pengujiNama,
                'penguji_nip' => $pengujiNip,
                'penguji_signature_path' => $pengujiSignaturePath,
            ]);

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

            $record->load(['checklists', 'penyelia', 'katim']);

            return response()->json([
                'success' => true,
                'message' => 'Data pengendalian mutu berhasil disimpan.',
                'data' => $this->formatRecord($record, $activeKatim),
            ], 201);
        });
    }

    public function show($id)
    {
        try {
            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();
            $item = PengendalianMutu::with(['checklists', 'penyelia', 'katim'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $this->formatRecord($item, $activeKatim),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengendalian mutu tidak ditemukan.',
            ], 404);
        }
    }

    public function verifyPenyelia(Request $request, $id)
    {
        try {
            $ssoUser = $this->extractSsoUser($request);
            $item = PengendalianMutu::with(['checklists', 'penyelia', 'katim'])->findOrFail($id);

            $isAssigned = $this->isSamePerson(
                $ssoUser,
                $item->penyelia?->auth_user_id,
                $item->penyelia?->nip,
                $item->penyelia_nama
            );

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memverifikasi sebagai penyelia pada data ini.',
                ], 403);
            }

            if ($item->penyelia_verified_at !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ini sudah diverifikasi oleh penyelia.',
                ], 422);
            }

            $signaturePath =$ssoUser['signature_path']
                ?? $this->sanitizeSignaturePath($request->input('signaturePath'));

            $item->update([
                'penyelia_verified_at' => now(),
                'penyelia_verified_by' => $ssoUser['id'],
                'penyelia_signature_path' => $signaturePath,
            ]);

            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();$item->refresh()->load(['checklists', 'penyelia', 'katim']);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diverifikasi oleh penyelia.',
                'data' => $this->formatRecord($item,$activeKatim),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error verifying penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi data oleh penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyKatim(Request $request,$id)
    {
        try {
            $ssoUser = $this->extractSsoUser($request);
            $item = PengendalianMutu::with(['checklists', 'penyelia', 'katim'])->findOrFail($id);
            $activeKatim = MasterKatim::where('is_active', true)->orderByDesc('id')->first();$targetKatim = $activeKatim ?? $item->katim;

            if (!$targetKatim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ketua Tim aktif belum diatur pada halaman admin.',
                ], 422);
            }

            $isAssigned =$this->isSamePerson(
                $ssoUser,$targetKatim->auth_user_id,
                $targetKatim->nip,$targetKatim->nama
            );

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memverifikasi sebagai Ketua Tim.',
                ], 403);
            }

            if ($item->penyelia_verified_at === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penyelia belum verifikasi. Verifikasi oleh Ketua Tim hanya dapat dilakukan setelah penyelia memverifikasi data.',
                ], 422);
            }

            if ($item->katim_verified_at !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ini sudah diverifikasi oleh Ketua Tim.',
                ], 422);
            }

            $signaturePath =$ssoUser['signature_path']
                ?? $this->sanitizeSignaturePath($request->input('signaturePath'));

            $item->update([
                'katim_id' => $targetKatim->id,
                'katim_nama' => $targetKatim->nama,
                'katim_nip' => $targetKatim->nip,
                'katim_verified_at' => now(),
                'katim_verified_by' => $ssoUser['id'],
                'katim_signature_path' => $signaturePath,
            ]);

            $item->refresh()->load(['checklists', 'penyelia', 'katim']);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diverifikasi oleh Ketua Tim.',
                'data' => $this->formatRecord($item,$activeKatim),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error verifying katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi data oleh Ketua Tim.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = PengendalianMutu::findOrFail($id);$item->delete();

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

    public function getAllMasterPenyelia(Request $request)
    {
        try {
            $query = MasterPenyelia::query();

            if ($search = $request->query('search')) {$query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
                });
            }

            $penyelias =$query->orderBy('nama', 'asc')->get();

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

    public function storeMasterPenyelia(Request $request)
    {
        $validated =$request->validate([
            'auth_user_id' => 'nullable|string|max:64',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $penyelia = MasterPenyelia::create([
                'auth_user_id' => !empty($validated['auth_user_id']) ? trim($validated['auth_user_id']) : null,
                'nama' => trim($validated['nama']),
                'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== ''
                    ? trim($validated['jabatan'])
                    : 'Penyelia Mikrobiologi',
                'is_active' => $request->has('is_active') ? (bool)$request->input('is_active') : true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data penyelia berhasil ditambahkan.',
                'data' => $penyelia,
            ], 201);
        } catch (\Throwable$e) {
            Log::error('Error creating master penyelia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penyelia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMasterPenyelia(Request $request,$id)
    {
        $validated =$request->validate([
            'auth_user_id' => 'nullable|string|max:64',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $penyelia = MasterPenyelia::findOrFail($id);$penyelia->update([
                'auth_user_id' => $request->has('auth_user_id')
                    ? (!empty($validated['auth_user_id']) ? trim($validated['auth_user_id']) : null)
                    : $penyelia->auth_user_id,
                'nama' => trim($validated['nama']),
                'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== ''
                    ? trim($validated['jabatan'])
                    : $penyelia->jabatan,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') :$penyelia->is_active,
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

    public function destroyMasterPenyelia($id)
    {
        try {
            $penyelia = MasterPenyelia::findOrFail($id);$penyelia->delete();

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

    public function getAllMasterKatim(Request $request)
    {
        try {
            $query = MasterKatim::query();

            if ($search = $request->query('search')) {$query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
                });
            }

            $katims =$query->orderByDesc('is_active')->orderBy('nama', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $katims,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching master katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data master ketua tim.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeMasterKatim(Request $request)
    {
        $validated =$request->validate([
            'auth_user_id' => 'nullable|string|max:64',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        try {
            $katim = DB::transaction(function () use ($request, $validated) {$isActive = $request->has('is_active') ? (bool)$request->input('is_active') : true;
                if ($isActive) {
                    MasterKatim::where('is_active', true)->update(['is_active' => false]);
                }

                return MasterKatim::create([
                    'auth_user_id' => !empty($validated['auth_user_id']) ? trim($validated['auth_user_id']) : null,
                    'nama' => trim($validated['nama']),
                    'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                    'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== ''
                        ? trim($validated['jabatan'])
                        : 'Ketua Tim Mikrobiologi',
                    'is_active' => $isActive,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data ketua tim berhasil ditambahkan.',
                'data' => $katim,
            ], 201);
        } catch (\Throwable$e) {
            Log::error('Error creating master katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data ketua tim.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMasterKatim(Request $request,$id)
    {
        $validated =$request->validate([
            'auth_user_id' => 'nullable|string|max:64',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        try {
            $katim = DB::transaction(function () use ($request, $validated,$id) {
                $record = MasterKatim::findOrFail($id);
                $isActive =$request->has('is_active') ? (bool) $request->input('is_active') :$record->is_active;

                if ($isActive) {
                    MasterKatim::where('id', '!=', $record->id)
                        ->where('is_active', true)
                        ->update(['is_active' => false]);
                }

                $record->update([
                    'auth_user_id' => $request->has('auth_user_id')
                        ? (!empty($validated['auth_user_id']) ? trim($validated['auth_user_id']) : null)
                        : $record->auth_user_id,
                    'nama' => trim($validated['nama']),
                    'nip' => isset($validated['nip']) ? trim($validated['nip']) : null,
                    'jabatan' => isset($validated['jabatan']) && trim($validated['jabatan']) !== ''
                        ? trim($validated['jabatan'])
                        : $record->jabatan,
                    'is_active' => $isActive,
                ]);

                return $record;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data ketua tim berhasil diperbarui.',
                'data' => $katim,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error updating master katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data ketua tim.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyMasterKatim($id)
    {
        try {
            $katim = MasterKatim::findOrFail($id);$katim->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data ketua tim berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error deleting master katim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data ketua tim.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}