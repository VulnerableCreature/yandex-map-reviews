<?php

namespace App\Http\Controllers\Api;

use App\Application\Reviews\Contracts\ListOrganizationReviewsInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReviewController extends Controller
{
    private const int PER_PAGE = 50;

    public function __construct(private readonly ListOrganizationReviewsInterface $listReviews) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listReviews->execute(
            $request->user()->id,
            (int) $request->query('page', 1),
            self::PER_PAGE,
        );

        if ($paginator === null) {
            return response()->json(['message' => 'Организация не подключена.'], 404);
        }

        return response()->json([
            'data' => ReviewResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
