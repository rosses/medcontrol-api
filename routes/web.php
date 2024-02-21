<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version()." -> connected to ".env("DB_HOST");
});

Route::group([
    'prefix' => 'v1'
],function($router) {
    /* Masterdata Lab endpoints */
    Route::group([
        'prefix' => 'lab',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'LabController@index');
        Route::get('/{id}', 'LabController@show');
        Route::post('/', 'LabController@create');
        Route::post('/{id}', 'LabController@update');
        Route::delete('/{id}', 'LabController@delete');
    });

    /* Masterdata Medicine endpoints */
    Route::group([
        'prefix' => 'medicine',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'MedicineController@index');
        Route::get('/{id}', 'MedicineController@show');
        Route::post('/', 'MedicineController@create');
        Route::post('/{id}', 'MedicineController@update');
        Route::delete('/{id}', 'MedicineController@delete');
    });

    /* Masterdata Diagnosis endpoints */
    Route::group([
        'prefix' => 'diagnosis',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'DiagnosisController@index');
        Route::get('/{id}', 'DiagnosisController@show');
        Route::post('/', 'DiagnosisController@create');
        Route::post('/{id}', 'DiagnosisController@update');
        Route::delete('/{id}', 'DiagnosisController@delete');
    });

    /* Masterdata CertificateTypes endpoints */
    Route::group([
        'prefix' => 'certificate-type',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'CertificateTypeController@index');
        Route::get('/{id}', 'CertificateTypeController@show');
        Route::post('/', 'CertificateTypeController@create');
        Route::post('/{id}', 'CertificateTypeController@update');
        Route::delete('/{id}', 'CertificateTypeController@delete');
    });

    /* Masterdata ExamTypes endpoints */
    Route::group([
        'prefix' => 'exam-type',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'ExamTypeController@index');
        Route::get('/{id}', 'ExamTypeController@show');
        Route::post('/', 'ExamTypeController@create');
        Route::post('/{id}', 'ExamTypeController@update');
        Route::delete('/{id}', 'ExamTypeController@delete');
    });

    /* Masterdata Exams endpoints */
    Route::group([
        'prefix' => 'exam',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'ExamController@index');
        Route::get('/{id}', 'ExamController@show');
        Route::post('/', 'ExamController@create');
        Route::post('/{id}', 'ExamController@update');
        Route::delete('/{id}', 'ExamController@delete');
    });

    /* Masterdata ExamDatas endpoints */
    Route::group([
        'prefix' => 'examdata',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'ExamDataController@index');
        Route::get('/{id}', 'ExamDataController@show');
        Route::post('/save', 'ExamDataController@saveResults');
        Route::get('/get-by-date/{DateID}', 'ExamDataController@getExamValuesByDate');
        Route::post('/', 'ExamDataController@create');
        Route::post('/{id}', 'ExamDataController@update');
        Route::delete('/{id}', 'ExamDataController@delete');
    });

    /* Masterdata Surgery endpoints */
    Route::group([
        'prefix' => 'surgery',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'SurgeryController@index');
        Route::get('/{id}', 'SurgeryController@show');
        Route::post('/', 'SurgeryController@create');
        Route::post('/{id}', 'SurgeryController@update');
        Route::delete('/{id}', 'SurgeryController@delete');
    });

    /* Masterdata Specialists endpoints */
    Route::group([
        'prefix' => 'specialist',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'SpecialistController@index');
        Route::get('/{id}', 'SpecialistController@show');
        Route::post('/', 'SpecialistController@create');
        Route::post('/{id}', 'SpecialistController@update');
        Route::delete('/{id}', 'SpecialistController@delete');
    });

    /* Masterdata Healths endpoints */
    Route::group([
        'prefix' => 'health',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'HealthController@index');
        Route::get('/{id}', 'HealthController@show');
        Route::post('/', 'HealthController@create');
        Route::post('/{id}', 'HealthController@update');
        Route::delete('/{id}', 'HealthController@delete');
    });

    Route::group([
        'prefix' => 'group',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'GroupController@index');
        Route::get('/{id}', 'GroupController@show');
        Route::post('/', 'GroupController@create');
        Route::post('/{id}', 'GroupController@update');
        Route::delete('/{id}', 'GroupController@delete');
    });

    Route::group([
        'prefix' => 'status',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'StatusController@index');
    });

    Route::group([
        'prefix' => 'budget-status',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'BudgetStatusController@index');
    });

    /* People */
    Route::group([
        'prefix' => 'people',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/', 'PeopleController@index');
        Route::get('/{id}/dates', 'PeopleController@datesForPeople');
        Route::get('/{id}/exams', 'PeopleController@examsForPeople');
        Route::get('/{id}/recipes', 'PeopleController@recipesForPeople');
        Route::get('/{id}/certificates', 'PeopleController@certificatesForPeople');
        Route::get('/{id}/interviews', 'PeopleController@interviewsForPeople');
        Route::get('/{id}/evolutions', 'PeopleController@evolutionsForPeople');
        Route::get('/{id}/text', 'PeopleController@getText');
        Route::post('/{id}/change-status', 'PeopleController@changeStatus');
        Route::post('/{id}/change-dates', 'PeopleController@changeDates');
        Route::get('/{id}', 'PeopleController@show');
        Route::post('/', 'PeopleController@create');
        Route::post('/{id}', 'PeopleController@update');
        Route::delete('/{id}', 'PeopleController@delete');
    });

    /* Date */
    Route::group([
        'prefix' => 'date',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/next', 'DateController@next');
        Route::get('/find', 'DateController@find');
        Route::post('/confirm', 'DateController@confirm');
        Route::post('/{id}', 'DateController@saveSession');
        Route::get('/{id}', 'DateController@getSession');
    });

    /* Template */
    Route::group([
        'prefix' => 'template',
        'middleware' => 'auth'
    ], function($router) {
        Route::get('/{surgeryID}', 'TemplateController@show');
        Route::post('/{surgeryID}', 'TemplateController@update');
    });

    /* Masterdata Surgery endpoints */
    Route::group([
        'prefix' => 'evolution',
        'middleware' => 'auth'
    ], function($router) { 
        Route::post('/', 'EvolutionController@create'); 
        Route::delete('/{id}', 'EvolutionController@delete');
    });

    Route::group([
        'prefix' => 'recipe',
        'middleware' => 'auth'
    ], function($router) { 
        Route::delete('/{id}', 'RecipeController@delete');
    });

    Route::group([
        'prefix' => 'interview',
        'middleware' => 'auth'
    ], function($router) { 
        Route::delete('/{id}', 'InterviewController@delete');
        Route::get('/{id}/vb', 'InterviewController@vb');
    });

    Route::group([
        'prefix' => 'interview',
        'middleware' => 'singles'
    ], function($router) { 
        Route::post('/order', 'SingleController@saveOrder');
        Route::post('/recipe', 'SingleController@saveRecipe');
        Route::delete('/order/{id}', 'SingleController@deleteOrder');
        Route::delete('/recipe/{id}', 'SingleController@deleteRecipe');
    });

    /* Auth */
    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
    });

    Route::group([
        'prefix' => 'pdf-render'
    ], function ($router) {
        Route::get('data-orders/{id}', 'PdfController@getDataOrders');
        Route::get('recipes/{id}', 'PdfController@getRecipes');
        Route::get('certificates/{id}', 'PdfController@getCertificates');
        Route::get('certs-single/{id}', 'PdfController@getCertificateSingle');
        Route::get('certs-interview/{id}', 'PdfController@getInterview');
        Route::get('people/{id}', 'PdfController@getPeople');
        Route::get('single-recipes/{id}', 'PdfController@getSingleRecipe');
        Route::get('data-single-orders/{id}', 'PdfController@getSingleOrder');
    });

    Route::get('master','AuthController@master');
});


