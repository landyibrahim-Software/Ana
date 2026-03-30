<?php

namespace App\Http\Controllers\Backend;

use App\Models\Code;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CodeController extends Controller
{
    public function index()
    {
        $codes = Code::all();
        return response()->json($codes);
    }

    public function show($id)
    {
        $code = Code::find($id);
        return response()->json($code);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $code = Code::create($validated);
        return response()->json($code, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);
        $code = Code::findOrFail($id);
        $code->update($validated);
        return response()->json($code);
    }

    public function destroy($id)
    {
        $code = Code::findOrFail($id);
        $code->delete();
        return response()->json(null, 204);
    }
}