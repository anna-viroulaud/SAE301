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
        $u = new User((int)$obj->id, $obj->username, $obj->email);
        $u->setPasswordHash($obj->password_hash ?? null);
        return $u;
    }

    public function findAll(): array {
        $requete = $this->cnx->prepare("SELECT id, username, email FROM Users");
        $requete->execute();
        $rows = $requete->fetchAll(PDO::FETCH_OBJ);
        $res = [];
        foreach($rows as $r) $res[] = new User((int)$r->id, $r->username, $r->email);
        return $res;
    }

    public function findByEmail(string $email): ?User {
        $requete = $this->cnx->prepare("SELECT * FROM Users WHERE email=:email LIMIT 1");
        $requete->bindParam(':email', $email, PDO::PARAM_STR);
        $requete->execute();
        $obj = $requete->fetch(PDO::FETCH_OBJ);
        if ($obj === false) return null;
        $u = new User((int)$obj->id, $obj->username, $obj->email);
        $u->setPasswordHash($obj->password_hash ?? null);
        return $u;
    }

    public function save($user) {
        $requete = $this->cnx->prepare(
            "INSERT INTO Users (username, email, password_hash) VALUES (:username, :email, :password_hash)"
        );

        $username = $user->getUsername();
        $email = $user->getEmail();
        $hash = $user->getPasswordHash();
        $requete->bindParam(':username', $username);
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
            "UPDATE Users SET username=:username, email=:email, password_hash=:password_hash WHERE id=:id"
        );
        $id = $user->getId();
        $username = $user->getUsername();
        $email = $user->getEmail();
        $hash = $user->getPasswordHash();
        $requete->bindParam(':id', $id);
        $requete->bindParam(':username', $username);
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