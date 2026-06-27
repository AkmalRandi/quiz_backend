<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataPelajaran;
use Illuminate\Support\Facades\Validator;

class MataPelajaranController extends Controller
{
    public function index()
    {
        return response()->json(MataPelajaran::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_mapel' => 'required|unique:mata_pelajaran'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $mapel = MataPelajaran::create($request->all());
        return response()->json(['message' => 'Mata pelajaran created', 'data' => $mapel], 201);
    }

    public function update(Request $request, $id)
    {
        $mapel = MataPelajaran::find($id);
        if (!$mapel) return response()->json(['error' => 'Not found'], 404);
        $mapel->update($request->all());
        return response()->json(['message' => 'Updated', 'data' => $mapel]);
    }

    public function destroy($id)
    {
        $mapel = MataPelajaran::find($id);
        if (!$mapel) return response()->json(['error' => 'Not found'], 404);
        $mapel->delete();
        return response()->json(['message' => 'Deleted']);
    }
}