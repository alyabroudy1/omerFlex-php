<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\MovieSource;
use App\Entity\Server;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Akwam implements MovieServerInterface
{

    private ?int $id = null;
    private static ?Akwam $instance = null;
    private function __construct(private HttpClientInterface $httpClient, private Server $serverConfig)
    {
        $this->init();
    }

    public static function getInstance(HttpClientInterface $httpClient, Server $serverConfig): static
    {
        if (!self::$instance){
            $instance = new self($httpClient, $serverConfig);
        }
       return $instance;
    }

    public function search($query): array
    {
        $response = $this->httpClient->request('GET', 'https://i.akwam.tube/?s=sonic');
        $content = $response->getContent();

        // Assuming $content contains your HTML response
        $crawler = new Crawler($content);

// Find all the <li> elements with the class "video-grid"
        $videoGridElements = $crawler->filter('li.video-grid');

// Initialize an array to store the extracted data
        $videos = [];

// Loop through each <li> element
        $videoGridElements->each(function (Crawler $videoGrid) use (&$videos) {
            // Extract data from each <li> element
            $videoUrl = $videoGrid->filter('div.thumb a')->attr('href');
            $title = $videoGrid->filter('h2.title a')->attr('title');
            $cardImage = $videoGrid->filter('div.thumb a img')->attr('data-src');

            $movie = new Movie();


            if (preg_match('~https?://([^/]+)(/.*)~', $cardImage, $imageMatches)) {
                if (count($imageMatches) > 1){
                    if ($imageMatches[1] === $this->serverConfig->getWebAddress()){
                        $cardImage = $imageMatches[2];
                    }
                }
            }
            if (preg_match('~https?://([^/]+)(/.*)~', $videoUrl, $matches)) {
                if (count($matches) > 1) {
                    $source = new MovieSource();
                    //todo: detect server from matches[0] and try to match it with existing server in db
                    $source->setName($matches[1]);
                    $source->setLink($matches[2] . '-akwam');
                    $source->setServer($this->serverConfig);
                    $movie->addSource($source);
                }
            }
            $movie->setTitle($title);
            $movie->setCardImage($cardImage);
            $movie->setVideoUrl($videoUrl);
            // Store the extracted data in an array
            $videos[] = $movie;
        });
        return $videos;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    private function init()
    {
        //todo: set cookie, headers and other stuff
        //$this->serverConfig->setName('Akwam'); // fetch id from serverConfig
    }

    public function getServerConfig(): Server
    {
        return $this->serverConfig;
    }

    public function setServerConfig(Server $serverConfig): void
    {
        $this->serverConfig = $serverConfig;
    }


}