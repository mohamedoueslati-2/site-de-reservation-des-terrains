<?php
session_start();
require_once 'config.php';

// Partie création de compte
if (isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['reg_email']);
    $mot_de_passe = trim($_POST['reg_password']);
    $num_telephone = trim($_POST['num_telephone']);

    // Validation
    if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($num_telephone)) {
        $register_error = "Tous les champs sont obligatoires";
    } elseif (!preg_match('/^[0-9]{8}$/', $num_telephone)) {
        $register_error = "Le numéro de téléphone doit contenir exactement 8 chiffres";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM joueur WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $register_error = "Cet email est déjà utilisé par un autre joueur";
        } else {
            // Insérer le joueur (statut actif par défaut)
            try {
                $stmt = $pdo->prepare("INSERT INTO joueur (nom, email, mot_de_passe, num_telephone, statut) VALUES (?, ?, ?, ?, 'actif')");
                $stmt->execute([$nom, $email, $mot_de_passe, $num_telephone]);
                $register_success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
            } catch (PDOException $e) {
                $register_error = "Erreur lors de la création du compte: " . $e->getMessage();
            }
        }
    }
}

// Partie connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM joueur WHERE email = ?");
    $stmt->execute([$email]);
    $joueur = $stmt->fetch();
    
    if ($joueur && $password == $joueur['mot_de_passe']) {
        if ($joueur['statut'] == 'bloqué') {
            $error = "Votre compte a été bloqué. Veuillez contacter l'administrateur.";
        } else {
            $_SESSION['joueur_id'] = $joueur['id'];
            $_SESSION['joueur_name'] = $joueur['nom'];
            header("Location: joueur/dashboard.php");
            exit();
        }
    } else {
        $error = "Email ou mot de passe invalide";
    }
}

// Determine container class based on form submission
$containerClass = isset($_POST['register']) ? 'sign-up' : 'sign-in';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Joueur</title>
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* CSS Variables */
        :root {
            --white: #fff;
            --gray: #f5f5f5;
            --gray-2: #8c8c8c;
            --primary-color: rgb(37, 36, 75);
            --secondary-color: #536DFE;
            --facebook-color: #4267B2;
            --google-color: #DB4437;
            --twitter-color: #1DA1F2;
            --insta-color: #E1306C;
            --error-color: #ff3860;
            --success-color: #09c372;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            height: 100vh;
        }

        .col {
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form {
            padding: 1rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1s;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 1rem 0;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1.4rem;
            color: var(--gray-2);
        }

        .input-group input {
            width: 100%;
            padding: 1rem 3rem;
            font-size: 1rem;
            background-color: var(--gray);
            border-radius: .5rem;
            border: 0.125rem solid var(--white);
            outline: none;
        }

        .input-group input:focus {
            border: 0.125rem solid var(--primary-color);
        }

        /* Validation styles */
        .error-message {
            color: var(--error-color);
            font-size: 0.8rem;
            display: block;
            margin-top: 0.25rem;
        }

        .success-message {
            color: var(--success-color);
            font-size: 0.8rem;
            display: block;
            margin: 0.5rem 0;
        }

        .input-group input.error {
            border: 0.125rem solid var(--error-color);
        }

        .input-group input.success {
            border: 0.125rem solid var(--success-color);
        }
        /* End validation styles */

        .form button {
            cursor: pointer;
            width: 100%;
            padding: .6rem 0;
            border-radius: .5rem;
            border: none;
            background-color: var(--primary-color);
            color: var(--white);
            font-size: 1.2rem;
            outline: none;
        }

        .form p {
            margin: 1rem 0;
            font-size: .7rem;
        }

        .flex-col {
            flex-direction: column;
        }

        .social-list {
            margin: 2rem 0;
            padding: 1rem;
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1.2s;
        }

        .pointer {
            cursor: pointer;
        }

        .container.sign-in .form.sign-in,
        .container.sign-up .form.sign-up {
            transform: scale(1);
        }

        .content-row {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 6;
            width: 100%;
        }

        .text {
            margin: 4rem;
            color: var(--white);
        }

        .text h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 2rem 0;
            transition: 1s ease-in-out;
        }

        .text p {
            font-weight: 600;
            transition: 1s ease-in-out;
            transition-delay: .2s;
        }

        .img img {
            width: 30vw;
            transition: 1s ease-in-out;
            transition-delay: .4s;
        }

        .text.sign-in h2,
        .text.sign-in p,
        .img.sign-in img {
            transform: translateX(-250%);
        }

        .text.sign-up h2,
        .text.sign-up p,
        .img.sign-up img {
            transform: translateX(250%);
        }

        .container.sign-in .text.sign-in h2,
        .container.sign-in .text.sign-in p,
        .container.sign-in .img.sign-in img,
        .container.sign-up .text.sign-up h2,
        .container.sign-up .text.sign-up p,
        .container.sign-up .img.sign-up img {
            transform: translateX(0);
        }

        /* BACKGROUND */

        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            height: 100vh;
            width: 300vw;
            transform: translate(35%, 0);
            background-image: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: 1s ease-in-out;
            z-index: 6;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        .container.sign-in::before {
            transform: translate(0, 0);
            right: 50%;
        }

        .container.sign-up::before {
            transform: translate(100%, 0);
            right: 50%;
        }

        /* RESPONSIVE */

        @media only screen and (max-width: 425px) {

            .container::before,
            .container.sign-in::before,
            .container.sign-up::before {
                height: 100vh;
                border-bottom-right-radius: 0;
                border-top-left-radius: 0;
                z-index: 0;
                transform: none;
                right: 0;
            }

            .container.sign-in .col.sign-in,
            .container.sign-up .col.sign-up {
                transform: translateY(0);
            }

            .content-row {
                align-items: flex-start !important;
            }

            .content-row .col {
                transform: translateY(0);
                background-color: unset;
            }

            .col {
                width: 100%;
                position: absolute;
                padding: 2rem;
                background-color: var(--white);
                border-top-left-radius: 2rem;
                border-top-right-radius: 2rem;
                transform: translateY(100%);
                transition: 1s ease-in-out;
            }

            .row {
                align-items: flex-end;
                justify-content: flex-end;
            }

            .form,
            .social-list {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .text {
                margin: 0;
            }

            .text p {
                display: none;
            }

            .text h2 {
                margin: .5rem;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div id="container" class="container <?php echo $containerClass; ?>">
        <!-- FORM SECTION -->
        <div class="row">
            <!-- SIGN UP (REGISTER) -->
            <div class="col align-items-center flex-col sign-up">
                <div class="form-wrapper align-items-center">
                    <div class="form sign-up">
                        <?php if (isset($register_error)): ?>
                            <div class="error-message"><?php echo $register_error; ?></div>
                        <?php endif; ?>
                        <?php if (isset($register_success)): ?>
                            <div class="success-message"><?php echo $register_success; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="input-group">
                                <i class='bx bxs-user'></i>
                                <input type="text" id="nom" name="nom" placeholder="Nom" required>
                            </div>
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" id="reg_email" name="reg_email" placeholder="Email" required>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" id="reg_password" name="reg_password" placeholder="Mot de passe" required>
                            </div>
                            <div class="input-group">
                                <i class='bx bx-phone'></i>
                                <input type="text" id="num_telephone" name="num_telephone" placeholder="Numéro de téléphone" required>
                            </div>
                            <button type="submit" name="register">
                                Créer le compte
                            </button>
                            <p>
                                <span>
                                    Déjà inscrit?
                                </span>
                                <b onclick="toggle()" class="pointer">
                                    Se connecter ici
                                </b>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END SIGN UP -->
            <!-- SIGN IN (LOGIN) -->
            <div class="col align-items-center flex-col sign-in">
                <div class="form-wrapper align-items-center">
                    <div class="form sign-in">
                        <?php if (isset($_GET['blocked'])): ?>
                            <div class="error-message">Votre compte a été bloqué. Veuillez contacter l'administrateur.</div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="error-message"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($register_success)): ?>
                            <div class="success-message"><?php echo $register_success; ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" id="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                            </div>
                            <button type="submit">
                                Connexion
                            </button>
                            <p>
                                <span>
                                    Pas encore inscrit?
                                </span>
                                <b onclick="toggle()" class="pointer">
                                    Créer un compte ici
                                </b>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
            <!-- END SIGN IN -->
        </div>
        <!-- END FORM SECTION -->
        
        <!-- CONTENT SECTION -->
        <div class="row content-row">
            <!-- SIGN IN CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-in">
                    <h2>Bienvenue</h2>
                    <p>Connectez-vous pour accéder à votre espace joueur</p>
                </div>
                <div class="img sign-in">
                    <!-- You can add an image here if needed -->
                </div>
            </div>
            <!-- END SIGN IN CONTENT -->
            
            <!-- SIGN UP CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="img sign-up">
                    <!-- You can add an image here if needed -->
                </div>
                <div class="text sign-up">
                    <h2>Rejoignez-nous</h2>
                    <p>Créez votre compte joueur pour commencer l'aventure</p>
                </div>
            </div>
            <!-- END SIGN UP CONTENT -->
        </div>
        <!-- END CONTENT SECTION -->
    </div>

    <script>
        // Toggle between sign-in and sign-up
        function toggle() {
            const container = document.getElementById('container');
            container.classList.toggle('sign-in');
            container.classList.toggle('sign-up');
        }

        // Initialize the page with the correct form showing
        document.addEventListener('DOMContentLoaded', function() {
            // If there was a registration error, make sure the register form shows
            <?php if (isset($register_error)): ?>
                document.getElementById('container').classList.remove('sign-in');
                document.getElementById('container').classList.add('sign-up');
            <?php endif; ?>
            
            // If there was a login error, make sure the login form shows
            <?php if (isset($error)): ?>
                document.getElementById('container').classList.add('sign-in');
                document.getElementById('container').classList.remove('sign-up');
            <?php endif; ?>
        });
    </script>
</body>
</html>