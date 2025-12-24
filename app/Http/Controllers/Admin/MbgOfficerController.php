<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetugasMbg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MbgOfficerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officers = PetugasMbg::paginate(20);
        return view('admin.mbg-officers.index', compact('officers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mbg-officers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:petugas_mbg,username|string|max:100',
            'password' => 'required|confirmed|string|min:6',
        ]);

        PetugasMbg::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.mbg-officers.index')
            ->with('success', 'Data petugas MBG berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $officer = PetugasMbg::findOrFail($id);
        return view('admin.mbg-officers.show', compact('officer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $officer = PetugasMbg::findOrFail($id);
        return view('admin.mbg-officers.edit', compact('officer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $officer = PetugasMbg::findOrFail($id);

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('petugas_mbg', 'username')->ignore($officer->id),
            ],
            'password' => 'nullable|confirmed|string|min:6',
        ]);

        $data = [
            'username' => $request->username,
        ];

        // Update password jika ada perubahan
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $officer->update($data);

        return redirect()->route('admin.mbg-officers.index')
            ->with('success', 'Data petugas MBG berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $officer = PetugasMbg::findOrFail($id);
        $officer->delete();

        return redirect()->route('admin.mbg-officers.index')
            ->with('success', 'Data petugas MBG berhasil dihapus');
    }

    /**
     * Verify password for MBG officer.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $officer = PetugasMbg::where('username', $request->username)->first();

        if (!$officer || !Hash::check($request->password, $officer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil'
        ]);
    }
}
