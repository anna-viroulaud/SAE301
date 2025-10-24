<?php
require_once("src/Repository/EntityRepository.php");
require_once("src/Class/User.php");

class UserRepository extends EntityRepository {

    public function __construct(){
        parent::__construct();
    }

    public function find($id): ?User {
        $requete = $this->cnx->prepare("SELECT * FROM Users WHERE id=:value");
        $requete->bindParam(':value', $id, PDO::PARAM_INT);
        $requete->execute();
        $obj = $requete->fetch(PDO::FETCH_OBJ);
        if ($obj === false) return null;
        
        $u = new User((int)$obj->id);
        $u->setUsername($obj->username ?? null);
        $u->setFirstName($obj->first_name ?? null);
        $u->setLastName($obj->last_name ?? null);
        $u->setDateOfBirth($obj->date_of_birth ?? null);
        $u->setEmail($obj->email ?? null);
        $u->setPasswordHash($obj->password_hash ?? null);
        return $u;
    }

    public function findAll(): array {
        $requete = $this->cnx->prepare("SELECT id FROM Users");
        $requete->execute();
        $rows = $requete->fetchAll(PDO::FETCH_OBJ);
        $res = [];
        foreach($rows as $r) {
            $user = $this->find((int)$r->id);
            if ($user) $res[] = $user;
        }
        return $res;
    }

    public function findByEmail(string $email): ?User {
        $requete = $this->cnx->prepare("SELECT * FROM Users WHERE email=:email LIMIT 1");
        $requete->bindParam(':email', $email, PDO::PARAM_STR);
        $requete->execute();
        $obj = $requete->fetch(PDO::FETCH_OBJ);
        if ($obj === false) return null;
        
        $u = new User((int)$obj->id);
        $u->setUsername($obj->username ?? null);
        $u->setFirstName($obj->first_name ?? null);
        $u->setLastName($obj->last_name ?? null);
        $u->setDateOfBirth($obj->date_of_birth ?? null);
        $u->setEmail($obj->email ?? null);
        $u->setPasswordHash($obj->password_hash ?? null);
        return $u;
    }

    public function save($user) {
        $requete = $this->cnx->prepare(
            "INSERT INTO Users (username, first_name, last_name, date_of_birth, email, password_hash) 
             VALUES (:username, :first_name, :last_name, :date_of_birth, :email, :password_hash)"
        );

        $username = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $dob = $user->getDateOfBirth();
        $email = $user->getEmail();
        $hash = $user->getPasswordHash();
        
        $requete->bindParam(':username', $username);
        $requete->bindParam(':first_name', $firstName);
        $requete->bindParam(':last_name', $lastName);
        $requete->bindParam(':date_of_birth', $dob);
        $requete->bindParam(':email', $email);
        $requete->bindParam(':password_hash', $hash);
        
        $ok = $requete->execute();
        if ($ok) {
            $id = (int)$this->cnx->lastInsertId();
            $user->setId($id);
            return true;
        }
        return false;
    }

    public function update($user) {
        $requete = $this->cnx->prepare(
            "UPDATE Users 
             SET username=:username, first_name=:first_name, last_name=:last_name, 
                 date_of_birth=:date_of_birth, email=:email, password_hash=:password_hash 
             WHERE id=:id"
        );
        
        $id = $user->getId();
        $username = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $dob = $user->getDateOfBirth();
        $email = $user->getEmail();
        $hash = $user->getPasswordHash();
        
        $requete->bindParam(':id', $id);
        $requete->bindParam(':username', $username);
        $requete->bindParam(':first_name', $firstName);
        $requete->bindParam(':last_name', $lastName);
        $requete->bindParam(':date_of_birth', $dob);
        $requete->bindParam(':email', $email);
        $requete->bindParam(':password_hash', $hash);
        
        return $requete->execute();
    }

    public function delete($id) {
        $requete = $this->cnx->prepare("DELETE FROM Users WHERE id=:id");
        $requete->bindParam(':id', $id, PDO::PARAM_INT);
        return $requete->execute();
    }
}

?>