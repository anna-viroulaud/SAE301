<?php

require_once("src/Class/Entity.php");

/**
 * Classe Category
 */
class Category extends Entity {
    
    private int $id = 0;
    private string $name = '';
    private string $slug = '';
    private int $productCount = 0;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getSlug(): string {
        return $this->slug;
    }

    public function setSlug(string $slug): void {
        $this->slug = $slug;
    }

    public function getProductCount(): int {
        return $this->productCount;
    }

    public function setProductCount(int $count): void {
        $this->productCount = $count;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'productCount' => $this->productCount
        ];
    }
}

?>
