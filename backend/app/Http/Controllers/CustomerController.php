<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreUpdateRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(
        protected Customer $customer
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 10);
        $filter  = $request->query('filter');

        $query = $this->customer->newQuery();

        if ($filter) {
            $query->where('name', 'LIKE', "%{$filter}%");
        }

        $customers = $query->paginate($perPage);

        return CustomerResource::collection($customers);
    }

    public function store(CustomerStoreUpdateRequest $request): CustomerResource
    {
        $customer = $this->customer->create($request->validated());

        return new CustomerResource($customer);
    }

    public function show(string $id): CustomerResource|JsonResponse
    {
        $customer = $this->customer->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Cliente não encontrado ou inacessível.',
            ], 404);
        }

        return new CustomerResource($customer);
    }

    public function update(CustomerStoreUpdateRequest $request, string $id): CustomerResource|JsonResponse
    {
        $customer = $this->customer->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Cliente não encontrado ou inacessível.',
            ], 404);
        }

        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    public function changeStatus(string $id): CustomerResource|JsonResponse
    {
        $customer = $this->customer->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Cliente não encontrado ou inacessível.',
            ], 404);
        }

        $customer->status = $customer->status === 1 ? 2 : 1;
        $customer->save();

        return new CustomerResource($customer);
    }

    public function destroy(string $id): JsonResponse|Response
    {
        $customer = $this->customer->find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Cliente não encontrado ou inacessível.',
            ], 404);
        }

        $customer->delete();

        return response()->noContent();
    }
}
