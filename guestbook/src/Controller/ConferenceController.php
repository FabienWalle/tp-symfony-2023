<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Conference;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;

class ConferenceController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager) { }


    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('conference/index.html.twig');
    }

    /**
     * @throws Exception
     */

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        #[Autowire('%photo_dir%')] string $photoDir,
        CommentRepository $commentRepository
    ): Response {
        {
            $offset = max(0, $request->query->getInt('offset', 0));
            $paginator = $commentRepository->getCommentPaginator($conference, $offset);

            $comment = new Comment();
            $form = $this->createForm(CommentFormType::class, $comment);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setConference($conference);

                if ($photo = $form['photo']->getData()) {
                    $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                    try {
                        $photo->move($photoDir, $filename);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                    }
                    $comment->setPhotoFilename($filename);
                }

                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
            }

            return $this->render('conference/show.html.twig', [
                'conference' => $conference,
                'comments' => $paginator,
                'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
                'comment_form' => $form,
            ]);
        }
    }
}