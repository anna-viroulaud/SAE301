<?php

require_once('Entity.php');

class User extends Entity {
    private int $id;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $passwordHash = null;

    public function __construct(int $id = 0, ?string $username = null, ?string $email = null) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "username" => $this->username,
            "email" => $this->email
        ];
    }

    // getters / setters
    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $v): void { $this->username = $v; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): void { $this->email = $v; }

    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function setPasswordHash(?string $h): void { $this->passwordHash = $h; }
}

?>