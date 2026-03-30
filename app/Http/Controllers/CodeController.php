<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Code;

class CodeController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        return Code::all();
    }

    // Show the form for creating a new resource.
    public function create()
    {
        // return view for creating code
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $code = Code::create($request->all());
        return response()->json($code, 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        return Code::findOrFail($id);
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        // return view for editing code
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $code = Code::findOrFail($id);
        $code->update($request->all());
        return response()->json($code, 200);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        Code::destroy($id);
        return response()->json(null, 204);
    }
}