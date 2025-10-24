<?php

require_once('Entity.php');

class Cart extends Entity {
    private int $userId;
    private array $items; // array of [ "productId" => int, "quantity" => int ]

    public function __construct(int $userId = 0, array $items = []) {
        $this->userId = $userId;
        $this->items = $items;
    }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $id): void { $this->userId = $id; }

    public function getItems(): array { return $this->items; }
    public function setItems(array $items): void { $this->items = $items; }

    public function jsonSerialize(): mixed {
        return [
            "userId" => $this->userId,
            "items" => $this->items
        ];
    }
}

?>