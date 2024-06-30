<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Form\OwnerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\OwnerRepository;

class OwnerController extends AbstractController
{
    #[Route('/owner', name: 'app_owner')]
    public function index(): Response
    {
        return $this->render('owner/list.html.twig', [
            'controller_name' => 'OwnerController',
        ]);
    }

    #[Route('/owner/add', name: 'owner_add')]
    public function addOwner(Request $request, EntityManagerInterface $entityManager): Response
    {
        $owner = new Owner();
        $form = $this->createForm(OwnerType::class, $owner);

        $form->handleRequest($request);

        try{
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($owner);
                $entityManager->flush();
                // Récupérer les erreurs du formulaire
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[$error->getOrigin()->getName()] = $error->getMessage();
                }

                // Si la requête est AJAX, retourner une réponse JSON avec les erreurs
                if ($request->isXmlHttpRequest() && $errors) {
                    return new JsonResponse(['status' => 'errord', 'errors' => $errors]);
                }

                $this->addFlash('success', 'User registered successfully!');
            

                return new JsonResponse(['status' => 'success', 'message' => 'Enregistrement effectué']);
                

            }

            return $this->render('owner/add.html.twig', [
                'form' => $form->createView(),
            ]);

        } catch (\Exception $e) {
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }

    }

    #[Route('/owners/list', name: 'owner_list')]
    public function listOwners(OwnerRepository $ownerRepository): Response
    {
        // Récupérer tous les propriétaires depuis le repository
         $owners = $ownerRepository->findAll();

        // Passer les données à votre template Twig pour l'affichage
        return $this->render('owner/list.html.twig', [
            'owners' => $owners,
        ]);
    }

    /**
     * @Route("/owner/edit/{id}", name="owner_edit", methods={"PATCH"})
     */
    public function editOwner(int $id, Request $request,EntityManagerInterface $entityManager): Response
    {

        try{
            // Récupérer l'entité Owner par son ID
            $owner = $entityManager->getRepository(Owner::class)->find($id);
            $data = json_decode($request->getContent(), true);

            if (isset($data['firstname'])) {
                $owner->setFirstname($data['firstname']);
            }

            if (isset($data['lastname'])) {
                $owner->setLastname($data['lastname']);
            }

            $entityManager->persist($owner);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Modification effectuée'], Response::HTTP_OK);

        }catch(\Exception $e){
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }
        
    }

    #[Route('/owner/delete/{id}', name: 'owner_delete')]
    public function deleteOwner(int $id, EntityManagerInterface $entityManager): Response
    {

        try{
            // Récupérer l'entité Owner par son ID
            $owner = $entityManager->getRepository(Owner::class)->find($id);

            if (!$owner) {
                throw $this->createNotFoundException('Propriétaire non trouvé pour l\'id '.$id);
            }

        $entityManager->remove($owner);
        $entityManager->flush();

        $this->addFlash('success', 'Propriétaire supprimé avec succès.');

        return $this->redirectToRoute('owner_list');

        }catch(\Exception $e){
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }

    }


}
