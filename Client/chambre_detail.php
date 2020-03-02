<?php session_start(); ?>
<?php require_once('../Partials/Header.php'); ?>

<?php
require_once('../Connexion.php');

if(isset($_GET['id'])) {
    $id = (int)htmlentities(trim($_GET['id']));
    
    if($connect) {
        // Pour ajouter l''alt' aux images sans écrire leur extension avec 'substr($string, $start, $lenght)' :
        //   $string = la variable contenant le nom de l'image,
        //   $start = '0' car on prends le nom complet,
        //   $length = 'strrpos($img, '.')' permet de récupérer la position du dernier '.' s'il y en a plusieurs et donc de donner cette longueur à la chaîne.
        function f_imgAlt($img) {
            return substr($img, 0, strrpos($img, '.'));
        }

        $sql = "SELECT * FROM chambre WHERE numChambre = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'i', $id);
        mysqli_stmt_execute($res);

        if($res) {
            mysqli_stmt_bind_result($res, $nCh, $prix, $nbLits, $nbPers, $confort, $img, $descr);
            mysqli_stmt_fetch($res);
        }
        mysqli_stmt_close($res);
    } else { header('location:accueil.php'); }
}
?>

<h2 class="text-center">Booking-AFPA.com</h2>
<section class="chDetail posCenter row">
    <h1 class="text-center w-100 bg-info text-white rounded">Chambre <?= $nCh; ?></h1>
        <div class="divImg col-md-4 text-center align-self-center">
            <span>
                <img src="../Img/<?= $img; ?>" alt="<?= f_imgAlt($img) ?>" >
                <br><img src="../Img/<?= $img; ?>" class="imgHov rounded" alt="<?= f_imgAlt($img) ?>" >
            </span>
        </div>
        <div class="col-md-8">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><b>NumChambre</b> <?= $nCh; ?></li>
                
                <li class="list-group-item"><b>Capacité :</b> <?= $nbPers; ?> personne(s)</li>
                
                
                <li class="list-group-item"><b>Nombre de lit(s) :</b> <?= $nbLits; ?></li>
                <li class="list-group-item"><b>Confort :</b> <?= $confort; ?></li>
                <li class="list-group-item">
                    <h3>Description</h3>
                    <?= $descr; ?>
                </li>
                <li class="list-group-item"><b>Prix :</b> <?= $prix; ?></li>
            </ul>
        </div>
    <div class="text-right w-100">
        <a href="accueil.php" class="btn btn-info col-sm-3 my-1">Accueil</a>
        <a href="reserver.php?id=<?= $nCh ?>" class="btn btn-primary col-sm-3 my-1">Réserver</a>
    </div>
    <?php if(isset($_SESSION['login'])) { ?>
    <div class="text-right w-100">
        <?php if($_SESSION['role'] == 1) { ?>
        <a href="../Admin/chEdite.php?chambre=<?= $nCh; ?>" class="btn btn-warning col-sm-3 my-1" title="Editer la Chambre <?= $nCh; ?>">Editer</a>
        <?php } ?>
        <a href="../Admin/chambre.php" class="btn btn-success col-sm-3 my-1">CHAMBRES</a>
    </div>
    <?php } ?>
</section>

<?php require_once('../Partials/Footer.php'); ?>