<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Chambre;
use App\Entity\Reservation;
use App\Entity\Facturation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public const USER_REFERENCE = 'user_';
    public const CHAMBRE_REFERENCE = 'chambre_';
    public const RESERVATION_REFERENCE = 'reservation_';

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        
        // Create Users
        for($i = 1; $i <= 20; $i++){
            $client = new User();
            
            $client->setEmail($faker->email)
                ->setNom($faker->lastName)
                ->setPrenom($faker->firstName)
                ->setTelephone($faker->phoneNumber)
                ->setPassword(password_hash('password123', PASSWORD_BCRYPT))
                ->setRoles(['ROLE_USER']);
            
            $manager->persist($client);
            $this->addReference(self::USER_REFERENCE . $i, $client);
        }
        
        $manager->flush(); // Flush users
        
        // Create Chambres
        $typesChambres = ['simple', 'double', 'suite'];
        
        for($i = 1; $i <= 50; $i++){
            $chambre = new Chambre();
            
            $typeChambre = $faker->randomElement($typesChambres);
            
            $capacity = match($typeChambre) {
                'simple' => $faker->numberBetween(1, 2),
                'double' => $faker->numberBetween(2, 3),
                'suite' => $faker->numberBetween(3, 6),
                default => 2
            };
            
            $price = match($typeChambre) {
                'simple' => $faker->randomFloat(2, 50, 100),
                'double' => $faker->randomFloat(2, 80, 150),
                'suite' => $faker->randomFloat(2, 150, 400),
                default => 100
            };
            
            $chambre->setChambreNumero((string)$i) 
                ->setCapacite($capacity)
                ->setPrix($price)
                ->setChambreType($typeChambre)
                ->setIsActive($faker->boolean(85));
            
            $manager->persist($chambre);
            $this->addReference(self::CHAMBRE_REFERENCE . $i, $chambre);
        }
        
        $manager->flush(); // Flush chambres
        
        // Create Reservations
        for($i = 1; $i <= 40; $i++){
            $reservation = new Reservation();
            
            $userIndex = $faker->numberBetween(1, 20);
            $chambreIndex = $faker->numberBetween(1, 50);
            
            $user = $this->getReference(self::USER_REFERENCE . $userIndex, User::class);
            $chambre = $this->getReference(self::CHAMBRE_REFERENCE . $chambreIndex, Chambre::class);
            
            $idReservation = 'RES-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            $dateDebut = $faker->dateTimeBetween('-2 months', '+3 months');
            $dateInterval = $faker->numberBetween(1, 10);
            $dateFin = (clone $dateDebut)->modify("+{$dateInterval} days");
            
            $now = new \DateTime();
            if ($dateFin < $now) {
                $status = $faker->randomElement(['terminée', 'annulée']);
            } elseif ($dateDebut <= $now && $dateFin >= $now) {
                $status = 'en cours';
            } else {
                $status = $faker->randomElement(['confirmée', 'en attente']);
            }
            
            $reservation->setIdReservation($idReservation)
                ->setDateDebut($dateDebut)
                ->setDateFin($dateFin)
                ->setStatus($status)
                ->setUser($user)
                ->setChambre($chambre);
            
            $manager->persist($reservation);
            $this->addReference(self::RESERVATION_REFERENCE . $i, $reservation);
        }
        
        $manager->flush(); // Flush reservations
        
        // Create Facturations
        for($i = 1; $i <= 40; $i++){
            $reservation = $this->getReference(self::RESERVATION_REFERENCE . $i, Reservation::class);
            
            $status = $reservation->getStatus();
            if ($status === 'confirmée' || $status === 'en cours' || $status === 'terminée') {
                
                $facturation = new Facturation();
                
                $chambre = $reservation->getChambre();
                $dateDebut = $reservation->getDateDebut();
                $dateFin = $reservation->getDateFin();
                $nights = $dateDebut->diff($dateFin)->days;
                $montant = $chambre->getPrix() * $nights;
                
                $montant += $faker->randomFloat(2, 10, 50);
                
                $dateFacture = (clone $dateDebut)->modify('+' . $faker->numberBetween(0, 2) . ' days');
                
                $facturation->setMontant($montant)
                    ->setDateFacture($dateFacture)
                    ->setReservation($reservation);
                
                $manager->persist($facturation);
            }
        }
        
        $manager->flush(); // Final flush
    }
}