<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // GET /api/users - Listar todos los contactos (solo una imagen)
    public function index(): JsonResponse
    {
        $users = User::with('images')->get();

        $users->each(function ($user) {
            $user->setRelation('images', $user->images->take(1));
        });

        return response()->json($users, 200);
    }

    // GET /api/users/{id} - Mostrar un contacto con todas sus imágenes
    public function show($id): JsonResponse
    {
        $user = User::with('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado'
            ], 404);
        }

        return response()->json($user, 200);
    }

    // POST /api/users - Crear un nuevo contacto
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone
        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('users', 'public');

            $user->images()->create([
                'url' => Storage::url($path)
            ]);
        }

        return response()->json(
            $user->load('images'),
            201
        );
    }

    // PUT/PATCH /api/users/{id}
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::with('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name',
            'email',
            'phone'
        ]));

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('users', 'public');

            $user->images()->create([
                'url' => Storage::url($path)
            ]);
        }

        return response()->json(
            $user->load('images'),
            200
        );
    }

    // DELETE /api/users/{id}
    public function destroy($id): JsonResponse
    {
        $user = User::with('images')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Contacto no encontrado'
            ], 404);
        }

        foreach ($user->images as $image) {

            $path = str_replace('/storage/', '', $image->url);

            Storage::disk('public')->delete($path);

            $image->delete();
        }

        $user->delete();

        return response()->json([
            'message' => 'Contacto eliminado correctamente'
        ], 200);
    }
}