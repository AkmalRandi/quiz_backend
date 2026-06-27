<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soal;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Validator;

class SoalController extends Controller
{
    public function index()
    {
        $soal = Soal::with('mataPelajaran')->get();
        return response()->json($soal);
    }

    public function getByMapel($id_mapel)
    {
        $soal = Soal::where('id_mapel', $id_mapel)->with('mataPelajaran')->get();
        return response()->json($soal);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mapel' => 'required|exists:mata_pelajaran,id_mapel',
            'pertanyaan' => 'required',
            'opsi_a' => 'required',
            'opsi_b' => 'required',
            'opsi_c' => 'required',
            'opsi_d' => 'required',
            'jawaban_benar' => 'required|in:a,b,c,d'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $soal = Soal::create($request->all());
        return response()->json(['message' => 'Soal created successfully', 'data' => $soal], 201);
    }

    public function update(Request $request, $id)
    {
        $soal = Soal::find($id);
        if (!$soal) {
            return response()->json(['error' => 'Soal not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_mapel' => 'sometimes|required|exists:mata_pelajaran,id_mapel',
            'pertanyaan' => 'sometimes|required',
            'opsi_a' => 'sometimes|required',
            'opsi_b' => 'sometimes|required',
            'opsi_c' => 'sometimes|required',
            'opsi_d' => 'sometimes|required',
            'jawaban_benar' => 'sometimes|required|in:a,b,c,d'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $soal->update($request->all());
        return response()->json(['message' => 'Soal updated successfully', 'data' => $soal]);
    }

    public function destroy($id)
    {
        $soal = Soal::find($id);
        if (!$soal) {
            return response()->json(['error' => 'Soal not found'], 404);
        }

        $soal->delete();
        return response()->json(['message' => 'Soal deleted successfully']);
    }
}