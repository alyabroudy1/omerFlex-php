<?php

namespace App\Controller;

use App\Entity\Movie;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Symfony\Component\Panther\Client;
use App\Entity\Server;
use App\Entity\Source;
use App\servers\AkwamTube;
use App\servers\MovieServerInterface;
use Doctrine\ORM\EntityManagerInterface;
use HeadlessChromium\BrowserFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;


/**
 * Class MovieController
 *
 * This class represents a controller for managing movies.
 * It extends the AbstractController class.
 * it offers two api end points:
 * - search(query) which returns a list of result movies.
 * - fetch(movie) which returns the next level of movie state
 */
#[Route('/movie')]
class MovieController extends AbstractController
{
    public function __construct(private ServersController $serversController)
    {
    }

    #[Route('/chrome', name: 'app_movie_chrome')]
    public function chrome(): JsonResponse
    {


//        $serverUrl = 'http://localhost:4444';
//        // Set the path to the Chrome binary
//        $chromeBinaryPath = __DIR__ .'/../../drivers/chromedriver';  // Replace with the actual path
//
//// Create a DesiredCapabilities instance
//        $capabilities = DesiredCapabilities::chrome();
//        $options = new ChromeOptions();
//        //$options->setBinary($chromeBinaryPath);
//        // Set the 'chromeOptions' to specify the binary location
//        // Set the ChromeDriver executable path
//
//        $options->addArguments(['-headless']);
//        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
//
//        $driver = RemoteWebDriver::create($serverUrl, $capabilities);
//
//     //   $driver = ChromeDriver::create();
//
//
//
//        $response2 =  $driver->get($url);
//        dd('done', $response2->getTitle());
//        ////////////////////////
//        $client = Client::createChromeClient(__DIR__ . '/../../drivers/chromedriver', [
//         //   'PANTHER_NO_SANDBOX',
//         //   'PANTHER_NO_HEADLESS'
//        ],[]);

        $chromeBinaryPath = __DIR__ .'/../../drivers/chromedriver';

// Or, if you care about the open web and prefer to use Firefox
        //   $client = Client::createFirefoxClient();

        $url = 'https://cimcb.lol/watch/مسلسل-the-winter-king-الموسم-الاول-الحلقة-5-الخامسة';
        $url2 = 'https://api-platform.com';
//        $client = Client::createChromeClient($chromeBinaryPath);
//        $response = $client->request('GET', $url); // Yes, this website is 100% written in JavaScript
       // $client->clickLink('Getting started');
        // Specify the path and filename where you want to save the HTML

// Save the HTML to a file

//        $client->close();
//        dd($client->getTitle());
// You can check if the file was saved successfully



//// Wait for an element to be present in the DOM (even if hidden)
//        $crawler = $client->waitFor('#installing-the-framework');
//// Alternatively, wait for an element to be visible
//        $crawler = $client->waitForVisibility('#installing-the-framework');
//
//        echo $crawler->filter('#installing-the-framework')->text();
//        $client->takeScreenshot('screen.png'); // Yeah, screenshot!
//
//        dd('$client');


///////////////////////
        $browserFactory = new BrowserFactory(__DIR__ . '/../drivers/chromedriver');

// starts headless Chrome
        $browser = $browserFactory->createBrowser([
//            'headless' => true,
//            'keepAlive' => true,
//            'noSandbox' => true
        ]);
        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $page->navigate('https://www.google.com');
dd($page);
            // get page title
            $pageTitle = $page->evaluate('document.title')->getReturnValue();
            $browser->close();
            dd($pageTitle);
//            $filename = 'my_webpage.html';
//            file_put_contents($filename, $response->html());
//            if (file_exists($filename)) {
//                echo "HTML saved to $filename!";
//            } else {
//                echo "Oops! Something went wrong.";
//            }


            // screenshot - Say "Cheese"! 😄
            $page->screenshot()->saveToFile('/foo/bar.png');

            // pdf
            $page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
        } finally {
            // bye
            $browser->close();
        }
    }

    /**
     * Searches for movies based on the provided query.
     *
     * @param string $query The search query.
     * @return JsonResponse The JSON response containing the search results.
     */
    #[Route('/search/{query}', name: 'app_movie_search')]
    public function search($query): JsonResponse
    {

        $movieList = $this->serversController->search($query);

        dd('MovieController: ', $movieList);
        return $this->json([
            'message' => 'Welcome to search',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

    #[Route('/fetchMovie/{movie}', name: 'app_movie_fetch_movie')]
    public function fetchMovie(Movie $movie): JsonResponse
    {
        //todo: validate input of the movie id
        $movieList =  $this->serversController->fetchMovie($movie);
        dd('fetchMovie', $movieList);
        return $this->json([
            'message' => 'Welcome to fetch',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

    #[Route('/fetchSource/{source}', name: 'app_movie_fetch_source')]
    public function fetchSource(Source $source): JsonResponse
    {
        $movieList =  $this->serversController->fetchSource($source);
        return $this->json([
            'message' => 'Welcome to fetch',
            'path' => 'src/Controller/MovieController.php',
        ]);
    }

}
