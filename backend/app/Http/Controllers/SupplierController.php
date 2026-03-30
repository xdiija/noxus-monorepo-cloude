<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierStoreUpdateRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SupplierController extends Controller
{
    public function __construct(
        protected Supplier $supplier
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 10);
        $filter  = $request->query('filter');

        $query = $this->supplier->newQuery();

        if ($filter) {
            $query->where('nome_fantasia', 'LIKE', "%{$filter}%");
        }

        $suppliers = $query->paginate($perPage);

        return SupplierResource::collection($suppliers);
    }

    public function store(SupplierStoreUpdateRequest $request): SupplierResource
    {
        $supplier = $this->supplier->create($request->validated());

        return new SupplierResource($supplier);
    }

    public function show(string $id): SupplierResource|JsonResponse
    {
        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            return response()->json([
                'message' => 'Fornecedor não encontrado ou inacessível.',
            ], 404);
        }

        return new SupplierResource($supplier);
    }

    public function update(SupplierStoreUpdateRequest $request, string $id): SupplierResource|JsonResponse
    {
        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            return response()->json([
                'message' => 'Fornecedor não encontrado ou inacessível.',
            ], 404);
        }

        $supplier->update($request->validated());

        return new SupplierResource($supplier);
    }

    public function changeStatus(string $id): SupplierResource|JsonResponse
    {
        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            return response()->json([
                'message' => 'Fornecedor não encontrado ou inacessível.',
            ], 404);
        }

        $supplier->status = $supplier->status === 1 ? 2 : 1;
        $supplier->save();

        return new SupplierResource($supplier);
    }

    public function destroy(string $id): JsonResponse|Response
    {
        $supplier = $this->supplier->find($id);

        if (!$supplier) {
            return response()->json([
                'message' => 'Fornecedor não encontrado ou inacessível.',
            ], 404);
        }

        $supplier->delete();

        return response()->noContent();
    }
}
