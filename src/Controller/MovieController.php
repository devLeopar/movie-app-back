<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends ApiController
{
    /**
     * @Route("/movies", methods="GET", name="movies")
     */
    public function moviesAction()
    {
        return $this->respond([
            'title' => 'The Princess Bride',
            'count' => 0
        ]);
    }

    /**
     * @Route("/movies", methods="POST",name="create-movie")
     */
    public function create(Request $request, MovieRepository $movieRepository, EntityManagerInterface $em)
    {

        //Require okta verification
        // if (!$this->isAuthorized()) {
        //     return $this->respondUnauthorized();
        // }

        $request = $this->transformJsonBody($request);

        if (!$request) {
            return $this->respondValidationError('Please provide a valid request');
        }

        if (!$request->get('title')) {
            return $this->respondValidationError('Please provide a title');
        }

        //persist the moview
        $movie = new Movie;
        $movie->setTitle($request->get('title'));
        $movie->setCount(0);
        $em->persist($movie);
        $em->flush();

        return $this->respondCreated($movieRepository->transform($movie));
    }

    /**
     * @Route("/movies/{id}/count", methods="POST", name="increase-movie-count")
     */
    public function increaseCount($id, EntityManagerInterface $em, MovieRepository $movieRepository)
    {

        //Require okta verification
        // if (!$this->isAuthorized()) {
        //     return $this->respondUnauthorized();
        // }

        $movie = $movieRepository->find($id);

        if (!$movie) {
            return $this->respondNotFound();
        }

        $movie->setCount($movie->getCount() + 1);
        $em->persist($movie);
        $em->flush();

        return $this->respond([
            'count' => $movie->getCount()
        ]);
    }
}
