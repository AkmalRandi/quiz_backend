<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// 🔥 TEST
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

    // 🔥 QUIZ (ALL AUTHENTICATED)
    $router->group(['middleware' => 'auth'], function () use ($router) {
        // Teacher
        $router->get('teacher/quizzes', 'QuizController@getTeacherQuizzes');
        $router->post('quizzes', 'QuizController@createQuiz');          // 🔥 ENDPOINT YANG DIPAKAI
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

    // 🔥 MATA PELAJARAN & SOAL (PUBLIC + PROTECTED)
    // ... sisanya sama
});