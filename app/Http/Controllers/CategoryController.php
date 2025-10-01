<?php
// app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends ApiController
{
    public function index()
    {
        try {
            $categories = Category::all();

            return $this->success($categories, 'Categories retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories',
                'description' => 'nullable|string',
            ]);

            $category = Category::create($validated);

            return $this->success($category, 'Category created successfully', 201);

        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to create category: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string',
            ]);

            $category->update($validated);

            return $this->success($category, 'Category updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Category not found');
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->error('Failed to update category: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has products
            if ($category->products()->exists()) {
                return $this->error('Cannot delete category with associated products', 422);
            }

            $category->delete();

            return $this->success(null, 'Category deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound('Category not found');
        } catch (\Exception $e) {
            return $this->error('Failed to delete category: ' . $e->getMessage(), 500);
        }
    }
}
