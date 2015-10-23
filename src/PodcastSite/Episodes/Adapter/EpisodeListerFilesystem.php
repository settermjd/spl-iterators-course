<?php

namespace PodcastSite\Episodes\Adapter;

use PodcastSite\Episodes\EpisodeListerInterface;
use PodcastSite\Iterator\ActiveEpisodeFilterIterator;
use PodcastSite\Sorter\SortByReverseDateOrder;
use PodcastSite\Entity\Episode;
use PodcastSite\Iterator\UpcomingEpisodeFilterIterator;
use PodcastSite\Iterator\PastEpisodeFilterIterator;

/**
 * Class EpisodeListerFilesystem
 * @package PodcastSite\Episodes\Adapter
 */
class EpisodeListerFilesystem implements EpisodeListerInterface
{
    protected $postDirectory;
    protected $fileParser;
    protected $episodeIterator;

    public function __construct($postDirectory, $fileParser)
    {
        $this->postDirectory = $postDirectory;
        $this->fileParser = $fileParser;
        $this->episodeIterator = new ActiveEpisodeFilterIterator(
            new \DirectoryIterator($this->postDirectory)
        );
    }

    protected function buildEpisodesList()
    {
        $episodeListing = [];
        foreach ($this->episodeIterator as $file) {
            $episodeListing[] = $this->buildEpisode($file);
        }

        return $episodeListing;
    }

    public function buildEpisode(\SplFileInfo $file)
    {
        $fileContent = file_get_contents($file->getPathname());
        $document = $this->fileParser->parse($fileContent, false);

        return new Episode($this->getEpisodeData($document));
    }

    public function getEpisodeData($document)
    {
        return [
            'publishDate' => (array_key_exists('publish_date', $document->getYAML())) ?
            $document->getYAML()['publish_date'] : '',
            'slug' => (array_key_exists('slug', $document->getYAML())) ? $document->getYAML()['slug'] : '',
            'title' => (array_key_exists('title', $document->getYAML())) ? $document->getYAML()['title'] : '',
            'content' => $document->getContent(),
            'link' => (array_key_exists('link', $document->getYAML())) ? $document->getYAML()['link'] : '',
            'download' => (array_key_exists('download', $document->getYAML())) ? $document->getYAML()['download'] : '',
            'guests' => (array_key_exists('guests', $document->getYAML())) ? $document->getYAML()['guests'] : '',
            'duration' => (array_key_exists('duration', $document->getYAML())) ? $document->getYAML()['duration'] : '',
            'fileSize' => (array_key_exists('fileSize', $document->getYAML())) ? $document->getYAML()['fileSize'] : '',
            'fileType' => (array_key_exists('fileType', $document->getYAML())) ? $document->getYAML()['fileType'] : '',
            'explicit' => (array_key_exists('explicit', $document->getYAML())) ? $document->getYAML()['explicit'] : ''
        ];
    }

    public function getUpcomingEpisodes()
    {
        $list = [];
        $iterator = new UpcomingEpisodeFilterIterator(
            new \ArrayIterator($this->buildEpisodesList())
        );
        foreach ($iterator as $episode) {
            $list[] = $episode;
        }

        return $list;
    }

    public function getPastEpisodes()
    {
        $list = [];
        $iterator = new PastEpisodeFilterIterator(
            new \ArrayIterator($this->buildepisodesList())
        );
        foreach ($iterator as $episode) {
            $list[] = $episode;
        }

        return $list;
    }

    public function getLatestEpisode()
    {
        $iterator = new \LimitIterator(
            new PastEpisodeFilterIterator(
                new \ArrayIterator($this->buildEpisodesList())
            ), 0, 1
        );

        $iterator->rewind();

        return $iterator->current();
    }
}
