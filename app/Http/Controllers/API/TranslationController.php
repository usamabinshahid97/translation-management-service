<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/translations",
     *     summary="Get paginated list of translations",
     *     tags={"Translations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Filter by locale code",
     *         required=false,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Parameter(
     *         name="tag",
     *         in="query",
     *         description="Filter by tag name",
     *         required=false,
     *         @OA\Schema(type="string", example="mobile")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in keys and values",
     *         required=false,
     *         @OA\Schema(type="string", example="welcome")
     *     ),
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="Filter by exact key",
     *         required=false,
     *         @OA\Schema(type="string", example="welcome.message")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of translations",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Translation")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Translation::with(['locale', 'tags']);
        
        if ($request->has('locale')) {
            $query->byLocale($request->locale);
        }
        
        if ($request->has('tag')) {
            $query->byTag($request->tag);
        }
        
        if ($request->has('search')) {
            $query->search($request->search);
        }
        
        if ($request->has('key')) {
            $query->byKey($request->key);
        }
        
        $translations = $query->paginate($request->get('per_page', 15));
        
        return response()->json($translations);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'value' => 'required|string',
            'locale_code' => 'required|string|exists:locales,code',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|exists:tags,name',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $locale = Locale::where('code', $request->locale_code)->first();
        
        $existingTranslation = Translation::where('key', $request->key)
            ->where('locale_id', $locale->id)
            ->first();
        
        if ($existingTranslation) {
            return response()->json(['error' => 'Translation already exists for this key and locale'], 409);
        }
        
        DB::beginTransaction();
        
        try {
            $translation = Translation::create([
                'key' => $request->key,
                'value' => $request->value,
                'locale_id' => $locale->id,
            ]);
            
            if ($request->has('tags')) {
                $tagIds = Tag::whereIn('name', $request->tags)->pluck('id')->toArray();
                $translation->tags()->sync($tagIds);
            }
            
            DB::commit();
            
            Cache::tags(['translations'])->flush();
            
            return response()->json($translation->load(['locale', 'tags']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create translation'], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $translation = Translation::with(['locale', 'tags'])->find($id);
        
        if (!$translation) {
            return response()->json(['error' => 'Translation not found'], 404);
        }
        
        return response()->json($translation);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $translation = Translation::find($id);
        
        if (!$translation) {
            return response()->json(['error' => 'Translation not found'], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:255',
            'value' => 'sometimes|string',
            'locale_code' => 'sometimes|string|exists:locales,code',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|exists:tags,name',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        DB::beginTransaction();
        
        try {
            if ($request->has('locale_code')) {
                $locale = Locale::where('code', $request->locale_code)->first();
                $translation->locale_id = $locale->id;
            }
            
            if ($request->has('key')) {
                $translation->key = $request->key;
            }
            
            if ($request->has('value')) {
                $translation->value = $request->value;
            }
            
            $translation->save();
            
            if ($request->has('tags')) {
                $tagIds = Tag::whereIn('name', $request->tags)->pluck('id')->toArray();
                $translation->tags()->sync($tagIds);
            }
            
            DB::commit();
            
            Cache::tags(['translations'])->flush();
            
            return response()->json($translation->load(['locale', 'tags']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update translation'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $translation = Translation::find($id);
        
        if (!$translation) {
            return response()->json(['error' => 'Translation not found'], 404);
        }
        
        $translation->delete();
        
        Cache::tags(['translations'])->flush();
        
        return response()->json(['message' => 'Translation deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/translations/export",
     *     summary="Export translations for frontend use",
     *     tags={"Translations"},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Locale code to export",
     *         required=true,
     *         @OA\Schema(type="string", example="en")
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Filter by tag names",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Key-value pairs of translations",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "welcome.message": "Welcome to our application",
     *                 "nav.home": "Home",
     *                 "btn.save": "Save"
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation errors")
     * )
     */
    public function export(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locale' => 'required|string|exists:locales,code',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|exists:tags,name',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $cacheKey = 'translations_export_' . $request->locale . '_' . md5(json_encode($request->tags ?? []));
        
        $translations = Cache::tags(['translations'])->remember($cacheKey, 300, function () use ($request) {
            $query = Translation::select(['key', 'value'])
                ->byLocale($request->locale);
            
            if ($request->has('tags')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('name', $request->tags);
                });
            }
            
            return $query->get()->pluck('value', 'key')->toArray();
        });
        
        return response()->json($translations);
    }

    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'locale' => 'sometimes|string|exists:locales,code',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|exists:tags,name',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $query = Translation::with(['locale', 'tags'])
            ->search($request->q);
        
        if ($request->has('locale')) {
            $query->byLocale($request->locale);
        }
        
        if ($request->has('tags')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('name', $request->tags);
            });
        }
        
        $translations = $query->paginate($request->get('per_page', 15));
        
        return response()->json($translations);
    }
}
