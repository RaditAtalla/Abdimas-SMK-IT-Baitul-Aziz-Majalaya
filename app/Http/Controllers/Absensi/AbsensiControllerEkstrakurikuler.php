<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\KehadiranEkstrakurikuler;
use App\Models\SiswaEkstrakurikuler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AbsensiControllerEkstrakurikuler extends Controller
{
    public function listEkstrakurikuler()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user?->getRoleNames()->first();

        $q = Ekstrakurikuler::query()
            ->with(['tahunAjaran', 'pembina'])
            ->withCount(['peserta as peserta_count'])
            ->orderByDesc('ekstrakurikuler_id');

        if ($role === 'Guru Mapel') {
            $q->where('user_id', $user->id);
        }

        $ekstrakurikuler = $q->get();

        return view('absensi_ekstrakurikuler.index_ekstrakurikuler', compact('ekstrakurikuler'));
    }

    public function harian(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $selectedDate = $request->get('date') ?: now()->format('Y-m-d');

        $ekstrakurikuler->load([
            'tahunAjaran',
            'pembina',
            'peserta.siswa.user',
        ]);

        // daftar peserta ekstra (siswa_ekstrakurikuler)
        $students = $ekstrakurikuler->peserta
            ->map(function ($p) {
                return [
                    'siswa_ekstrakurikuler_id' => $p->siswa_ekstrakurikuler_id,
                    'name' => $p->siswa?->nama ?? $p->siswa?->user?->name ?? '-',
                    'avatar' => '/build/images/user/avatar-1.jpg',
                ];
            })
            ->values()
            ->all();

        // absensi map untuk tanggal ini (key: siswa_ekstrakurikuler_id)
        $attendanceMap = KehadiranEkstrakurikuler::query()
            ->where('ekstrakurikuler_id', $ekstrakurikuler->ekstrakurikuler_id)
            ->whereDate('tanggal', $selectedDate)
            ->get()
            ->keyBy('siswa_ekstrakurikuler_id');

        return view('absensi_ekstrakurikuler.index', [
            'ekstrakurikuler' => $ekstrakurikuler,
            'selectedDate' => $selectedDate,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
        ]);
    }

    public function storeHarian(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $maxBackDays = Auth::user()->hasRole('Bagian Akademik') ? 30 : 7;

        if ($request->has('attendances') && is_array($request->input('attendances'))) {
            $data = $request->validate([
                'tanggal' => [
                    'required',
                    'date',
                    'before_or_equal:today',
                    'after_or_equal:' . now()->subDays($maxBackDays)->toDateString(),
                ],
                'attendances' => ['required', 'array'],
                'attendances.*.siswa_ekstrakurikuler_id' => ['required', 'integer'],
                'attendances.*.status' => ['required', Rule::in(['hadir', 'alpha', 'sakit', 'izin'])],
                'attendances.*.note' => ['nullable', 'string'],
            ]);

            foreach ($data['attendances'] as $att) {
                if (in_array($att['status'], ['izin', 'sakit'], true) && blank($att['note'] ?? null)) {
                    return back()->with('warning', 'Keterangan wajib diisi untuk status Izin / Sakit. Mohon periksa kembali inputan Anda.');
                }
            }

            $validIds = SiswaEkstrakurikuler::query()
                ->where('ekstrakurikuler_id', $ekstrakurikuler->ekstrakurikuler_id)
                ->pluck('siswa_ekstrakurikuler_id')
                ->toArray();

            $dateFormatted = Carbon::parse($data['tanggal'])->format('Y-m-d');

            DB::beginTransaction();
            try {
                $savedCount = 0;
                foreach ($data['attendances'] as $att) {
                    if (!in_array($att['siswa_ekstrakurikuler_id'], $validIds)) {
                        continue;
                    }

                    $where = [
                        'ekstrakurikuler_id'       => $ekstrakurikuler->ekstrakurikuler_id,
                        'siswa_ekstrakurikuler_id' => $att['siswa_ekstrakurikuler_id'],
                        'tanggal'                  => $dateFormatted,
                    ];

                    $values = [
                        'status'     => $att['status'],
                        'note'       => $att['note'] ?? null,
                        'updated_by' => Auth::id(),
                    ];

                    KehadiranEkstrakurikuler::query()->updateOrCreate(
                        $where,
                        $values + ['created_by' => Auth::id()]
                    );
                    $savedCount++;
                }

                DB::commit();
                return back()->with('success', "Absensi ekstrakurikuler tersimpan ($savedCount siswa).");
            } catch (\Throwable $th) {
                DB::rollBack();
                return back()->with('error', 'Gagal menyimpan absensi ekstrakurikuler: ' . $th->getMessage());
            }
        }

        // Single fallback
        $data = $request->validate([
            'tanggal' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . now()->subDays($maxBackDays)->toDateString(),
            ],
            'siswa_ekstrakurikuler_id' => ['required', 'integer'],
            'status' => ['required', Rule::in(['hadir', 'alpha', 'sakit', 'izin'])],
            'note' => ['nullable', 'string'],
        ]);

        if (in_array($data['status'], ['izin', 'sakit'], true) && blank($data['note'])) {
            return back()->with('warning', 'Keterangan wajib diisi untuk status Izin / Sakit.');
        }

        // pastikan peserta itu benar milik ekstra ini
        $valid = SiswaEkstrakurikuler::query()
            ->where('siswa_ekstrakurikuler_id', $data['siswa_ekstrakurikuler_id'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->ekstrakurikuler_id)
            ->exists();

        if (!$valid) {
            return back()->with('warning', 'Peserta tidak valid untuk ekstrakurikuler ini.');
        }

        $where = [
            'ekstrakurikuler_id' => $ekstrakurikuler->ekstrakurikuler_id,
            'siswa_ekstrakurikuler_id' => $data['siswa_ekstrakurikuler_id'],
            'tanggal' => Carbon::parse($data['tanggal'])->format('Y-m-d'),
        ];

        $values = [
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'updated_by' => Auth::id(),
        ];

        KehadiranEkstrakurikuler::query()->updateOrCreate(
            $where,
            $values + ['created_by' => Auth::id()]
        );

        return back()->with('success', 'Absensi ekstrakurikuler tersimpan.');
    }

    public function rekap(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->load([
            'tahunAjaran',
            'pembina',
            'peserta.siswa.user',
        ]);

        $today = now()->toDateString();
        $defaultFrom = now()->startOfMonth()->toDateString();
        $defaultTo   = $today;

        $rawFrom = $request->query('from');
        $rawTo   = $request->query('to');

        try {
            $from = $rawFrom
                ? Carbon::createFromFormat('Y-m-d', $rawFrom)->toDateString()
                : $defaultFrom;
        } catch (\Throwable $e) {
            $from = $defaultFrom;
        }

        try {
            // kalau to kosong tapi from ada => anggap 1 hari
            $to = $rawTo
                ? Carbon::createFromFormat('Y-m-d', $rawTo)->toDateString()
                : ($rawFrom ? $from : $defaultTo);
        } catch (\Throwable $e) {
            $to = ($rawFrom ? $from : $defaultTo);
        }

        if ($to > $today) $to = $today;

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $peserta = $ekstrakurikuler->peserta;

        $stats = KehadiranEkstrakurikuler::query()
            ->select([
                'siswa_ekstrakurikuler_id',
                DB::raw("SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) as alpha"),
                DB::raw("SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN status='izin'  THEN 1 ELSE 0 END) as izin"),
            ])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->ekstrakurikuler_id)
            ->whereBetween('tanggal', [$from, $to])
            ->groupBy('siswa_ekstrakurikuler_id')
            ->get()
            ->keyBy('siswa_ekstrakurikuler_id');

        $rows = $peserta->map(function ($p) use ($stats) {
            $s = $stats->get($p->siswa_ekstrakurikuler_id);

            $hadir = (int)($s->hadir ?? 0);
            $alpha = (int)($s->alpha ?? 0);
            $sakit = (int)($s->sakit ?? 0);
            $izin  = (int)($s->izin  ?? 0);

            $total = $hadir + $alpha + $sakit + $izin;
            $persen = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;

            return [
                'name' => $p->siswa?->nama ?? $p->siswa?->user?->name ?? '-',
                'avatar' => '/build/images/user/avatar-1.jpg',
                'hadir' => $hadir,
                'alpha' => $alpha,
                'sakit' => $sakit,
                'izin' => $izin,
                'persen' => $persen,
            ];
        })->values()->all();

        return view('absensi_ekstrakurikuler.ekstrakurikuler_rekap', [
            'ekstrakurikuler' => $ekstrakurikuler,
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
        ]);
    }
}
