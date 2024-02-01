<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Image;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        $query->with('images');

        if ($request->has('category')) {
            $category = $request->input('category');
            $query->whereIn('category_id', $category);
        }

        if ($request->has('brand')) {
            $brand = $request->input('brand');
            $query->whereIn('brand', $brand);
        }

        if ($request->has('keyword')) {
            $query->whereIn('name', 'ilike', '%' . $request->input('keyword') . '%');
        }


        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price':
                $query->orderBy('price');
                break;
            case 'az':
                $query->orderBy('name', 'asc');
                break;
            case 'za':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $limit = $request->input('limit', 99);
        $products = $query->take($limit)->get();



        return response()->json(['products' => $products], 200);
    }



    public function store(Request $request)
    {
        $this->validateProductRequest($request);

        $product = Product::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'brand' => $request->input('brand'),
            'stock' => $request->input('stock', 0),
            'status' => $request->input('status', 'active'),
            'category_id' => $request->input('category_id'),
        ]);

        return response()->json(['product' => $product], 201);
    }

    public function show(Product $product)
    {
        $product->load('images');

        return response()->json(['product' => $product], 200);
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'name' => 'string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'brand' => 'string|max:255',
            'stock' => 'integer|min:0',
            'status' => Rule::in(['active', 'inactive']),
            'category_id' => 'exists:categories,id',
        ];

        $request->validate($rules);

        $product->update($request->only([
            'name',
            'description',
            'price',
            'brand',
            'stock',
            'status',
            'category_id',
        ]));

        return response()->json(['product' => $product], 200);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    private function validateProductRequest(Request $request, $product = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:products,name' . ($product ? ',' . $product->id : ''),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'brand' => 'required|string|max:255',
            'stock' => 'integer|min:0',
            'status' => Rule::in(['active', 'inactive']),
            'category_id' => 'required|exists:categories,id',
        ];

        $request->validate($rules);
    }

    public function activate(Product $product)
    {
        $product->update(['status' => 'active']);

        return response()->json(['message' => 'Product activated successfully'], 200);
    }

    public function deactivate(Product $product)
    {
        $product->update(['status' => 'inactive']);

        return response()->json(['message' => 'Product deactivated successfully'], 200);
    }

    public function addImage(Request $request, Product $product)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $image = Image::create([
            'product_id' => $product->id,
            'url' => $request->input('url'),
        ]);

        return response()->json(['image' => $image], 201);
    }

    public function updateImage(Request $request, Product $product, Image $image)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $image->update([
            'url' => $request->input('url'),
        ]);

        return response()->json(['image' => $image], 200);
    }

    public function deleteImage(Product $product, Image $image)
    {
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }

    public function getAvailableBrands()
    {
        $brands = Product::distinct('brand')->pluck('brand');

        return response()->json(['brands' => $brands], 200);
    }
}
