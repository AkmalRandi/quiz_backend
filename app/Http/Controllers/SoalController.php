<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Models\Opsi;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    public function index()
    {
        try {
            $data = Soal::with(['mataPelajaran', 'opsi'])->get();
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

    public function getByMapel($id_mapel)
    {
        try {
            $data = Soal::where('mapel_id', $id_mapel)
                ->with(['opsi'])
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
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'pertanyaan' => 'required|string',
            'gambar' => 'nullable|string',
            'jawaban_benar' => 'required|integer|min:0',
            'opsi' => 'required|array|min:2',
            'opsi.*.teks' => 'required|string',
            'opsi.*.gambar' => 'nullable|string',
            'opsi.*.is_benar' => 'required|boolean'
        ]);

        try {
            $soal = Soal::create($request->only(['mapel_id', 'pertanyaan', 'gambar', 'jawaban_benar']));

            foreach ($request->opsi as $item) {
                Opsi::create([
                    'soal_id' => $soal->id,
                    'teks' => $item['teks'],
                    'gambar' => $item['gambar'] ?? null,
                    'is_benar' => $item['is_benar']
                ]);
            }

            $soal->load('opsi');

            return response()->json([
                'success' => true,
                'data' => $soal
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
        // implement update jika diperlukan
    }

    public function destroy($id)
    {
        try {
            $soal = Soal::findOrFail($id);
            $soal->delete();
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