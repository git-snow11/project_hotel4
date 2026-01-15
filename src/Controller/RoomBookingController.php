<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\Facturation;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RoomBookingController extends AbstractController
{
    #[Route('/room/{id}/book', name: 'room_booking')]
    #[IsGranted('ROLE_USER')]
    public function bookRoom(
        Chambre $chambre,
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        $checkIn = $request->query->get('check_in');
        $checkOut = $request->query->get('check_out');
        $guests = $request->query->get('guests');
        
        $dateDebut = new \DateTime($checkIn);
        $dateFin = new \DateTime($checkOut);
        $nights = $dateDebut->diff($dateFin)->days;
        $totalPrice = $chambre->getPrix() * $nights;
        
        return $this->render('booking/confirmation.html.twig', [
            'room' => $chambre,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'nights' => $nights,
            'total_price' => $totalPrice,
        ]);
    }
    
    #[Route('/booking/confirmation', name: 'booking_confirm', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirmBooking(
        Request $request,
        EntityManagerInterface $manager,
        ChambreRepository $chambreRepository
    ): Response
    {
        $roomId = $request->request->get('room_id');
        $checkIn = $request->request->get('check_in');
        $checkOut = $request->request->get('check_out');
        
        $chambre = $chambreRepository->find($roomId);
        $user = $this->getUser();
        
        
        $user->setPrenom($request->request->get('prenom'))
            ->setNom($request->request->get('nom'))
            ->setEmail($request->request->get('email'))
            ->setTelephone($request->request->get('telephone'));
        
       
        $reservation = new Reservation();
        $reservation->setIdReservation('RES-' . date('Ymd') . '-' . uniqid())
            ->setDateDebut(new \DateTime($checkIn))
            ->setDateFin(new \DateTime($checkOut))
            ->setStatus('confirmée')
            ->setUser($user)
            ->setChambre($chambre);
        
        $manager->persist($reservation);
        
        
        $dateDebut = new \DateTime($checkIn);
        $dateFin = new \DateTime($checkOut);
        $nights = $dateDebut->diff($dateFin)->days;
        $montant = $chambre->getPrix() * $nights;
        
        $facturation = new Facturation();
        $facturation->setMontant($montant)
            ->setDateFacture(new \DateTime())
            ->setReservation($reservation);
        
        $manager->persist($facturation);
        $manager->flush();
        
        $this->addFlash('success', 'Your booking has been confirmed!');
        
        return $this->redirectToRoute('booking_success', ['id' => $reservation->getId()]);
    }
    
    #[Route('/booking/success/{id}', name: 'booking_success')]
    #[IsGranted('ROLE_USER')]
    public function bookingSuccess(Reservation $reservation): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        return $this->render('booking/success.html.twig', [
            'reservation' => $reservation,
        ]);
    }
    
    #[Route('/booking/invoice/{id}', name: 'booking_invoice')]
    #[IsGranted('ROLE_USER')]
    public function showInvoice(Reservation $reservation, EntityManagerInterface $manager): Response
    {
        
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        
        $facturation = $manager->getRepository(Facturation::class)
            ->findOneBy(['reservation' => $reservation]);
        
        if (!$facturation) {
           
            $dateDebut = $reservation->getDateDebut();
            $dateFin = $reservation->getDateFin();
            $nights = $dateDebut->diff($dateFin)->days;
            $montant = $reservation->getChambre()->getPrix() * $nights;
            
            $facturation = new Facturation();
            $facturation->setMontant($montant)
                ->setDateFacture(new \DateTime())
                ->setReservation($reservation);
            
            $manager->persist($facturation);
            $manager->flush();
        }
        
        return $this->render('booking/facture.html.twig', [
            'facturation' => $facturation,
            'reservation' => $reservation,
        ]);
    }
}