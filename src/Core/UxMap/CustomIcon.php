<?php

namespace App\Core\UxMap;

use Symfony\UX\Map\Icon\UxIcon;

class CustomIcon extends UxIcon
{
    protected ?string $color = null;

    /**
     * On surcharge la méthode statique pour retourner notre CustomIcon 
     * au lieu de l'UxIcon standard
     */
    public static function ux(string $name): self
    {
        // On récupère les propriétés par défaut (24x24)
        return new self($name);
    }

    /**
     * VOICI LA MÉTHODE QUE TU VOULAIS :
     * Elle permet de chaîner ->color() comme tu le fais déjà
     */
    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    /**
     * C'est ici que la magie opère pour Symfony UX Map.
     * On ajoute la couleur dans le tableau de données envoyé au JavaScript.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['color'] = $this->color;
        
        return $data;
    }
}