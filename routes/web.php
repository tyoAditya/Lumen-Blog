<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LogLevel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return $router->app->version();
});

$dice = new Dice();


$router->get('/rolldice', function (Request $request, Response $response) use ($dice) {
    $logger = new Logger('dice-server');
    $logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    
    $params = $request->getQueryParams();
    if(isset($params['rolls'])) {
        $result = $dice->roll($params['rolls']);
        if (isset($params['player'])) {
            $logger->info("A player is rolling the dice.", ['player' => $params['player'], 'result' => $result]);
        } else {
            $logger->info("Anonymous player is rolling the dice.", ['result' => $result]);
        }
        $response->getBody()->write(json_encode($result));
    } else {
        $response->withStatus(400)->getBody()->write("Please enter a number of rolls");
    }
    return $response;
});

// API
$router->group(['prefix' => 'api'], function () use ($router) {
    // Auth routes
    $router->post('auth/login', 'AuthController@login');
    $router->post('auth/register', 'AuthController@register');

    // Passport Authentication
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        // Category
        $router->get('category', 'CategoryController@index');
        // Post
        $router->get('post', 'PostController@index');
        $router->get('post/{id}', 'PostController@show');
        $router->post('post', 'PostController@store');
        $router->put('post/{id}', 'PostController@update');
        $router->delete('post/{id}', 'PostController@destroy');
    });
});
