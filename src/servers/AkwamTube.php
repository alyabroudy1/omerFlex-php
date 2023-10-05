<?php

namespace App\servers;

use App\Entity\Movie;
use App\Entity\MovieSource;
use App\Entity\Server;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkwamTube implements MovieServerInterface
{

    private ?int $id = null;
    private static ?AkwamTube $instance = null;
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
            $videoElement= $videoGrid->filter('div.thumb a');
            $videoUrl = $videoElement->attr('href');
            $cardImageElement = $videoGrid->filter('div.thumb a img');
            $cardImage = $cardImageElement->attr('data-src');
            $title = $cardImageElement->attr('alt');

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
                    $videoUrl = $matches[2];
                }
            }
            $movie->setTitle($title);
            $movie->setCardImage($cardImage);
            $movie->setVideoUrl($videoUrl);
            $movie->setServer($this->serverConfig);
            $movie->setMainMovie($movie);
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