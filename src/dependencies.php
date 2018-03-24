<?php
// DIC configuration
use Slim\Http\Request;
use Slim\Http\Response;
dl('pdo_sqlite.so');
require 'helper.php';
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['flash'] = function(){
    return new \Slim\Flash\Messages();
};

$container['pdo'] = function($c){
    try {
        return new PDO($c->get('settings')['pdo']['dns']);
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
};

$app->add(function (Request $request, Response $response, $next) {
    $path = $request->getUri()->getPath();
    
    if(!checkAuth() && $path != '/login'){
        $this->flash->addMessage('msg','请登陆');
        return $response->withRedirect('/login',302);
    }

    return $next($request,$response);
});
