<?php

namespace App\Controller;

use App\Repository\ChambreRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChambreSearchController extends AbstractController
{

    #[Route('/search-rooms', name: 'search_rooms', methods: ['GET'])]
    public function searchRooms(
        Request $request, 
        ChambreRepository $chambreRepository,
        ReservationRepository $reservationRepository
    ): Response
    {
        
        $checkIn = $request->query->get('check_in');
        $checkOut = $request->query->get('check_out');
        $guests = (int) $request->query->get('guests', 1);
        $roomType = $request->query->get('room_type');
        
        
        if (!$checkIn || !$checkOut) {
            $this->addFlash('error', 'Please select check-in and check-out dates.');
            return $this->redirectToRoute('app_home');
        }
        
        
        try {
            $dateDebut = new \DateTime($checkIn);
            $dateFin = new \DateTime($checkOut);
            
            if ($dateFin <= $dateDebut) {
                $this->addFlash('error', 'Check-out date must be after check-in date.');
                return $this->redirectToRoute('app_home');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Invalid date format.');
            return $this->redirectToRoute('app_home');
        }
        
       
        $queryBuilder = $chambreRepository->createQueryBuilder('c')
            ->where('c.is_active = :active')
            ->andWhere('c.capacite >= :guests')
            ->setParameter('active', true)
            ->setParameter('guests', $guests);
        
       
        if ($roomType) {
            $queryBuilder->andWhere('c.chambre_type = :type')
                ->setParameter('type', $roomType);
        }
        
        $allRooms = $queryBuilder->getQuery()->getResult();
        
        
        $availableRooms = [];
        foreach ($allRooms as $room) {
            if ($this->isRoomAvailable($room, $dateDebut, $dateFin, $reservationRepository)) {
                $availableRooms[] = $room;
            }
        }
        
       
        $nights = $dateDebut->diff($dateFin)->days;
        
        return $this->render('chambre_search/search_results.html.twig', [
            'rooms' => $availableRooms,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'room_type' => $roomType,
            'nights' => $nights,
        ]);
    }
    
    private function isRoomAvailable(
        $room, 
        \DateTime $checkIn, 
        \DateTime $checkOut, 
        ReservationRepository $reservationRepository
    ): bool
    {
        $overlappingReservations = $reservationRepository->createQueryBuilder('r')
            ->where('r.chambre = :room')
            ->andWhere('r.status != :cancelled')
            ->andWhere(
                '(r.date_debut < :checkOut AND r.date_fin > :checkIn)'
            )
            ->setParameter('room', $room)
            ->setParameter('cancelled', 'annulée')
            ->setParameter('checkIn', $checkIn)
            ->setParameter('checkOut', $checkOut)
            ->getQuery()
            ->getResult();
        
        return count($overlappingReservations) === 0;
    }
}