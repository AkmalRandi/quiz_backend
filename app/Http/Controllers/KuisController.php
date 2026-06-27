<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kuis;
use Illuminate\Support\Facades\Validator;

class KuisController extends Controller
{
    public function index()
    {
        return response()->json(Kuis::with('mataPelajaran')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mapel' => 'required|exists:mata_pelajaran,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $kuis = Kuis::create($request->all());
        return response()->json(['message' => 'Kuis created successfully', 'kuis' => $kuis], 201);
    }

    public function update(Request $request, $id)
    {
        $kuis = Kuis::find($id);
        if (!$kuis) {
            return response()->json(['error' => 'Kuis not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_mapel' => 'sometimes|required|exists:mata_pelajaran,id',
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'sometimes|required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $kuis->update($request->all());
        return response()->json(['message' => 'Kuis updated successfully', 'kuis' => $kuis]);
    }

    public function destroy($id)
    {
        $kuis = Kuis::find($id);
        if (!$kuis) {
            return response()->json(['error' => 'Kuis not found'], 404);
        }
        $kuis->delete();
        return response()->json(['message' => 'Kuis deleted successfully']);
    }
}