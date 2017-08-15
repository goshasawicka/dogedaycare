<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../config.php';

$app = new \Slim\App(
    ["settings" => $config]
);
$container = $app->getContainer();


//VIEWS
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/../app/templates');

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};


//ROUTES
require __DIR__ . '/../app/routes.php';


$app->run();
