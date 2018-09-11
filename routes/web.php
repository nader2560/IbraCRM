<?php

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Auth::routes();

/*
|------------------------------------------------------------------------------------
| Admin
|------------------------------------------------------------------------------------
*/

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', 'DashboardController@index')->name('dash');

    Route::resource('users', 'UserController');

    Route::resource('products', 'ProductController');

Route::prefix('feedback')->group(function(){
    Route::get('/','FeedBackController')->name('feed');
    Route::get('eby','EbayController@index')->name('ebay');
    Route::get('eby/product/{id}','EbayController@product');
    Route::post('eby/msg','EbayController@answer')->name('respondebay');
    Route::post('eby/fbmsg','EbayController@answerfb')->name('respondfbebay');
    Route::get('amz','AmazonController@index')->name('amazon');
    Route::get('fbm','FacebookController@index')->name('facebook');
    Route::get('gmt','GumtreeController@index')->name('gumtree');
    Route::get('gmt/product/{id}','GumtreeController@product');
    Route::get('gmt/msg/{id}','GumtreeController@getThread');
    Route::post('gmt/msgsend','GumtreeController@sendMsg');
    Route::get('gpl','GooglePlusController@index')->name('google');
});

    Route::get('/test',function(){
        return view('admin.feedback.test');
    });
    Route::get('/test2',function(){
        return view('admin.feedback.test2');
    });
    Route::get('/test4',function(){
        return view('admin.feedback.test4');
    });
});
Route::get('/test2',function(){
    return view('admin.feedback.test2');
});
Route::get('/test3',function(){
    return view('admin.feedback.gumtree_parse');
});
Route::get('/test4',function(){
    return view('admin.feedback.test4');
});


