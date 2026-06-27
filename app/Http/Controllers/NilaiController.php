<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index()
    {
        try {
            $data = Nilai::with(['siswa', 'mataPelajaran'])->get();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getBySiswa($id_siswa)
    {
        try {
            $data = Nilai::where('siswa_id', $id_siswa)
                ->with('mataPelajaran')
                ->get();
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:users,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'nilai' => 'required|integer|min:0|max:100',
            'jawaban' => 'nullable|json'
        ]);

        try {
            $nilai = Nilai::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $nilai
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // implement
    }

    public function destroy($id)
    {
        try {
            $nilai = Nilai::findOrFail($id);
            $nilai->delete();
            return response()->json([
                'success' => true,
                'message' => 'Deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}