<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Product;
use Corcel\Model\Post;
use Illuminate\Http\Request;
use Woocommerce;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $itemel = Woocommerce::get('products/categories');
        //dd($itemel);
        $items = Product::latest('updated_at')->get();
        return view('admin.products.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, Product::rules());
        $item = new Product();
        //dd($request);
        $items = Woocommerce::get('products/categories');
        $item->title = $request->get("title");
        $item->price = $request->get("price");
        $item->description = $request->get("description");
        $categories=array();
        foreach($items as $ping)
        {
            array_push($categories,$ping["id"]);

        }
        //dd($item);
        $item->category = $categories[$request->get("standard_product_category")];
        $item->save();
        $item->image_path = $request->image_path;
        $item->save();
        //dd($item);
        $wp_id = Product::createWordpressPost($item->id);
        $item->wordpress_id = $wp_id;
        dd($item);
        //$ebay_id = Product::createEbayPost($item->id);
        //$item->ebay_id = $ebay_id;

        //$amazon_id = Product::createAmazonPost($item->id);
        //$item->amazon_id = implode(";", $amazon_id);

        //Product::createGumtreePost($item->id);

        //$item->save();
        //dd($item);
        return back()->withSuccess(trans('app.success_store'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Product::findOrFail($id);
        $list_feeds = [];
        foreach($item->amazon_feed_status as $amz_feed){
            $xml = simplexml_load_string($amz_feed);
            $list_feeds += [$xml];
        }
        $item->amazon_feeds = $list_feeds;
        return view('admin.products.view', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Product::findOrFail($id);

        return view('admin.products.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, Product::rules());

        $item = Product::findOrFail($id);

        $item->update($request->all());

        return redirect()->route(ADMIN . '.products.index')->withSuccess(trans('app.success_update'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::destroy($id);

        return back()->withSuccess(trans('app.success_destroy'));
    }
}
