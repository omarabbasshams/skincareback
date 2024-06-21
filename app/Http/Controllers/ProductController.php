<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->file('image')) {
            // Delete the old image if it exists
            if ($product->image_path) {
                Storage::delete($product->image_path);
            }

            $imagePath = $request->file('image')->store('public/product_images');
            $product->update(['image_path' => $imagePath]);
        }

        return back()->with('success', 'Product image updated successfully.');
    }
}
