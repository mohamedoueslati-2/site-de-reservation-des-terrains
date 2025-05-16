<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && $password == $admin['mot_de_passe']) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['nom'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Email ou mot de passe invalide";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
            padding: 2rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 80%;
            max-width: 450px;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
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

        .error-message {
            color: var(--error-color);
            font-size: 0.85rem;
            display: block;
            margin: 0.5rem 0;
            padding: 0.5rem;
            background-color: #ffebee;
            border-radius: 0.5rem;
        }

        .form button {
            cursor: pointer;
            width: 100%;
            padding: .6rem 0;
            margin-top: 1rem;
            border-radius: .5rem;
            border: none;
            background-color: var(--primary-color);
            color: var(--white);
            font-size: 1.2rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form button:hover {
            background-color: var(--secondary-color);
        }

        .form h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
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
            z-index: -1;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        /* RESPONSIVE */
        @media only screen and (max-width: 768px) {
            .container::before {
                height: 100vh;
                border-bottom-right-radius: 0;
                border-top-left-radius: 0;
                transform: none;
                right: 0;
            }

            .col {
                width: 100%;
            }

            .content-row {
                align-items: flex-start !important;
            }

            .text h2 {
                font-size: 2rem;
                margin: 1rem;
            }
            
            .form {
                width: 90%;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- FORM SECTION -->
        <div class="row">
            <div class="col align-items-center flex-col">
                <div class="form-wrapper align-items-center">
                    <div class="form">
                        <h2>Admin Login</h2>
                        
                        <?php if (isset($error)) { ?>
                            <div class="error-message"><?php echo $error; ?></div>
                        <?php } ?>
                        
                        <form method="post">
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" id="email" name="email" placeholder="Email" required>
                            </div>
                            
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                            </div>
                            
                            <button type="submit">Connexion</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- CONTENT SECTION -->
            <div class="col align-items-center flex-col">
                <div class="text">
                    <h2>Bienvenue dans l'espace administrateur</h2>
                    <p>Connectez-vous pour gérer les terrains, les réservations et les joueurs.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>