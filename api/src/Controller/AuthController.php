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
        $json = $request->getJson();
        $obj = json_decode($json);

        if (!isset($obj->email) || !isset($obj->password)) {
            http_response_code(400);
            return ["error" => "Email et mot de passe requis."];
        }
        
        $user = $this->users->findByEmail($obj->email);
        
        // Vérifier l'utilisateur et le mot de passe
        if ($user && password_verify($obj->password, $user->getPasswordHash())) {
            // Connexion réussie : Sécurité de session
            session_regenerate_id(true); 
            $_SESSION['auth_user_id'] = $user->getId();
            $_SESSION['auth_user_email'] = $user->getEmail();
            $_SESSION['auth_username'] = $user->getUsername();
            
            // On renvoie les infos non sensibles de l'utilisateur
            return ["success" => true, "user" => $user];
        } else {
            http_response_code(401); // Unauthorized
            return ["error" => "Identifiants incorrects."];
        }
    }
    
    // Déconnexion (Critère d'acceptation 3)
    protected function processDeleteRequest(HttpRequest $request) {
        // Destruction de session complète
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        return ["success" => true, "message" => "Déconnexion réussie."];
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