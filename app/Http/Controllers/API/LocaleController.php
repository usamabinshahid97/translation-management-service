<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocaleController extends Controller
{
    public function index(): JsonResponse
    {
        $locales = Locale::all();
        return response()->json($locales);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:locales',
            'name' => 'required|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $locale = Locale::create($request->only(['code', 'name', 'is_active']));

        return response()->json($locale, 201);
    }

    public function show($id): JsonResponse
    {
        $locale = Locale::find($id);

        if (!$locale) {
            return response()->json(['error' => 'Locale not found'], 404);
        }

        return response()->json($locale);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $locale = Locale::find($id);

        if (!$locale) {
            return response()->json(['error' => 'Locale not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|max:10|unique:locales,code,' . $id,
            'name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $locale->update($request->only(['code', 'name', 'is_active']));

        return response()->json($locale);
    }

    public function destroy($id): JsonResponse
    {
        $locale = Locale::find($id);

        if (!$locale) {
            return response()->json(['error' => 'Locale not found'], 404);
        }

        if ($locale->translations()->count() > 0) {
            return response()->json(['error' => 'Cannot delete locale with existing translations'], 409);
        }

        $locale->delete();

        return response()->json(['message' => 'Locale deleted successfully']);
    }
}