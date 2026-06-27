<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// 🔥 TEST ROUTE
$router->get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Pong! API is working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// 🔥 API ROUTES
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // 🔥 AUTH ROUTES
    $router->post('login/siswa', 'AuthController@loginSiswa');
    $router->post('login/guru', 'AuthController@loginGuru');
    $router->post('register/siswa', 'AuthController@registerSiswa');
    $router->post('register/guru', 'AuthController@registerGuru');
    $router->post('logout', 'AuthController@logout');

    // 🔥 PUBLIC ROUTES
    $router->get('mata-pelajaran', 'MataPelajaranController@index');
    $router->get('soal', 'SoalController@index');
    $router->get('soal/mapel/{id_mapel}', 'SoalController@getByMapel');

    // 🔥 PROTECTED ROUTES
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('soal', 'SoalController@store');
        $router->put('soal/{id}', 'SoalController@update');
        $router->delete('soal/{id}', 'SoalController@destroy');

        $router->get('nilai', 'NilaiController@index');
        $router->get('nilai/siswa/{id_siswa}', 'NilaiController@getBySiswa');
        $router->post('nilai', 'NilaiController@store');
        $router->put('nilai/{id}', 'NilaiController@update');
        $router->delete('nilai/{id}', 'NilaiController@destroy');

        $router->post('mata-pelajaran', 'MataPelajaranController@store');
        $router->put('mata-pelajaran/{id}', 'MataPelajaranController@update');
        $router->delete('mata-pelajaran/{id}', 'MataPelajaranController@destroy');
    });
});