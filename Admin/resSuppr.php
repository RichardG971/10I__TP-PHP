<?php require_once('../Admin/Authentification/securite.php'); ?>

<h2 class="text-center">Administration</h2>

<?php
require_once('../Connexion.php');

if(isset($_GET['client']) && isset($_GET['chambre'])) {
    $idCl = (int)trim($_GET['client']);
    $idCh = (int)trim($_GET['chambre']);
    
    if($connect) {
        if($_SESSION['role'] == 1) {
            $sql = "DELETE FROM reservation WHERE numClient = ? && numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'ii', $idCl, $idCh);
            $exe = mysqli_stmt_execute($res);
        } else {
            $sql = "SELECT dateDepart FROM reservation WHERE numClient = ? && numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'ii', $idCl, $idCh);
            mysqli_stmt_execute($res);
            mysqli_stmt_bind_result($res, $dateDep);
            mysqli_stmt_fetch($res);
            mysqli_stmt_close($res);

            if(trim(stripslashes(html_entity_decode($dateDep))) > date('Y-m-d')) {
                require_once('../Partials/Header.php');
                ?>

                <div class="posCenter">
                    <h3 class="text-center bg-warning rounded px-3">
                        Vous êtes autoriser à supprimer les réservations dont les clients ont fini leur séjour.
                        <br>Pour plus d'options, contacter un administrateur.
                    <h3>
                    <h2 class="text-center">Echec de la suppression...</h2>

                <?php
            } else {
                $sql = "DELETE FROM reservation WHERE numClient = ? && numChambre = ?";
                $res = mysqli_prepare($connect, $sql);
                mysqli_stmt_bind_param($res, 'ii', $idCl, $idCh);
                $exe = mysqli_stmt_execute($res);
            }
        }

        if(isset($exe)) {
            mysqli_stmt_close($res);
            header("location:../Admin/admin.php");
            require_once('../Partials/Header.php');
            echo '<div class="posCenter"><h2 class="text-center">Suppression réussie</h2>';
        }
    } else { echo "<script>alert('Connexion perdue');</script>"; }

    ?>
    
        <div class="text-right w-100">
            <a href="../Admin/admin.php" class="btn btn-info col-sm-3 my-1">RETOUR</a>
        </div>
    </div>

    <?php require_once('../Partials/Footer.php'); }
