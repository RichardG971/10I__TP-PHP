<?php require_once('../Admin/Authentification/securite.php');  ?>

<?php
require_once('../Connexion.php');

if($_SESSION['role'] == 1) {
    if(isset($_GET['chambre'])) {
        $id = (int)htmlentities(trim($_GET['chambre']));
        
        if($connect) {
            $sql = "SELECT image FROM chambre WHERE numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'i', $id);
            mysqli_stmt_execute($res);

            if($res) {
                mysqli_stmt_bind_result($res, $img);
                mysqli_stmt_fetch($res);
                mysqli_stmt_close($res); // Si plusieurs requête, penser à les fermer au préalable.
            }
            
            $sql = "DELETE FROM chambre WHERE numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'i', $id);
            $exe = mysqli_stmt_execute($res);
            
            if($exe) {
                // echo mysqli_stmt_affected_rows($res);
                unlink('../Img/'.$img);
                header("location:../Admin/chambre.php");
                require_once('../Partials/Header.php'); // Si avant 'header("location:...")', 'header("location:...")' ne fonctionnera pas car il ne faut pas d'HTML avant de l'utiliser.
                echo '<h2 class="text-center">Administration</h2>';
                echo '<div class="posCenter"><h2 class="text-center">Suppression réussie</h2>';
            } else {
                require_once('../Partials/Header.php');
                echo '<h2 class="text-center">Administration</h2>';
                echo '<div class="posCenter"><h2 class="text-center">Echec de la suppression...</h2>';
            }
            echo '<p class="text-center">Nom image : '.$img.'</p>';
            mysqli_stmt_close($res);
        } else { echo "<script>alert('Connexion perdue');</script>"; }
    }
?>
        <div class="text-center w-100">
            <a href="../Admin/chambre.php" class="btn btn-info col-sm-3 my-1">RETOUR</a>
        </div>
    </div>

<?php
    require_once('../Partials/Footer.php');
} else {
    header('location:../Admin/index.php');
}
