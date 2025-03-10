<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Todo\TodoCreateRequest;
use App\Http\Requests\Todo\TodoUpdateRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Log;

/**
 * @OA\OpenApi(
 *
 *   @OA\Info(
 *       title="Returns Services API",
 *       version="1.0.0",
 *       description="API documentation for Todo items",
 *
 *       @OA\Contact(
 *           email="test@example.com"
 *       ),
 *   ),
 *
 *   @OA\Server(
 *       description="Returns Services API",
 *       url=L5_SWAGGER_CONST_HOST
 *   ),
 *
 *   @OA\PathItem(
 *       path="/"
 *   )
 *  ),
 */
class TodoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth:sanctum',
        ];

    }

    /**
     * @OA\Get(
     *    tags={"Todos"},
     *     path="/todos",
     *     summary="List of all todo items",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *     ),
     *
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *
     *            @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Unauthorized."),
     *           )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *
     *           @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Server error. Try again later."),
     *           )
     *      )
     *  )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1'],
        ]);

        $todos = Todo::query()
            // ->where('user_id', auth()->id())
            ->orderBy('favorite', 'desc')
            ->simplePaginate($data['limit'] ?? 10, ['*'], 'page', $data['page'] ?? 1);

        return TodoResource::collection($todos);
    }

    /**
     * @OA\Post(
     *    tags={"Todos"},
     *     path="/todos",
     *     summary="Create new todo item",
     *     description="Colors are in hex format. There is the list of available colors: #ffffff, #ff0000, #00ff00, #0000ff, #ffff00, #00ffff, #ff00ff, #000000, #808080, #ff8000, #8000ff",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *              required=true,
     *
     *              @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="title", type="string", example="Buy milk"),
     *                 @OA\Property(property="description", type="string", example="Buy milk"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="favorite", type="boolean", example=false),
     *                 @OA\Property(property="color", type="string", example="#ff0000"),
     *              ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Buy milk"),
     *                 @OA\Property(property="description", type="string", example="Buy milk"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="favorite", type="boolean", example=false),
     *                 @OA\Property(property="color", type="string", example="#ff0000"),
     *           )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *     )
     *  )
     */
    public function store(TodoCreateRequest $request): TodoResource|JsonResponse
    {
        $data = $request->validated();

        try {
            $data['user_id'] = auth('sanctum')->id();

            $todo = Todo::create($data);

            return TodoResource::make($todo);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => 'Server error. Try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/todos/{id}",
     *     tags={"Todos"},
     *     summary="Get todo item",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *
     *     @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Buy milk"),
     *                 @OA\Property(property="description", type="string", example="Buy milk"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="favorite", type="boolean", example=false),
     *                 @OA\Property(property="color", type="string", example="#ff0000"),
     *           )
     *     ),
     *
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *     ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *     )
     * )
     */
    public function show(string $id): TodoResource|JsonResponse
    {
        try {
            $todo = Todo::query()->where('user_id', auth('sanctum')->id())->findOrFail($id);

            return TodoResource::make($todo);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => 'Server error. Try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/todos/{id}",
     *     tags={"Todos"},
     *     summary="Update todo item",
     *     description="Colors are in hex format. There is the list of available colors: #ffffff, #ff0000, #00ff00, #0000ff, #ffff00, #00ffff, #ff00ff, #000000, #808080, #ff8000, #8000ff",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *              required=true,
     *
     *              @OA\JsonContent(
     *                 type="object",
     *
     *                 @OA\Property(property="title", type="string", example="Buy milk"),
     *                 @OA\Property(property="description", type="string", example="Buy milk"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="favorite", type="boolean", example=false),
     *                 @OA\Property(property="color", type="string", example="#ff0000"),
     *           )
     *      ),
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *     ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *     ),
     * )
     */
    public function update(TodoUpdateRequest $request, string $id): TodoResource|JsonResponse
    {
        $data = $request->validated();

        try {
            $todo = Todo::query()->where('user_id', auth('sanctum')->id())->findOrFail($id);

            $todo->update($data);

            return TodoResource::make($todo);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => 'Server error. Try again later.',
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/todos/{id}",
     *     tags={"Todos"},
     *     summary="Delete todo item",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Todo not found",
     *     ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *     ),
     * )
     */
    public function destroy(string $id)
    {
        try {
            $todo = Todo::query()->where('user_id', auth('sanctum')->id())->findOrFail($id);

            $todo->delete();

            return response()->json([
                'message' => 'Todo deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Todo not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['error' => $e]);

            return response()->json([
                'message' => 'Server error. Try again later.',
            ], 500);
        }
    }
}
