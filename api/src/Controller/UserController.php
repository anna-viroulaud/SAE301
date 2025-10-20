<?php
require_once "src/Controller/EntityController.php";
require_once "src/Repository/UserRepository.php";
require_once "src/Class/User.php";

class UserController extends EntityController {
    private UserRepository $users;

    public function __construct(){
        $this->users = new UserRepository();
    }

    protected function processGetRequest(HttpRequest $request) {
        $id = $request->getId(); // si URI /api/users/profile -> id = "profile"
        if ($id === "profile") {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                return ["error" => true, "message" => "Vous devez être connecté pour accéder au profil."];
            }
            $u = $this->users->find($_SESSION['user_id']);
            if ($u === null) {
                http_response_code(404);
                return ["error" => true, "message" => "Utilisateur introuvable."];
            }
            return $u;
        }

        if ($id) {
            $u = $this->users->find($id);
            return $u === null ? false : $u;
        } else {
            return $this->users->findAll();
        }
    }

    protected function processPostRequest(HttpRequest $request) {
        $id = $request->getId();
        $json = $request->getJson();
        $obj = json_decode($json);

        // LOGIN
        if ($id === "login") {
            if (!isset($obj->email) || !isset($obj->password)) {
                http_response_code(400);
                return ["error" => true, "message" => "Email et mot de passe requis."];
            }
            $email = trim($obj->email);
            $password = $obj->password;
            $user = $this->users->findByEmail($email);
            if ($user === null || !password_verify($password, $user->getPasswordHash())) {
                http_response_code(401);
                return ["error" => true, "message" => "Identifiants incorrects."];
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user->getId();
            return $user;
        }

        // LOGOUT
        if ($id === "logout") {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            return ["ok" => true, "message" => "Déconnexion réussie."];
        }

        // REGISTER (POST /api/users)
        if (!isset($obj->email) || !isset($obj->password) || !isset($obj->username)) {
            http_response_code(400);
            return ["error" => true, "message" => "Nom d'utilisateur, email et mot de passe requis."];
        }
        $email = trim($obj->email);
        $username = trim($obj->username);
        $password = $obj->password;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return ["error" => true, "message" => "Format d'email invalide."];
        }
        if (strlen($password) < 8) {
            http_response_code(400);
            return ["error" => true, "message" => "Le mot de passe doit contenir au moins 8 caractères."];
        }

        if ($this->users->findByEmail($email) !== null) {
            http_response_code(409);
            return ["error" => true, "message" => "Un compte avec cet email existe déjà."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $u = new User(0, $username, $email);
        $u->setPasswordHash($hash);

        $ok = $this->users->save($u);
        if (!$ok) {
            http_response_code(500);
            return ["error" => true, "message" => "Impossible de créer le compte, réessayez plus tard."];
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u->getId();
        http_response_code(201);
        return $u;
    }


}
?>