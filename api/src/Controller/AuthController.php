<?php
require_once "src/Controller/EntityController.php";
require_once "src/Repository/UserRepository.php";
require_once "src/Class/User.php";

/**
 * AuthController
 * Gère les requêtes d'authentification (login, logout, session check)
 */
class AuthController extends EntityController {

    private UserRepository $users;

    public function __construct(){
        $this->users = new UserRepository();
        // Assurer que la session est démarrée pour la gestion d'état (Critère 7 / DoD 3)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Connexion (Critère d'acceptation 2)
    protected function processPostRequest(HttpRequest $request) {
        $id = $request->getId(); // ex: "login" ou "logout"

        // parse payload: prefer JSON, fallback to params (FormData / x-www-form-urlencoded)
        $json = $request->getJson();
        $obj = $json ? json_decode($json) : null;
        if ($obj === null) {
            $obj = (object)[
                'email' => $request->getParam('email') ?? null,
                'password' => $request->getParam('password') ?? null
            ];
        }

        // LOGIN -> POST /api/auth/login
        if ($id === "login") {
            if (!isset($obj->email) || !isset($obj->password) || empty($obj->email) || empty($obj->password)) {
                http_response_code(400);
                return ["error" => "Email et mot de passe requis."];
            }

            $user = $this->users->findByEmail(trim($obj->email));
            if ($user === null || !password_verify($obj->password, $user->getPasswordHash())) {
                http_response_code(401);
                return ["error" => "Identifiants incorrects."];
            }

            // succès -> créer session et normaliser la clé
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['username']   = $user->getUsername();

            return ["success" => true, "user" => $user];
        }

        // LOGOUT -> POST /api/auth/logout (ton front utilise POST)
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
            return ["success" => true, "message" => "Déconnecté."];
        }

        http_response_code(400);
        return ["error" => "Requête invalide."];
    }
    
    // Vérification de session (pour le frontend au démarrage)
    protected function processGetRequest(HttpRequest $request) {
        if (isset($_SESSION['auth_user_id'])) {
            $user = $this->users->find($_SESSION['auth_user_id']);
            if ($user) {
                return ["is_authenticated" => true, "user" => $user];
            } else {
                session_destroy();
                return ["is_authenticated" => false];
            }
        }
        return ["is_authenticated" => false];
    }
    
    // Disable other methods
    protected function processPutRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Méthode non autorisée."]; }
    protected function processPatchRequest(HttpRequest $request) { http_response_code(405); return ["error" => "Méthode non autorisée."]; }
}
?>