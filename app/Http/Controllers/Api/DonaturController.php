<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donatur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonaturController extends Controller
{
    /**
     * List donatur.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Donatur::withCount('donasi');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $donatur = $query->latest('id')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $donatur,
        ]);
    }

    /**
     * Store new donatur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'tipe_donatur' => 'required|in:perorangan,lembaga,hamba_allah',
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'kategori' => 'required|in:rutin,insidental',
        ]);

        $donatur = Donatur::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data donatur berhasil ditambahkan.',
            'data' => $donatur,
        ], 201);
    }

    /**
     * Update donatur.
     */
    public function update(Request $request, Donatur $donatur): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'tipe_donatur' => 'required|in:perorangan,lembaga,hamba_allah',
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'kategori' => 'required|in:rutin,insidental',
        ]);

        $donatur->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data donatur berhasil diperbarui.',
            'data' => $donatur,
        ]);
    }

    /**
     * Options list for dropdown.
     */
    public function options(): JsonResponse
    {
        $donatur = Donatur::orderBy('nama')->get(['id', 'nama', 'no_hp']);

        return response()->json([
            'success' => true,
            'data' => $donatur,
        ]);
    }
}
