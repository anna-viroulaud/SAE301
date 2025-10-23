<?php
require_once "src/Controller/EntityController.php";
require_once "src/Repository/UserRepository.php";
require_once "src/Class/User.php";

/**
 * UserController
 * Gère les requêtes relatives aux utilisateurs (notamment la création / inscription)
 */
class UserController extends EntityController {

    private UserRepository $users;

    public function __construct(){
        $this->users = new UserRepository();
    }

    // Inscription (Critère d'acceptation 1)
    protected function processPostRequest(HttpRequest $request) {
        $json = $request->getJson();
        $obj = json_decode($json);

        if ($obj === null) {
            $obj = (object)[
                'username' => $request->getParam('username') ?? null,
                'email'    => $request->getParam('email') ?? null,
                'password' => $request->getParam('password') ?? null
            ];
        }

        if (!isset($obj->email) || !isset($obj->password) || empty($obj->email) || empty($obj->password)) {
            http_response_code(400);
            return ["error" => "Email et mot de passe requis."];
        }

        $email = trim($obj->email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return ["error" => "Email invalide."];
        }

        if ($this->users->findByEmail($email)) {
            http_response_code(409); // Conflict
            return ["error" => "Cet email est déjà utilisé."];
        }

        $u = new User(0);
        $u->setEmail($email);
        if (isset($obj->username) && $obj->username !== "") {
            $u->setUsername(trim($obj->username));
        }

        // hachage du mot de passe
        $hashedPassword = password_hash($obj->password, PASSWORD_DEFAULT);
        $u->setPasswordHash($hashedPassword);

        $ok = $this->users->save($u);

        if ($ok) {
            http_response_code(201);
            return $u;
        } else {
            http_response_code(500);
            return ["error" => "Échec de la création de l'utilisateur."];
        }
    }
    
    protected function processGetRequest(HttpRequest $request) {
        // Logique pour une page de profil basique (Critère d'acceptation 6)
        $id = $request->getId();

        // /api/users/profile -> profil de l'utilisateur connecté
        if ($id === "profile") {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                return ["error" => "Vous devez être connecté."];
            }
            $u = $this->users->find($_SESSION['user_id']);
            if ($u === null) {
                http_response_code(404);
                return ["error" => "Utilisateur introuvable."];
            }
            return $u;
        }

        // /api/users/{id}
        if ($id) {
            $u = $this->users->find($id);
            if ($u === null) {
                http_response_code(404);
                return ["error" => "Utilisateur introuvable."];
            }
            return $u;
        }

        http_response_code(403);
        return ["error" => "Accès non autorisé."];
    }

    protected function processPatchRequest(HttpRequest $request) {
        $id = $request->getId();
        if ($id !== "profile") {
            http_response_code(400);
            return ["error" => "Requête invalide."];
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            return ["error" => "Vous devez être connecté."];
        }

        // lire payload
        $json = $request->getJson();
        $obj = $json ? json_decode($json) : null;
        if ($obj === null) {
            parse_str(file_get_contents("php://input"), $parsed);
            $obj = (object)$parsed;
        }

        $userId = $_SESSION['user_id'];
        $u = $this->users->find($userId);
        if ($u === null) {
            http_response_code(404);
            return ["error" => "Utilisateur introuvable."];
        }

        // champs modifiables : username, email, password (+ éventuels firstName/lastName/dob)
        if (isset($obj->username)) {
            $u->setUsername(trim($obj->username));
        }

        if (isset($obj->email)) {
            $email = trim($obj->email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                return ["error" => "Email invalide."];
            }
            $existing = $this->users->findByEmail($email);
            if ($existing !== null && $existing->getId() !== $userId) {
                http_response_code(409);
                return ["error" => "Email déjà utilisé."];
            }
            $u->setEmail($email);
        }

        if (isset($obj->password) && $obj->password !== "") {
            if (strlen($obj->password) < 8) {
                http_response_code(400);
                return ["error" => "Le mot de passe doit contenir au moins 8 caractères."];
            }
            $u->setPasswordHash(password_hash($obj->password, PASSWORD_DEFAULT));
        }

        // champs optionnels si votre User classe les supporte
        if (isset($obj->firstName) && method_exists($u, 'setFirstName')) $u->setFirstName(trim($obj->firstName));
        if (isset($obj->lastName)  && method_exists($u, 'setLastName'))  $u->setLastName(trim($obj->lastName));
        if (isset($obj->dob)       && method_exists($u, 'setDob'))       $u->setDob(trim($obj->dob));

        $ok = $this->users->update($u);
        if (!$ok) {
            http_response_code(500);
            return ["error" => "Impossible de mettre à jour le profil."];
        }

        return $u;
    }

    // Autres méthodes désactivées pour cette US
    protected function processDeleteRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Méthode non autorisée."]; }
    protected function processPutRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Méthode non autorisée."]; }
}
?>