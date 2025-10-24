<?php

require_once('Entity.php');

class User extends Entity {
    private int $id;
    private ?string $username = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $dateOfBirth = null;
    private ?string $email = null;
    private ?string $passwordHash = null;

    public function __construct(int $id = 0) {
        $this->id = $id;
    }

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "username" => $this->username, // Peut être null si pas utilisé
            "firstName" => $this->firstName,
            "lastName" => $this->lastName,
            "dob" => $this->dateOfBirth,
            "email" => $this->email
        ];
    }

    // getters / setters
    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $v): void { $this->username = $v; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(?string $v): void { $this->firstName = $v; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(?string $v): void { $this->lastName = $v; }

    public function getDateOfBirth(): ?string { return $this->dateOfBirth; }
    public function setDateOfBirth(?string $v): void { $this->dateOfBirth = $v; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): void { $this->email = $v; }

    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function setPasswordHash(?string $h): void { $this->passwordHash = $h; }
}

?>