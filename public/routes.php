<?php

use \PodcastSite\Feed\FeedCreatorFactory;

/**
 * The default route
 */
$app->get('/', function () use ($app) {
    $app->render(
        'home.twig', [
            'show' => $app->show,
            'latestEpisode' => $app->episodeLister->getLatestEpisode(),
            'pastEpisodes' => $app->episodeLister->getPastEpisodes(false),
            'upcomingEpisodes' => $app->episodeLister->getUpcomingEpisodes(),
        ]
    );
})->name('home');

/**
 * Get an episode
 */
$app->get('/episode/:episodeSlug', function ($episodeSlug) use ($app) {
    $episode = $app->episodeLister->getEpisode($episodeSlug);
    if (is_null($episode)) {
        $app->notFound();
    } else {
        $app->render(
            'episode.twig', [
                'show' => $app->show,
                'episode' => $episode,
                'route' => sprintf(
                    'http://%s',
                    $_SERVER['HTTP_HOST'] . $app->request()->getResourceUri()
                )
            ]
        );
    }
})->name('episode');
