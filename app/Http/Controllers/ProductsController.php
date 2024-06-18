<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreValidation;
use App\Http\Requests\ProductUpdateValidation;
use App\Models\ProductCondition;
use App\Models\ProductImages;
use App\Models\Products;
use App\Models\ProductsCategory;
use App\Models\ProductVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;
        $status = ($request['status'] != '' || $request['status'] != null) ? $request['status'] : null;
        $vendor = ($request['vendor'] != '' || $request['vendor'] != null) ? $request['vendor'] : null;
        $category = ($request['category'] != '' || $request['category'] != null) ? $request['category'] : null;


        $data = Products::when($search, function ($q) use ($search) {
            $q->where('name', 'ilike', '%' . $search . '%');
        })->when($vendor, function ($q) use ($vendor) {
            $q->where('vendor', 'ilike', '%' . $vendor . '%');
        })->when($category, function ($q) use ($category) {
            $q->where('product_category_id', $category);
        })->with('productPrice', 'productCategory', 'productImages', 'productShipping', 'productCondition', 'merchant', 'productVariants.variantItems', 'productVariants.variant');



        $details = [
            'from' =>   $request->skip + 1,
            'to'   =>   min(($request->skip + $request->take), $data->count()),
            'total' =>   $data->count()
        ];
        $message = ($data->count() == 0) ? "No Results Found" : "Results Found";
        return response([
            'data'      => $data->skip($request->skip)
                ->take($request->take)
                ->orderBy('id', 'desc')
                ->get(),
            'details'   => $details,
            'message'   => $message
        ]);
    }
    public function store(ProductStoreValidation $request)
    {

        $validate = $request->validated();

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $product = Products::create([
                'product_category_id'   => $request['product_category_id'],
                'product_condition_id'  => $request['product_condition_id'],
                'name'                  => $request['name'],
                'quantity'              => $request['quantity'],
                'description'           => $request['description'],
                'vendor'                => $request['vendor'],
                'weight'                => $request['weight'],
                'storage_condition'     => $request['storage_condition'],
                'pre_order'             => $request['pre_order'],
                'status'                => $request['status'],
                'draft'                 => true,
                'user_id'               => Auth::id()
            ]);
            $product->productPrice()->createMany($validate['price']);
            $product->productShipping()->create($validate['shipping']);
            $product->productVariants()->createMany([
                'variant_id'    => $validate['variants']['variant_id']
            ]);

            $product->productVariants->variantItems->createMany($validate['variants']['variants_items']);

            return response([
                'id' => $product->id,
                'message' => 'Data saved.'
            ]);
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function update(ProductUpdateValidation $request, $id)
    {
        $validate = $request->validated();

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            $product = Products::find($id);
            if (!$product) {
                return response([
                    'message' => 'Invalid product id'
                ], 400);
            } else {

                Products::updateOrCreate(
                    ['id' => $id],
                    [
                        'product_category_id'   => $request['product_category_id'],
                        'name'                  => $request['name'],
                        'quantity'              => $request['quantity'],
                        'description'           => $request['description'],
                        'vendor'                => $request['vendor'],
                        'weight'                => $request['weight'],
                        'storage_condition'     => $request['storage_condition'],
                        'pre_order'             => $request['pre_order'],
                        'draft'                 => $request['draft'],
                        'status'                => $request['status'],
                    ]
                );

                $product->productPrice()->createMany($request->price);

                $product->productShipping()->updateOrCreate(
                    ['product_id' => $id],
                    [
                        'weight'            => $request['shipping']['weight'],
                        'shipping_fee'      => $request['shipping']['shipping_fee']
                    ]
                );

                $product->productVariants()->updateOrCreate(
                    [
                        'product_id' => $id,
                        'variant_id' => $request['variants']['variant_id']
                    ],
                    [
                        'variant_id' => $request['variants']['variant_id']
                    ]
                );
                foreach ($request->variants['variants_items'] as $variantItemData) {
                    $product->productVariants->variantItems()->updateOrCreate(
                        [
                            'product_variant_id' => $product->productVariants->id,

                        ],
                        [
                            'variant_item_name' => $variantItemData['variant_item_name'],
                            'price' => $variantItemData['price'],
                            'stock' => $variantItemData['stock']
                        ]
                    );
                }
                return response([
                    'message' => 'Product updated.'
                ], 200);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function delete($id)
    {

        $product = Products::find($id);
        if (!$product) {
            return response(['message' => 'Invalid id'], 400);
        } else {
            $product->delete();
            return response(['message' => 'Product deleted.']);
        }
    }

    public function get($id)
    {
        $product = Products::where('id', $id)->with('productPrice', 'productCategory', 'productImages', 'productVideos', 'productShipping', 'productCondition', 'merchant', 'productVariants.variantItems', 'productVariants.variant')->first();

        if (!$product) {
            return response(['message' => 'Invalid product id'], 400);
        } else {
            return response(['data' => $product]);
        }
    }

    public function productImages(Request $request, $id)
    {
        $payload = $request->validate([
            'file' => 'required|image|mimes:jpeg,png',
            'auth'  => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            if (isset($id)) {

                $file = $payload['file'];

                $product = Products::find($id);
                if ($product != null) {
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                    $handle = fopen($file->getPathname(), 'rb');
                    $path = 'products/' . $id . '/' . $filename;
                    $path_exist = 'products/' . $id . '/';
                    if (!File::exists(storage_path('app/public/' . $path_exist))) {
                        File::makeDirectory(storage_path('app/public/' . $path_exist), 0755, true);
                    }
                    $storageFile = fopen(storage_path('app/public/' . $path), 'w');
                    while (!feof($handle)) {
                        $chunk = fread($handle, 4096);
                        if (connection_aborted()) {
                            fclose($handle);
                            fclose($storageFile);
                            Storage::disk('local')->delete($path);
                            return response()->json(['message' => 'File upload cancelled']);
                        }
                        fwrite($storageFile, $chunk);
                    }
                    fclose($handle);
                    fclose($storageFile);
                    $payload = [
                        'product_id'    => $id,
                        'file_name'     => $file->getClientOriginalName(),
                        'file_path'     => $path
                    ];
                    ProductImages::create($payload);
                    return response()->json(['message' => 'File uploaded successfully']);
                } else {
                    return response(['message' => 'Invalid id'], 400);
                }
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        } else {
            return response(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function productImagesDelete($id)
    {

        $product_images = ProductImages::find($id);
        if (!$product_images) {
            return response([
                'message' => 'Invalid product images id'
            ], 400);
        } else {

            $product_images->delete();

            return response([
                'message' => 'Data and image deleted.'
            ]);
        }
    }



    public function productVideo(Request $request, $id)
    {
        $payload = $request->validate([
            'file' => 'required|file|mimes:mp4,mov,avi',
            'auth' => 'string|required'
        ]);

        $auth = (new ActionAuthorizationController)->authChecker($request);
        $check_auth = (new ActionAuthorizationController)->checkAuth($auth);

        if ($check_auth) {
            if (isset($id)) {

                $file = $payload['file'];

                $product = Products::find($id);
                if ($product != null) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = uniqid() . '.' . $extension;
                    $handle = fopen($file->getPathname(), 'rb');
                    $path = 'products/' . $id . '/videos/' . $filename;
                    $path_exist = 'products/' . $id . '/videos/';
                    if (!File::exists(storage_path('app/public/' . $path_exist))) {
                        File::makeDirectory(storage_path('app/public/' . $path_exist), 0755, true);
                    }
                    $storageFile = fopen(storage_path('app/public/' . $path), 'w');
                    while (!feof($handle)) {
                        $chunk = fread($handle, 4096);
                        if (connection_aborted()) {
                            fclose($handle);
                            fclose($storageFile);
                            Storage::disk('local')->delete($path);
                            return response()->json(['message' => 'File upload cancelled']);
                        }
                        fwrite($storageFile, $chunk);
                    }
                    fclose($handle);
                    fclose($storageFile);
                    $payload = [
                        'product_id'    => $id,
                        'file_name'     => $file->getClientOriginalName(),
                        'file_path'     => $path
                    ];
                    ProductVideo::create($payload);
                    return response()->json(['message' => 'File uploaded successfully']);
                } else {
                    return response(['message' => 'Invalid id'], 400);
                }
            } else {
                return response(['message' => 'Invalid id'], 400);
            }
        }
    }

    public function deleteProductVideo($id)
    {
        $product_video = ProductVideo::find($id);

        if (!$product_video) {
            return response(['message'  => 'Product Video Not Found']);
        } else {
            $product_video->delete();
            return response([
                'message' => 'Product Video deleted.'
            ]);
        }
    }


    public function showProductCategories()
    {
        return response([
            'data' => ProductsCategory::all()
        ]);
    }

    public function showProductConditions()
    {
        $conditions = ProductCondition::all();
        return response([
            'data' => $conditions,
        ]);
    }
    public function merchantProducts(Request $request)
    {
        $search = ($request['search'] != '' || $request['search'] != null) ? $request['search'] : null;
        $status = ($request['status'] != '' || $request['status'] != null) ? $request['status'] : null;
        $vendor = ($request['vendor'] != '' || $request['vendor'] != null) ? $request['vendor'] : null;
        $category = ($request['category'] != '' || $request['category'] != null) ? $request['category'] : null;


        $data = Products::when($search, function ($q) use ($search) {
            $q->where('name', 'ilike', '%' . $search . '%');
        })->when($vendor, function ($q) use ($vendor) {
            $q->where('vendor', 'ilike', '%' . $vendor . '%');
        })->when($category, function ($q) use ($category) {
            $q->where('product_category_id', $category);
        })
            ->where('user_id', Auth::id())
            ->with('productPrice', 'productCategory', 'productImages', 'productShipping', 'productCondition');



        $details = [
            'from' =>   $request->skip + 1,
            'to'   =>   min(($request->skip + $request->take), $data->count()),
            'total' =>   $data->count()
        ];
        $message = ($data->count() == 0) ? "No Results Found" : "Results Found";
        return response([
            'data'      => $data->skip($request->skip)
                ->take($request->take)
                ->orderBy('id', 'desc')
                ->get(),
            'details'   => $details,
            'message'   => $message
        ]);
    }
}