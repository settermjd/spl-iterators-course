<?php

require_once '../vendor/autoload.php';

date_default_timezone_set('Europe/Berlin');

use Slim\Slim;
use PodcastSite\Entity\Show;
use PodcastSite\Episodes\EpisodeLister;
use Mni\FrontYAML\Parser;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

// Initialise a Slim app
$app = new Slim(array(
    'debug' => true,
    'mode' => 'development',
    'view' => new \Slim\Views\Twig(),
    'templates.path' => dirname(__FILE__) . '/../storage/templates'
));

// Add Episode Lister support
$app->episodeLister = EpisodeLister::factory([
    'type' => 'filesystem',
    'path' => dirname(__FILE__) . '/../storage/posts',
    'parser' => new Parser()
]);

// Setup the app views
$view = $app->view();

$engine = new MarkdownEngine\MichelfMarkdownEngine();
$view->parserExtensions = array(
    new MarkdownExtension($engine)
);

// Add in the routes for the application
require_once('routes.php');

// Launch the application
$app->run();
