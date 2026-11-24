<?php
class Service
{
    private int $idService = 0;
    private int $idAdministration = 0;
    private string $nom = '';
    private string $description = '';
    private bool $actif = true;

    public function getIdService(): int { return $this->idService; }
    public function getIdAdministration(): int { return $this->idAdministration; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): string { return $this->description; }
    public function isActif(): bool { return $this->actif; }

    public function setIdService(int $id): void { $this->idService = $id; }
    public function setIdAdministration(int $id): void { $this->idAdministration = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setActif(bool $actif): void { $this->actif = $actif; }

    public static function fromArray(array $row): self
    {
        $s = new self();
        $s->setIdService((int) ($row['idService'] ?? 0));
        $s->setIdAdministration((int) ($row['idAdministration'] ?? 0));
        $s->setNom((string) ($row['nom'] ?? ''));
        $s->setDescription((string) ($row['description'] ?? ''));
        $s->setActif((bool) ($row['actif'] ?? true));
        return $s;
    }
}
