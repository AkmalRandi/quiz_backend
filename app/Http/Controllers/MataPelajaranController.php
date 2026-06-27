<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index()
    {
        try {
            $data = MataPelajaran::with('guru')->get();
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
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'guru_id' => 'required|exists:users,id'
        ]);

        try {
            $mapel = MataPelajaran::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $mapel
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
        $this->validate($request, [
            'nama' => 'string|max:100',
            'deskripsi' => 'nullable|string',
            'guru_id' => 'exists:users,id'
        ]);

        try {
            $mapel = MataPelajaran::findOrFail($id);
            $mapel->update($request->all());
            return response()->json([
                'success' => true,
                'data' => $mapel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mapel = MataPelajaran::findOrFail($id);
            $mapel->delete();
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