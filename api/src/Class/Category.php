<?php

require_once ('Entity.php');

/**
 *  Class Category
 * 
 *  Représente une catégorie avec id et name
 */
class Category extends Entity {
    private int $id;
    private ?string $name = null;

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
     */ 
    public function setId(int $id): void
    {
        $this->id = $id;
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
     */ 
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Define how to convert/serialize a Category to a JSON format
     */
    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "name" => $this->name
        ];
    }
}

?>