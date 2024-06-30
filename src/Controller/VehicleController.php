<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Vehicle;
use App\Repository\VehicleRepository;
use App\Repository\OwnerRepository;
use App\Form\VehicleType;

class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle')]
    public function index(): Response
    {
        return $this->render('vehicle/index.html.twig', [
            'controller_name' => 'VehicleController',
        ]);
    }

    #[Route('/vehicle/add', name: 'vehicle_add')]
    public function addOVehicle(Request $request, EntityManagerInterface $entityManager): Response
    {

        $vehicle = new Vehicle();
        $form = $this->createForm(VehicleType::class, $vehicle);

        $form->handleRequest($request);

        try{
            
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($vehicle);
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

            return $this->render('vehicle/add.html.twig', [
                'form' => $form->createView(),
            ]);

        } catch (\Exception $e) {
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }

    }

    #[Route('/vehicles/list', name: 'vehicle_list')]
    public function listVehicles(VehicleRepository $vehicleRepository, OwnerRepository $ownerRepository): Response
    {
        // Récupérer tous les propriétaires depuis le repository
         $vehicles = $vehicleRepository->findAll();
         $owners = $ownerRepository->findAll();

        // Passer les données à votre template Twig pour l'affichage
        return $this->render('vehicle/list.html.twig', [
            'vehicles' => $vehicles,
            'owners' => $owners,
        ]);
    }

    /**
     * @Route("/vehicle/edit/{id}", name="owner_edit", methods={"PATCH"})
     */
    public function editVehicle(int $id, Request $request,EntityManagerInterface $entityManager, OwnerRepository $ownerRepository): Response
    {

        try{
            // Récupérer l'entité Owner par son ID
            $vehicle = $entityManager->getRepository(Vehicle::class)->find($id);
            $data = json_decode($request->getContent(), true);

            if (isset($data['mark'])) {
                $vehicle->setMark($data['mark']);
            }

            if (isset($data['model'])) {
                $vehicle->setModel($data['model']);
            }

            if (isset($data['registrationNumber'])) {
                $vehicle->setRegistrationNumber($data['registrationNumber']);
            }

            if (isset($data['characteristics'])) {
                $vehicle->setCharacteristics($data['characteristics']);
            }

            if (isset($data['registrationDate'])) {
                $vehicle->setRegistrationDate(new \DateTime($data['registrationDate']));
            }

            if (isset($data['owner'])) {
                $owner = $ownerRepository->find($data['owner']);
                if ($owner) {
                    $vehicle->setOwner($owner);
                }
            }

            $entityManager->persist($vehicle);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Modification effectuée'], Response::HTTP_OK);

        }catch(\Exception $e){
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }
        
    }

    #[Route('/vehicle/delete/{id}', name: 'vehicle_delete')]
    public function deleteOwner(int $id, EntityManagerInterface $entityManager): Response
    {

        try{
            // Récupérer l'entité Owner par son ID
            $vehicle = $entityManager->getRepository(Vehicle::class)->find($id);

            if (!$vehicle) {
                throw $this->createNotFoundException('Propriétaire non trouvé pour l\'id '.$id);
            }

        $entityManager->remove($vehicle);
        $entityManager->flush();

        $this->addFlash('success', 'Propriétaire supprimé avec succès.');

        return $this->redirectToRoute('vehicle_list');

        }catch(\Exception $e){
            // Gérer l'exception
            $errorMessage = sprintf('An error occurred: %s', $e->getMessage());
            $this->addFlash('error', $errorMessage);
            return new JsonResponse(['status' => 'error', 'errors' => $errorMessage]);
        }

    }

}
