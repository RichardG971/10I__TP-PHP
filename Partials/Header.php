<!DOCTYPE html>
<html lang="fr">
<head>
    <title>PHP_TP</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
        h1, h2, h3 {
            font-variant: small-caps;
        }

        .posCenter {
            margin-left: auto;
            margin-right: auto;
        }
        
        .modalBgc {
            background-color: rgba(0, 0, 0, 0.8);
            height: 100%;
            width: 100%;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1100;
            overflow-y: hidden;
            visibility: hidden;
        }

        /* Combinaison avec la class 'formRes' ou 'formResFinal' mis en place avec php après la soumission du formulaire. */
        .formRes.modalBgc,
        .formResFinal.modalBgc {
            visibility: visible;
        }

        .modalRes {
            overflow-y: auto;
        }

        label {
            font-weight: bold;
        }

        .accueil, .chDetail, label {
            font-size: 1.2rem;
        }
        .accueil .divImg img {
            max-width: 95%;
            max-height: 150px;
        }
        .chambre .divImg img,
        .chEdit .divImg img {
            max-width: 200px;
            max-height: 200px;
        }
        .chambre .divImg,
        .chEdit .divImg {
            position: relative;
        }
        .chDetail .divImg img,
        .reserv .divImg img {
            max-width: 95%;
            max-height: 30rem;
        }
        .accueil .divImg .imgHov,
        .chDetail .divImg .imgHov,
        .reserv .divImg .imgHov,
        .chEdit .divImg .imgHov {
            position: absolute;
            max-height: 40rem;
            max-width: 40rem;
            top: -20px;
            box-shadow: 0 0 0 0.25rem #17a2b8;
            color: rgb(255, 193, 7);
            transform: translate(-50%, 0);
            visibility: hidden;
            margin-bottom: 5%;
        }
        .chambre .divImg .imgHov {
            position: absolute;
            max-height: 30rem;
            max-width: 30rem;
            top: -50px;
            left: 0;
            box-shadow: 0 0 0 0.25rem #17a2b8;
            color: rgb(255, 193, 7);
            visibility: hidden;
            margin-bottom: 5%;
        }
        .divImg span:hover .imgHov { /* Attention, si certain conteneur sont identifié par un id, 'hover' ne fonctionnerait pas si l'id n'est pas indiqué */
            visibility: visible;
            z-index: 1050;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>

<body class="container">
    <header>
        <nav id="navHead" class="navbar navbar-expand-sm bg-primary navbar-dark fixed-top mb-5 justify-content-between">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="../Client/accueil.php">ACCUEIL</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <!-- Dropdown -->

                <?php if(isset($_SESSION['login'])) { ?>
                
                <li class="nav-item active dropdown ml-auto ">
                    <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown"><?= $_SESSION['login'] ?><span id="role" class="d-none"><?= $_SESSION['role'] ?></span></a>
                    <div class="dropdown-menu dropdown-menu-right">

                        <?php if($_SESSION['role'] === 1) { ?>
                        <a class="dropdown-item" href="../Admin/userAjout.php">Ajouter un utilisateur</a>
                        <?php } ?>

                        <a class="dropdown-item" href="../Admin/admin.php">Gestion des réservations</a>
                        <a class="dropdown-item" href="../Admin/chambre.php" id="gestCh">Gestion des chambres</a>
                        <a class="dropdown-item" href="../Admin/Authentification/logout.php">Déconnexion</a>
                    </div>
                </li>

                <?php } ?>
                
            </ul>
        </nav>
    </header>
    
    <main>