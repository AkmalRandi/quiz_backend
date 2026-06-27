<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nilai;
use Illuminate\Support\Facades\Validator;

class NilaiController extends Controller
{
    public function index()
    {
        return response()->json(Nilai::with(['user', 'kuis'])->get());
    }

    public function getByUser($id_user)
    {
        return response()->json(Nilai::where('id_user', $id_user)->with('kuis')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
            'id_kuis' => 'required|exists:kuis,id',
            'skor' => 'required|integer|between:0,100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $nilai = Nilai::create($request->all());
        return response()->json(['message' => 'Nilai saved', 'nilai' => $nilai], 201);
    }
}