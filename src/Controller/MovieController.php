<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\MovieSource;
use App\Entity\Server;
use App\servers\AkwamTube;
use App\servers\MovieServerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieController extends AbstractController
{
    public function __construct(private HttpClientInterface $httpClient, private EntityManagerInterface $entityManager)
    {
    }
    #[Route('/movie', name: 'app_movie')]
    public function index(): JsonResponse
    {
        //fetch new Server() from db
        $serverConfig = $this->entityManager->getRepository(Server::class)->findOneBy(['name' => Server::SERVER_AKWAM]);
        if (!$serverConfig){
            $serverConfig = new Server();
            $serverConfig->setName(Server::SERVER_AKWAM);
            $serverConfig->setWebAddress('https://i.akwam.tube');
            //only the first time if server is not saved to db
            $this->entityManager->persist($serverConfig);
        }
        $server = AkwamTube::getInstance($this->httpClient, $serverConfig);
        $movieListAkwam = $server->search('sonic');
        $movieListCima = $server->search('sonic');
        $this->matchMovieist($movieListAkwam, $server);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

    private function matchMovieist(array $movieList, $server)
    {
        foreach ($movieList as $movie){
            $this->matchMovie($movie, $server);
        }
    }

    private function matchMovie(Movie $movie, $server)
    {
        //check if exist in db
        //todo adjust search to be more loss
        $existingMovies = $this->entityManager->getRepository(Movie::class)->findBy(['title' => $movie->getTitle()]);
        if ($existingMovies){
            //todo: if morethen one movie try to identify the right one if not then add it as new movie
            $matchedMovie = $this->detectCorrectMatch($existingMovies, $movie);
            //todo: check if movie is series or season or an item

            $tobeMatchedMovieList = $this->getToBeMatchedMovieList($matchedMovie);
//            //todo: if season or series fetch episodes/seasons from database and match them
//            //todo:check if it has the same source
//            $matchSourceCond = $this->matchSources($matchedMovie, $movie);
//            if (!$matchSourceCond){
//                $targetSource = $movie->getSources()->first();
//                if ($targetSource){
//                    $matchedMovie->addSource();
//                    $this->entityManager->persist($targetSource);
//                    $this->entityManager->flush();
//                }
//            }
        }else{
            //if not exist add it
            //only if its an item movie
            //refactor movie to be ready to save
           // $this->refactorMovieForSave($movie, $server);
            $this->entityManager->persist($movie);
//            if ($movie->getSources()->first()){
//                $this->entityManager->persist($movie->getSources()->first());
//            }
            $this->entityManager->flush();
        }
    }

    private function refactorMovieForSave(Movie $movie, MovieServerInterface $server)
    {
        //extract source from movie details: server, sourceLink,
        // Use preg_match to extract the desired part
//        if (preg_match('~https?://([^/]+)(/.*)~', $movie->getVideoUrl(), $matches)) {
//            if (count($matches) > 1){
//                $source = new MovieSource();
//                //todo: detect server from matches[0] and try to match it with existing server in db
//                $source->setName($matches[1]);
//                $source->setLink($matches[2]);
//                $source->setServer($server->getServerConfig());
//                $movie->addSource($source);
//                $this->entityManager->persist($source);
//            }
//        }
    }

    private function detectCorrectMatch(array $existingMovies, mixed $movie)
    {
        if (count($existingMovies) === 1){
            return $existingMovies[0];
        }else{
            //todo: try to match the right movie
        }
    }

    private function matchSources(Movie $matchedMovie, $movie)
    {
//        $targetSource = $movie->getSources()->first();
//        if ($targetSource && $matchedMovie->getSources()){
//            /** @var MovieSource $source */
//            foreach ($matchedMovie->getSources() as $source){
//                $matchCond = $source->getLink() === $targetSource->getLink();
//                if ($matchCond){
//                    return true;
//                }
//            }
//        }
        return false;
    }

    /**
     * get season/series/episodes of movie from db
     * @param mixed $matchedMovie
     * @return void
     */
    private function getToBeMatchedMovieList(Movie $matchedMovie)
    {
        $toBeMatchedMovieList = [];
       $toBeMatchedMovieList = $this->entityManager->getRepository(Movie::class)->findBy(['mainMovie' => $matchedMovie]);
        dd('$toBeMatchedMovieList', $toBeMatchedMovieList);
    }
}
