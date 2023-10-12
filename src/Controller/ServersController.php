<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Server;
use App\Entity\Source;
use App\servers\AkwamTube;
use App\servers\MovieServerInterface;
use App\servers\MyCima;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use http\Header\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServersController extends AbstractController
{
    private $servers;

    public function __construct(private HttpClientInterface $httpClient, private EntityManagerInterface $entityManager)
    {
        $this->initializeServers();
    }

    public function search($query): array
    {
        //get search result from servers
        //todo: try to get the result from database first and if theres no result then fetch it from the net
        //todo: find a way to update database movies something like fetched the last added movies once a day
       //todo:optimize search
        $movieList = $this->getMovieListFromDB($query);
        dump('search db: ' . count($movieList));
        if (empty($movieList)) {
            //search all server and add result to db
            $this->searchAllServers($query);
            //fetch result again from db
            $movieList = $this->getMovieListFromDB($query);
        }

        return $movieList;
    }

    public function fetchMovie(Movie $movie): array
    {
        $movieList = $this->entityManager->getRepository(Movie::class)->findSubMovies($movie);
        dump('fetchMovie db: ' . count($movieList));
        if (empty($movieList)) {
            $source = $movie->getSources()->get(0);
            /** @var MovieServerInterface $server */
            $server = $this->servers[$source->getServer()->getName()];
            //fetch result again from db
            $movieList = $server->fetchMovie($movie);
            //save to data base movies with only groupOfGroup, Group, Item
            if ($movie->getState() < Movie::STATE_RESOLUTION){
                $this->matchMovieList($movieList, $server);
            }
        }
        return $movieList;
    }

    public function fetchSource(Source $source): array
    {
        $resultList = [];
        //check if movie existi in database
        $resultMovie = $this->entityManager->getRepository(Source::class)->find($source);

        /** @var MovieServerInterface $server */
        $server = $this->servers[$source->getServer()->getName()];
        //todo: cache
        $resultList = $server->fetchSource($source);
        return $resultList;
    }

    private function initializeServers()
    {
        //akwamTube
        //fetch new Server() from db
        //todo: suggest refactoring
//        $akwamTubeServerConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_AKWAM]);
//        dump('initializeServers akwamTube:');
//        if (empty($akwamTubeServerConfig)) {
//            $akwamTubeServerConfig = new Server();
//            $akwamTubeServerConfig->setName(Server::SERVER_AKWAM);
//            $akwamTubeServerConfig->setWebAddress('https://i.akwam.tube');
//            $akwamTubeServerConfig->setActive(true);
//            //only the first time if server is not saved to db
//            $this->entityManager->persist($akwamTubeServerConfig);
//            $this->entityManager->flush();
//        }
//        $this->servers[Server::SERVER_AKWAM] = AkwamTube::getInstance($this->httpClient, $akwamTubeServerConfig);

        //myCima
        //fetch new Server() from db
        //todo: suggest refactoring
        $myCimaServerConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_MYCIMA]);
        dump('initializeServers SERVER_MYCIMA:');
        if (!$myCimaServerConfig) {
            $myCimaServerConfig = new Server();
            $myCimaServerConfig->setName(Server::SERVER_MYCIMA);
            $myCimaServerConfig->setWebAddress('https://mycima10.wecima.watch');
            //only the first time if server is not saved to db
            $myCimaServerConfig->setActive(true);
            //only the first time if server is not saved to db
            $this->entityManager->persist($myCimaServerConfig);
            $this->entityManager->flush();
        }
        $this->servers[Server::SERVER_MYCIMA] = MyCima::getInstance($this->httpClient, $myCimaServerConfig);

        //todo: other server ...
    }

    private function getMovieListFromDB($query)
    {
        return $this->entityManager->getRepository(Movie::class)->findMainMoviesByTitleLoose($query);
    }

    private function searchAllServers($query)
    {
        dump('searchAllServers');
        //todo: doing it using thread or workers for performance
        /** @var MovieServerInterface $server */
        foreach ($this->servers as $server) {
            $result = $server->search($query);
            //todo: in new process match it with database and add it if missing
            $this->matchMovieList($result, $server);
        }
    }

    private function matchMovieList(array $movieList, $server)
    {
        //todo: in new process match it with database and add it if missing
        foreach ($movieList as $movie) {
            //todo: optimize
            if ($movie->getState() === Movie::STATE_VIDEO || $movie->getState() === Movie::STATE_RESOLUTION){
                continue;
            }
            $this->matchMovie($movie, $server);
        }
    }

    private function getExistingMovie(Movie $movie)
    {
//        $mainMovie = $movie->getMainMovie();
//        if(empty($mainMovie)){
//            $mainMovie = $movie;
//        }
//        else{
//            if(!empty($mainMovie->getMainMovie())){
//                $mainMovie = $mainMovie->getMainMovie();
//                if(!empty($mainMovie->getMainMovie())){
//                    $mainMovie = $mainMovie->getMainMovie();
//                }
//            }
//        }
        //todo: optimize
        $title = $this->getCleanTitle($movie->getTitle());
        return $this->entityManager->getRepository(Movie::class)->findByTitleAndState($title, $movie->getState());
    }

    private function detectCorrectMatch(array $existingMovies, mixed $movie)
    {
        if (count($existingMovies) === 1) {
            return $existingMovies[0];
        } else {
            //todo: try to match the right movie
            return $existingMovies[0];
        }
    }

    private function matchMovie(Movie $movie, $server)
    {
        $existingMovies = $this->getExistingMovie($movie);
        if ($existingMovies) {
            /** @var Movie $matchedMovie */
            $matchedMovie = $this->detectCorrectMatch($existingMovies, $movie);
            //todo: match other cases
            if ($movie->getState() === Movie::STATE_ITEM) {
                $itemSources = $matchedMovie->getSources();
                foreach ($movie->getSources() as $source) {
                    //means it's they are both in the same level
                        //check if the source exist els add it
                        foreach ($itemSources as $mainSource) {
                            if ($mainSource->getVidoUrl() === $source->getVidoUrl()) {
                                //if exist continue
                                continue;
                            }
                            $source->setMovie($matchedMovie);
                            $matchedMovie->addSource($source);
                            $this->entityManager->persist($source);
                            $this->entityManager->flush();
                        }
                }
            }
        } else {
            //if not exist add it
            //only if its an item movie
            //refactor movie to be ready to save
            // $this->refactorMovieForSave($movie, $server);
            //todo: optimize cleaning the title before save and before cleaning title to search in db
            $title = $this->getCleanTitle($movie->getTitle());
            $movie->setTitle($title);
            if ($movie->getSources()->first()) {
                $this->entityManager->persist($movie->getSources()->first());
            }
            $this->entityManager->persist($movie);
            $this->entityManager->flush();
            dd('188', $movie);
        }
    }

    private function getCleanTitle(?string $title)
    {
        // Array of words to be replaced
        $replace = array('series', '-', '_', 'season', 'مسلسل', 'فيلم', 'فلم', 'موسم');
        $title = str_ireplace($replace, '', $title);

        // Replace 4 digit numbers
        $title = preg_replace('/\b\d{4}\b/', '', $title);

        // Extra spaces should be removed from the title
        $title = trim($title);

        // Multiple spaces between words should be replaced with only one space
        $title = preg_replace('!\s+!', ' ', $title);

        return $title;
    }

    private function fetchNextLevelMovie(Movie $movie)
    {
        $subMovies = $movie->getSubMovies();
        //try to get its sub movies from db
        if ($subMovies->count() === 0){
            $subMovies = $this->entityManager->getRepository(Movie::class)->findBy(['mainMovie' => 1]);
        }

        if ($subMovies === null || count($subMovies) === 0){
            if (!empty($movie->getSources())){
                $serverName = $movie->getSources()->get(0)->getServer()->getName();
                /** @var MovieServerInterface $server */
                $server = $this->servers[$serverName];
                //todo: cache
                $subMovies = $server->fetchMovie($movie);
                //todo: in new process match it with database and add it if missing
                $this->matchMovieList($subMovies, $server);
            }
            //todo: we may do something if source is empty
        }
        if ($subMovies instanceof Collection){
            $subMovies = $subMovies->toArray();
        }
        return $subMovies;
    }

    private function fetchMovieSublist(Movie $movie)
    {
        //check if sublist in db else fetch from server
    }
}
