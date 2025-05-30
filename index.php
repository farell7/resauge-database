<?php
session_start();

// Définir le chemin de base
define('BASE_PATH', '/SAE_201-203');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    require_once 'PHP/connexion.php';
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Vérifier si le compte est validé (sauf pour les administrateurs)
            if ($user['role'] !== 'admin' && !$user['valide']) {
                $_SESSION['error'] = 'Votre compte est en attente de validation par un administrateur.';
                header('Location: ' . BASE_PATH . '/index.php');
                exit();
            }
            
            $_SESSION['utilisateur'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            switch($user['role']) {
                case 'admin':
                    header('Location: ' . BASE_PATH . '/PHP/admin.php');
                    break;
                case 'teacher':
                    header('Location: ' . BASE_PATH . '/PHP/teacher.php');
                    break;
                case 'agent':
                    header('Location: ' . BASE_PATH . '/PHP/agent.php');
                    break;
                case 'student':
                    header('Location: ' . BASE_PATH . '/PHP/student.php');
                    break;
                default:
                    $_SESSION['error'] = 'invalid_role';
                    header('Location: ' . BASE_PATH . '/index.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'invalid_credentials';
            header('Location: ' . BASE_PATH . '/index.php');
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'database_error';
        header('Location: ' . BASE_PATH . '/index.php');
        exit();
    }
}

if (isset($_SESSION['utilisateur'])) {
    $role = $_SESSION['utilisateur']['role'];
    switch($role) {
        case 'admin':
            header('Location: ' . BASE_PATH . '/PHP/admin.php');
            break;
        case 'teacher':
            header('Location: ' . BASE_PATH . '/PHP/teacher.php');
            break;
        case 'agent':
            header('Location: ' . BASE_PATH . '/PHP/agent.php');
            break;
        case 'student':
            header('Location: ' . BASE_PATH . '/PHP/student.php');
            break;
        default:
            header('Location: ' . BASE_PATH . '/index.php');
    }
    exit();
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./CSS/styleindex.css">
    <title>ResaUGE - Connexion</title>
</head>

<body id="login-page">
    <div class="container" id="container">
        <div class="form-container sign-up">
            <div class="register-title"><h1>Créer un compte</h1></div>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <form action="PHP/register.php" method="POST">
                <input type="text" name="nom" placeholder="Nom" required> 
                <input type="text" name="prenom" placeholder="Prénom" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <input type="text" name="pseudo" placeholder="Pseudo" required>
                <input type="text" name="postal" placeholder="Code Postal" pattern="^\d{5}$" title="Entrez un code postal Français à 5 chiffres" required>
                <input type="date" name="birthdate" placeholder="Date de naissance" required>
                <select name="role" required>
                    <option value="student">Etudiant</option>
                    <option value="teacher">Enseignant</option>
                    <option value="agent">Agent</option>
                    <option value="admin">Administrateur</option>
                </select>
                <button type="submit">S'inscrire</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form action="index.php" method="POST">
                <h1>Se connecter</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php echo $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" id="login-email" required>
                <input type="password" name="password" placeholder="Mot de passe" id="login-password" required>
                <a href="PHP/forgot_password.php">Mot de passe oublié?</a>
                <button type="submit">Se connecter</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Bienvenue !</h1>
                    <p id="role-description-login">Saisissez vos données personnelles pour profiter pleinement de toutes les fonctionnalités du service.</p>
                    <button class="hidden" id="login">Se connecter</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Ravi de vous revoir !</h1>
                    <p id="role-description-register">Connectez vous avec vos informations de connexion pour accéder à toutes les fonctionnalités du service.</p>
                    <button class="hidden" id="register">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>

    <script src="./JS/script.js"></script>
</body>
</html>