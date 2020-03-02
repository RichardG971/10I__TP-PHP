<?php require_once('../Admin/Authentification/securite.php'); ?>

<?php
require_once('../Connexion.php');

if($connect) {
    // Pour changer le format des dates.
    function dateFormat($D) {
        $D = date('d-m-Y', strtotime($D));
        return $D;
    }
    
    $sql = "SELECT * FROM reservation ORDER BY dateArrivee, dateDepart";
    // Préparation de la requête
    $res = mysqli_prepare($connect, $sql);

    // Exécution de la requête
    mysqli_stmt_execute($res);
} else { echo "<script>alert('Connexion perdue');</script>"; }

// if($res) {
    // Liaison de données
    mysqli_stmt_bind_result($res, $nCl, $nCh, $dateArr, $dateDep);
    mysqli_stmt_store_result($res);
    $nbRow = mysqli_stmt_num_rows($res);
?>

<h4 class="text-center">Gestion des Réservations</h4>
<div class="input-group my-1 justify-content-between" style="margin-bottom: 0 !important;">
    <div>
        <a href="chambre.php" class="btn btn-info">CHAMBRES</a>
    </div>
</div>

<?php if($nbRow == 0) { echo "<div class='posCenter'><h2 class='text-center'>Il n'y a actuellement aucune réservation !</h2></div>"; } else { ?>

<table class="table table-bordered table-striped table-hover text-center align-middle">
    <thead class="thead-dark">
        <tr>
            <th class="align-middle">Client</th>
            <th class="align-middle">Chambre</th>
            <th class="align-middle">Arrivée</th>
            <th class="align-middle">Départ</th>
            <th class="align-middle">Action</th>
        </tr>
    </thead>
    <tbody>

        <?php while(mysqli_stmt_fetch($res)) { ?>

        <tr>
            <td class="align-middle"><?= $nCl; ?></td>
            <td class="align-middle"><?= $nCh; ?></td>
            <td class="align-middle"><?= date('d-m-Y', strtotime($dateArr)); ?></td>
            <td class="align-middle"><?= date('d-m-Y', strtotime($dateDep)); ?></td>
            <td class="align-middle">
                <div class="my-1"><a href="resDetail.php?client=<?= $nCl; ?>&chambre=<?= $nCh; ?>" class="btn btn-info col" title="Détails de la réservation du client <?= $nCl; ?>"><i class="fas fa-info"></i></a></div>
                <div class="my-1"><a href="resEdite.php?client=<?= $nCl; ?>&chambre=<?= $nCh; ?>" class="btn btn-warning col" title="Editer la réservation du client <?= $nCl; ?>"><i class="fas fa-pen"></i></a></div>

                <?php if($_SESSION['role'] == 1 || trim($dateDep) <= date('Y-m-d')) { ?>
                <div class="my-1"><a onclick="return confirm('Etes vous sûr...');" href="resSuppr.php?client=<?= $nCl; ?>&chambre=<?= $nCh; ?>" class="btn btn-danger col" title="Supprimer la réservation du client <?= $nCl ?>"><i class="fas fa-trash"></i></a></div>
                <?php } ?>

            </td>
        </tr>
    
        <?php } mysqli_stmt_free_result($res); ?>
        
    </tbody>
</table>

<?php } mysqli_stmt_close($res);