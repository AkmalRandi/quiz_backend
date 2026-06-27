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
    
    // 🔥 AUTH
    $router->post('login/siswa', 'AuthController@loginSiswa');
    $router->post('login/guru', 'AuthController@loginGuru');
    $router->post('register/siswa', 'AuthController@registerSiswa');
    $router->post('register/guru', 'AuthController@registerGuru');
    $router->post('logout', 'AuthController@logout');
    $router->get('profile', 'AuthController@profile');
    $router->post('refresh', 'AuthController@refresh');

    // 🔥 QUIZ (TEACHER + STUDENT)
    $router->group(['middleware' => 'auth'], function () use ($router) {
        // Teacher
        $router->get('teacher/quizzes', 'QuizController@getTeacherQuizzes');
        $router->post('quizzes', 'QuizController@createQuiz');
        $router->delete('quizzes/{id}', 'QuizController@deleteQuiz');
        $router->patch('quizzes/{id}/visibility', 'QuizController@toggleVisibility');
        $router->post('quizzes/{id}/publish', 'QuizController@publishQuiz');
        
        // Student
        $router->get('quizzes', 'QuizController@getStudentQuizzes');
        $router->get('quizzes/{id}', 'QuizController@getQuizDetail');
        $router->post('quizzes/join/{joinCode}', 'QuizController@joinQuiz');
        $router->post('quizzes/{id}/start', 'QuizController@startQuiz');
        $router->post('quizzes/{id}/submit', 'QuizController@submitQuiz');
        $router->get('quizzes/{id}/result', 'QuizController@getResult');
    });

    // 🔥 MATA PELAJARAN
    $router->get('mata-pelajaran', 'MataPelajaranController@index');
    $router->get('soal', 'SoalController@index');
    $router->get('soal/mapel/{id_mapel}', 'SoalController@getByMapel');

    // 🔥 PROTECTED (MATA PELAJARAN CRUD)
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