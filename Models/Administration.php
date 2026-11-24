<?php
class Administration
{
    private int $idAdministration = 0;
    private string $siteRef = '';
    private string $typeAdministration = '';
    private string $nom = '';
    private string $adresse = '';
    private string $horaires = '';
    private string $telephone = '';
    private float $latitude = 0.0;
    private float $longitude = 0.0;
    private bool $actif = true;

    public function getIdAdministration(): int { return $this->idAdministration; }
    public function getSiteRef(): string { return $this->siteRef; }
    public function getTypeAdministration(): string { return $this->typeAdministration; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getHoraires(): string { return $this->horaires; }
    public function getTelephone(): string { return $this->telephone; }
    public function getLatitude(): float { return $this->latitude; }
    public function getLongitude(): float { return $this->longitude; }
    public function isActif(): bool { return $this->actif; }

    public function setIdAdministration(int $id): void { $this->idAdministration = $id; }
    public function setSiteRef(string $siteRef): void { $this->siteRef = $siteRef; }
    public function setTypeAdministration(string $type): void { $this->typeAdministration = $type; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setHoraires(string $horaires): void { $this->horaires = $horaires; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setLatitude(float $latitude): void { $this->latitude = $latitude; }
    public function setLongitude(float $longitude): void { $this->longitude = $longitude; }
    public function setActif(bool $actif): void { $this->actif = $actif; }

    public static function fromArray(array $row): self
    {
        $a = new self();
        $a->setIdAdministration((int) ($row['idAdministration'] ?? 0));
        $a->setSiteRef((string) ($row['siteRef'] ?? ''));
        $a->setTypeAdministration((string) ($row['typeAdministration'] ?? ''));
        $a->setNom((string) ($row['nom'] ?? ''));
        $a->setAdresse((string) ($row['adresse'] ?? ''));
        $a->setHoraires((string) ($row['horaires'] ?? ''));
        $a->setTelephone((string) ($row['telephone'] ?? ''));
        $a->setLatitude((float) ($row['latitude'] ?? 0));
        $a->setLongitude((float) ($row['longitude'] ?? 0));
        $a->setActif((bool) ($row['actif'] ?? true));
        return $a;
    }
}
