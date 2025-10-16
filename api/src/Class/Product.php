<?php

require_once('Entity.php');

/**
 *  Class Product
 * 
 *  Représente un produit avec ses propriétés : id, name, category, price, image
 * 
 *  Implémente l'interface JsonSerializable 
 *  pour permettre la conversion automatique en JSON.
 */
class Product extends Entity implements JsonSerializable {
    private int $id;                    // id du produit
    private ?string $name = null;       // nom du produit
    private ?int $idcategory = null;    // id de la catégorie
    private ?float $price = null;       // prix du produit
    private ?string $image = null;      // image principale (URL ou chemin)

    public function __construct(int $id){
        $this->id = $id;
    }

    /**
     * Get the value of id
     */ 
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the value of idcategory
     */ 
    public function getIdcategory(): ?int
    {
        return $this->idcategory;
    }

    /**
     * Set the value of idcategory
     *
     * @return  self
     */ 
    public function setIdcategory(int $idcategory): self
    {
        $this->idcategory = $idcategory;
        return $this;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get the value of image
     */ 
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */ 
    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Conversion JSON (pour API / frontend)
     */
    public function jsonSerialize(): mixed {
        return [
            "id"        => $this->id,
            "name"      => $this->name,
            "category"  => $this->idcategory,
            "price"     => $this->price,
            "image"     => $this->image
        ];
    }
}
