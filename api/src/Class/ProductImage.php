<?php

require_once ('Entity.php');

/**
 * Class ProductImage
 * * Représente une seule image associée à un produit, avec l'ID de l'image,
 * l'ID du produit associé, et l'URL/nom du fichier de l'image.
 * * Implémente l'interface JsonSerializable pour la conversion en JSON.
 */
class ProductImage extends Entity {
    // Les propriétés privées correspondent aux colonnes de la table SQL
    private int $id;
    private ?int $product_id = null;
    private ?string $url = null;

    /**
     * Constructeur
     * @param int $id L'ID de l'image (clé primaire)
     */
    public function __construct(int $id){
        $this->id = $id;
    }

    // --- Getters ---

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    // --- Setters ---

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setProductId(int $product_id): self
    {
        $this->product_id = $product_id;
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    // --- Méthode JsonSerializable ---

    /**
     * Définit comment convertir un objet ProductImage en JSON.
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "url" => $this->url
        ];
    }
}

?>