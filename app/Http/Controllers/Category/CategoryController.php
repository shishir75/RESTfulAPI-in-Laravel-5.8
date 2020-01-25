<?php

namespace App\Http\Controllers\Category;

use App\Category;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CategoryController extends ApiController
{
    public function __construct()
    {
        $this->middleware('client.credentials')->only(['index', 'show']);

        $this->middleware('auth:api')->except(['index', 'show']);

        $this->middleware('transform.input:'. CategoryTransformer::class)->only(['store', 'update']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return $this->showAll($categories);
    }


    public function store(Request $request)
    {
        $this->allowedAdminAction(); // gate

        $rules = [
          'name' => 'required',
          'description' => 'required',
        ];

        $this->validate($request, $rules);

        $category = Category::create($request->all());

        return $this->showOne($category, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $this->showOne($category);
    }


    public function update(Request $request, Category $category)
    {
        $this->allowedAdminAction(); // gate

        $category->fill($request->only([
            'name',
            'description',
        ]));

        if ($category->isClean())
        {
            return $this->errorResponse('You need to specify any different value to update', 422);
        }

        $category->save();

        return $this->showOne($category);
    }


    public function destroy(Category $category)
    {
        $this->allowedAdminAction(); // gate

        $category->delete();

        return $this->showOne($category);
    }
}
