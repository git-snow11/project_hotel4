<?php

namespace App\Entity;

use App\Repository\FacturationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacturationRepository::class)]
class Facturation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_facture = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?reservation $reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateFacture(): ?\DateTime
    {
        return $this->date_facture;
    }

    public function setDateFacture(\DateTime $date_facture): static
    {
        $this->date_facture = $date_facture;

        return $this;
    }

    public function getReservation(): ?reservation
    {
        return $this->reservation;
    }

    public function setReservation(?reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }
}
