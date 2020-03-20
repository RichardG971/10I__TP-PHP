<?php require_once('../Admin/Authentification/securite.php'); ?>
<?php require_once('../Partials/Header.php'); ?>

<?php
require_once('../Connexion.php');

if($connect) {
    $dateArrSearch = $dateDepSearch = '';
    $occupation = 'Toutes';
    $occupationRes = 'Toutes les chambres';

    function f_ch_retourBdd($str) {
        $str = stripslashes(html_entity_decode($str));
        return $str;
    }
    
    // Pour écrire les dates avec les mois en français.
    function dateFormat($D) {
        $tabMois = [ '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril', '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août', '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre' ];
        $D = date('d-m Y', (strtotime($D)));
        foreach($tabMois as $key => $value) {
            if(substr($D, 3, 2) == $key) { $D = str_replace('-'.$key, ' '.$value, $D); }
        }
        return $D;
    }

    // Pour ajouter l''alt' aux images sans écrire leur extension avec 'substr($string, $start, $lenght)' :
    //   $string = la variable contenant le nom de l'image,
    //   $start = '0' car on prends le nom complet,
    //   $length = 'strrpos($img, '.')' permet de récupérer la position du dernier '.' s'il y en a plusieurs et donc de donner cette longueur à la chaîne.
    function f_imgAlt($img) {
        return substr($img, 0, strrpos($img, '.'));
    }

    
    /* // Pour comparer les dates et vérifier les requêtes.
    print_r($_GET); echo '<br><br>';
    
    $sql = "SELECT * FROM reservation ORDER BY dateArrivee, dateDepart";
    $res =  mysqli_prepare($connect, $sql);
    mysqli_stmt_execute($res);
    mysqli_stmt_bind_result($res, $nCl, $nCh, $dateArr, $dateDep);
    // mysqli_stmt_bind_result($res, $nCh);
    while(mysqli_stmt_fetch($res)) {
        echo 'Client '.$nCl.' - - - Chambre '.$nCh.' - - - Arrivée '.$dateArr.' - - - Départ '.$dateDep.'<br>';
        // echo $nCh.'<br>';
    }
    mysqli_stmt_close($res); echo '<br>'; */


    if(!isset($_GET['search']) || (empty($_GET['dateArrSearch']) && empty($_GET['dateDepSearch']))) {
        $sql = "SELECT * FROM chambre";
        $res = mysqli_prepare($connect, $sql);
        // Exécution identique quelque soit la requête, donc écriture une seule fois suite au conditions. (cf. l.109)
        $nbRow = '';
    } else {
        // Sélectionner les réservations pour connaitre les dates des chambres occupées ou libres.
        $dateArrSearch = trim($_GET['dateArrSearch']);
        $dateDepSearch = trim($_GET['dateDepSearch']);
        $occupation = trim($_GET['occupation']);

        if((!empty($dateArrSearch) || !empty($dateDepSearch)) && $occupation == 'Toutes') {
            $occupation = "libre(s)";
        } else {
            $occupation = trim($_GET['occupation']);
        }

        if(!empty($dateArrSearch) && empty($dateDepSearch)) {
            $t1an = (365*24*60*60);
            $dateDepSearch = date('Y-m-d', (strtotime($dateArrSearch) + $t1an));
        }else if(empty($dateArrSearch) && !empty($dateDepSearch)) {
            $t1sem = (7*24*60*60);
            if($dateDepSearch > date('Y-m-d', (strtotime(date('Y-m-d')) + $t1sem))) {
                $dateArrSearch = date('Y-m-d');
            } else {
                $dateArrSearch = date('Y-m-d', (strtotime($dateDepSearch) - $t1sem));
            }
        }

        if($occupation == 'Toutes') {
            $occupationRes = 'Toutes les chambres';
        } else {
            $occupationRes = 'Chambre(s) '.$occupation;
        }

        
        if($occupation == 'occupée(s)') {
            // Sélection des chambres occupées.
            $sql = "SELECT * FROM chambre
            WHERE numChambre IN 
                (SELECT DISTINCT numChambre FROM reservation
                WHERE (dateArrivee >= ? AND dateArrivee < ?)
                OR (dateDepart > ? AND dateDepart <= ?)
                OR (dateArrivee < ? AND dateDepart > ?))";
            
        } else if($occupation == 'libre(s)') {
            // Sélection des chambres libres.
            $sql = "SELECT * FROM chambre
            WHERE numChambre NOT IN 
                (SELECT DISTINCT numChambre FROM reservation
                WHERE (dateArrivee >= ? AND dateArrivee < ?)
                OR (dateDepart > ? AND dateDepart <= ?)
                OR (dateArrivee < ? AND dateDepart > ?))";
        }
        $res =  mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'ssssss', $dateArrSearch, $dateDepSearch, $dateArrSearch, $dateDepSearch, $dateArrSearch, $dateDepSearch);
    }

    mysqli_stmt_execute($res);
    mysqli_stmt_bind_result($res, $nCh, $prix, $nbLits, $nbPers, $conf, $img, $descr);
    if(isset($nbRow)) {
        mysqli_stmt_store_result($res);
        $nbRow = mysqli_stmt_num_rows($res);
    }
    while(mysqli_stmt_fetch($res)) {
        $tabFetch_ch[] = [
            'nCh' => $nCh,
            'prix' => $prix,
            'nbLits' => $nbLits,
            'nbPers' => $nbPers,
            'conf' => $conf,
            'img' => $img,
            'descr' => strlen($descr) > 150 ? substr($descr, 0, 150)." ..." : $descr
        ];
    }
    if(isset($nbRow)) { mysqli_stmt_free_result($res); }
    
    if($occupation == 'occupée(s)') {
        $bg_occupation = 'bg-warning';
    }  else if($occupation == 'libre(s)') {
        $bg_occupation = 'bg-success text-white';
    } else {
        $bg_occupation = 'bg-light';
    }
} else { echo "<script>alert('Connexion perdue');</script>"; }
?>

<h2 class="text-center">Administration</h2>
<h4 class="text-center">Gestion des Chambres</h4>

<form method="get">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-10 col">

            <div class="row justify-content-center">
                <div class="form-group col-md-4 col">
                    <label for="dateArrSearch">Date d'arrivée</label>
                    <input type="date" id="dateArrSearch" class="form-control text-center" name="dateArrSearch" placeholder="rechercher">
                </div>
                <div class="form-group col-md-4 col">
                    <label for="dateDepSearch">Date de départ</label>
                    <input type="date" id="dateDepSearch" class="form-control text-center" name="dateDepSearch" placeholder="rechercher">
                </div>
                <div class="form-group col-md-4 col-6">
                    <label for="langue">Libres / Occupées</label>
                    <select class="form-control" id="occupation" name="occupation">
                        <option value="Toutes" required>Toutes</option>
                        <option value="libre(s)">Libres</option>
                        <option value="occupée(s)">Occupées</option>
                    </select>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-4 col-6 mb-3">
                    <button type="submit" class="form-control bg-primary text-white border-primary" id="search" name="search"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <?php if(isset($_GET['search'])) { ?>
            <hr>
            <h3 class="text-center">Votre recherche</h3>
            <div class="row justify-content-center">
                <div class="form-group col-md-4 col-6">
                    <div class="<?= $bg_occupation ?> form-control text-center font-weight-bolder">
                        <?= $occupationRes ?>
                    </div>
                </div>
                <div class="form-group col-md-8 col-8">
                    <div class="form-control text-center bg-light">
                    <?php
                    if(isset($dateArrSearch) && !empty($dateArrSearch) && isset($dateDepSearch) && !empty($dateDepSearch)) {
                        echo 'du <b>'.dateFormat($dateArrSearch).'</b> au <b>'.dateFormat($dateDepSearch).'</b>';
                    } else {
                        echo '<b>Toutes les dates</b>';
                    }
                    ?>
                    </div>
                </div>
            </div>
            <?php } ?>
            
        </div>
    </div>
</form>

<div class="input-group my-1 justify-content-between" style="margin-bottom: 0 !important;">
    <div>
        <a href="admin.php" class="btn btn-info">RESERVATION</a>
    </div>
    
    <?php if($_SESSION['role'] == 1) { ?>
    <div>
        <a href="chAjout.php" class="btn btn-primary">Ajouter une chambre</a>
    </div>
    <?php } ?>

</div>

<?php
if(isset($nbRow) && $nbRow == 0) {
    echo "
        <div class='posCenter'>
            <h2 class='text-center'>Il n'y a pas de chambres enregistrée dans votre établissement !</h2>
            <div class='text-center'>
                <a href='chAjout.php' class='btn btn-primary'>Ajouter une chambre</a>
            </div>
        </div>
    ";
} else {
?>

<?php
if(!isset($tabFetch_ch)) {
    echo "
        <div class='posCenter'>
            <h2 class='text-center'>Il n'y a pas de ".$occupationRes." sur la période que vous avez sélectionnée</h2>
        </div>
    ";
} else {
?>

<table class="chambre table table-bordered table-striped table-hover align-middle">
    <thead class="thead-dark text-center">
        <tr>
            <th class="align-middle" colspan="2">CHAMBRE</th>
            <th class="align-middle">
                Lits
                <br>Capacité
            </th>
            <th class="align-middle">Confort</th>
            <th class="align-middle">Description</th>
            <th class="align-middle">Prix<br>/ nuit</th>
            <th class="align-middle">Action</th>
        </tr>
    </thead>
    <tbody>

        <?php
        if($res) {
            foreach($tabFetch_ch as $tabVal) {
        ?>

        <tr>
            <td class="align-middle text-center"><?= $tabVal['nCh']; ?></td>
            <td class="align-middle text-center">
                <div class="divImg">
                    <span>
                        <img src="../Img/<?= $tabVal['img']; ?>" alt="<?= f_imgAlt($tabVal['img']); ?>">
                        <br><img src="../Img/<?= $tabVal['img']; ?>" class="imgHov rounded" alt="<?= f_imgAlt($tabVal['img']); ?>">
                    </span>
                </div>
            </td>
            <td class="align-middle">
                Nombre de lit(s)&nbsp;:&nbsp;<?= $tabVal['nbLits']; ?>
                <br><?= $tabVal['nbPers']; ?>&nbsp;personne(s)
            </td>
            <td class="align-middle"><?= f_ch_retourBdd($tabVal['conf']); ?></td>
            <td class="align-middle"><?= f_ch_retourBdd($tabVal['descr']); ?></td>
            <td class="align-middle text-center"><?= $tabVal['prix']; ?></td>
            <td class="align-middle">
                <div class="my-1"><a href="../Client/chambre_detail.php?id=<?= $tabVal['nCh']; ?>" class="btn btn-info col" title="Détails de la Chambre <?= $tabVal['nCh'] ?>"><i class="fas fa-info"></i></a><div>
                <?php if($_SESSION['role'] == 1) { ?>
                <div class="my-1"><a href="chEdite.php?chambre=<?= $tabVal['nCh']; ?>" class="btn btn-warning col" title="Editer la Chambre <?= $tabVal['nCh']; ?>"><i class="fas fa-pen"></i></a></div>
                <div class="my-1"><a onclick="return confirm('Etes vous sûr...')" href="chSuppr.php?chambre=<?= $tabVal['nCh']; ?>" class="btn btn-danger col" title="Supprimer la Chambre <?= $tabVal['nCh']; ?>"><i class="fas fa-trash"></i></a></div>
                <?php } ?>
            </td>
        </tr>
    
        <?php
            }
        }} mysqli_stmt_close($res); ?>
        
    </tbody>
</table>

<?php
}
require_once('../Partials/Footer.php');
?>
