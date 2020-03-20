<?php require_once('../Admin/Authentification/securite.php'); ?>
<?php require_once('../Partials/Header.php'); ?>

<?php
require_once('../Connexion.php');

if(isset($_GET['client']) && isset($_GET['chambre'])) {
    $idCl_get = (int)trim($_GET['client']);
    $idCh_get = (int)trim($_GET['chambre']);
    
    if($connect) {
        // Formatage des données récupérées du formulaire après soumission.
        //    Donner en 'value' des champs les données récupérées pour que le client n'est pas à tout re-saisir en cas d'erreur.
        // Mettre la donnée soumise du champ 'nom' en majuscule avec cette fonction.
        function f_ch_recupFormFormatUpper($str) {
            $str = trim($str);
            $str = preg_replace('/[-\']/', ' ', $str);
            $tabStrReplace = [ 'a' => ['à', 'â', 'ä', 'Â', 'Ä'], 'e' => ['é', 'è', 'ê', 'ë', 'Ê', 'Ë'], 'i' => ['î', 'ï', 'Î', 'Ï'], 'o' => ['ô', 'ö', 'Ô', 'Ö'], 'u' => ['ù', 'û', 'ü', 'Û', 'Ü'], 'y' => ['ÿ'] ];
            foreach($tabStrReplace as $key => $tab) {
                foreach($tab as $value) { $str = str_replace($tab, $key, $str); }
            }
            $str = strtoupper($str);
            return $str;
        }

        // Mettre les données soumises des champs avec la première lettre de chaque mot en majuscule avec cette fonction.
        function f_ch_recupFormFormat($str) {
            $str = trim($str);
            $str = preg_replace('/[,.-]/', ' ', $str);
            $str = preg_replace('/  +/', ' ', $str);
            $tabStrReplaceUcf = [ 'a' => ['à'], 'e' => ['é', 'è'], 'i' => ['î', 'ï'], 'o' => ['ô', 'ö'], 'u' => ['û', 'ü'] ];
            $tabStrReplace = [ 'a' => ['â', 'ä', 'Â', 'Ä'], 'e' => ['ê', 'ë', 'Ê', 'Ë'], 'i' => ['Î', 'Ï'], 'o' => ['Ô', 'Ö'], 'u' => ['ù', 'Û', 'Ü'], 'y' => ['ÿ'] ];
            foreach($tabStrReplaceUcf as $key => $tab) {
                foreach($tab as $value) {
                    if(substr($str, 0, 2) == $value) { $str = substr_replace($str, $key, 0, 2); }
                }
            }
            foreach($tabStrReplace as $key => $tab) {
                foreach($tab as $value) { $str = str_replace($tab, $key, $str); }
            }
            $str = htmlentities($str); // Convertir en entités HTML car 'strtolower()' ne reconnaît pas les caractères accentués,
            $str = strtolower($str); // tout convertir en minuscule,
            $str = html_entity_decode($str); // décodé l'encodement avec 'htmlentities()',
            $str = ucwords($str); // mettre la première lettre de chaque mot en majuscule.
            $tabStrReplace = [ 'd\'' => ['D\''], 'l\'' => ['L\''], '\'A' => ['\'a', '\'à', '\'â', '\'ä', '\'Â', '\'Ä'], '\'E' => ['\'e', '\'é', '\'è', '\'ê', '\'ë', '\'Ê', '\'Ë'], '\'I' => ['\'i', '\'î', '\'ï', '\'Î', '\'Ï'], '\'O' => ['\'o', '\'ô', '\'ö', '\'Ô', '\'Ö'], '\'U' => ['\'u', '\'ù', '\'û', '\'ü', '\'Û', '\'Ü'], '\'Y' => ['\'y', '\'ÿ'] ];
            $tabStrReplace += [ ' A' => [' à'], ' E' => [' é', ' è'], ' I' => [' î', ' ï'], ' O' => [' ô', ' ö'], ' U' => [' û', ' ü'], 'd ' => ['D '], 'l ' => ['L '] ];
            foreach($tabStrReplace as $key => $tab) {
                foreach($tab as $value) { $str = str_replace($tab, $key, $str); }
            }
            return $str;
        }
        
        // Formatage des données récupérées de la base de données et des '$var_rec' du formulaire pour les comparer.
        function f_ch_retourBddUpper($str) {
            $str = trim($str);
            $str = preg_replace('/[-\']/', ' ', $str);
            $tabStrReplace = [ 'a' => ['à', 'â', 'ä', 'Â', 'Ä'], 'e' => ['é', 'è', 'ê', 'ë', 'Ê', 'Ë'], 'i' => ['î', 'ï', 'Î', 'Ï'], 'o' => ['ô', 'ö', 'Ô', 'Ö'], 'u' => ['ù', 'û', 'ü', 'Û', 'Ü'], 'y' => ['ÿ'] ];
            foreach($tabStrReplace as $key => $tab) {
                foreach($tab as $value) { $str = str_replace($tab, $key, $str); }
            }
            $str = strtoupper($str);
            return $str;
        }

        function f_ch_retourBddUcwords($str) {
            $str = stripslashes(html_entity_decode($str));
            $str = f_ch_retourBddUpper($str);
            $str = strtolower($str);
            $str = ucwords($str);
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

        // Sélection de la chambre pour récupérer son prix : 
        $sql = "SELECT prix FROM chambre WHERE numChambre = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'i', $idCh_get);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $prix);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);
        
        // Sélection de la réservation.
        $sql = "SELECT * FROM reservation WHERE numClient = ? && numChambre = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'ii', $idCl_get, $idCh_get);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nCl, $nCh, $dateArr, $dateDep);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);

        // Calcul du prix du séjour.
        $dateArrSej = new DateTime($dateArr);
        $dateDepSej = new DateTime($dateDep);

        $nbJour = $dateArrSej->diff($dateDepSej);

        $tpsSejour = $nbJour->format('%a');
        $prixSejour = ($prix * $tpsSejour).' €';

        // Sélection du client.
        $sql = "SELECT * FROM client WHERE numClient = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'i', $nCl);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nCl, $nom, $prenom, $tel, $adresse);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);

        $adresseTab = explode(", ", f_ch_retourBddUcwords($adresse));
        foreach ($adresseTab as $value) { $ad[] = $value; }
        
    } else { header('location:accueil.php'); }
} else { header('location:accueil.php'); }
?>

<h2 class="text-center">Administration</h2>
<div class="posCenter card">
    <div class="card-header text-center"><h3>Réservation du client <?= $prenom; ?> <?= $nom; ?></h3></div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="w-100"></div>
            <div class="text-center">
                <h4>Chambre <?= $nCh ?></h4>
            </div>
            <div class="w-100"></div>
            <div class="col-md-4 col-8 text-left py-3">
                <div class="row justify-content-around">
                    <div class="col">
                        Prix :
                    </div>
                    <div class="col">
                        <span id="prix"><?= $prix ?></span> € / nuit
                    </div>
                </div>
                <div class="row justify-content-around align-items-center bg-primary text-white my-3 py-3 border rounded" style="font-size: 1.2rem; font-weight: bolder;">
                    <div class="col">
                        Prix total :
                    </div>
                    <div class="col">
                        <div><?= $tpsSejour; ?> nuit(s)<br><?= $prixSejour; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-around">
            <div class="col-md-4 col-12">
                <label for="dateArr">Date d'arrivée</label>
                <span style="background-color: #e9ecef;" class="form-control text-center"><?= dateFormat($dateArr) ?></span>
            </div>
            <div class="col-md-4 col-12">
                <label for="dateDep">Date de départ</label>
                <span style="background-color: #e9ecef;" class="form-control text-center"><?= dateFormat($dateDep) ?></span>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md">
                <h3 class="col">Identité client</h3>
                <div class="w-100"></div>
                <div class="col-12">
                    <label for="nom">Nom</label>
                    <span style="background-color: #e9ecef;" class="form-control"><?= f_ch_retourBddUpper(stripcslashes(html_entity_decode($nom))); ?></span>
                </div>
                <div class="col-12">
                    <label for="prenom">Prénom</label>
                    <span style="background-color: #e9ecef;" class="form-control"><?= f_ch_retourBddUcwords($prenom); ?></span>
                </div>
                <div class="col-12">
                    <label for="tel">Téléphone</label>
                    <span style="background-color: #e9ecef;" class="form-control"><?= f_ch_retourBddUcwords($tel); ?></span>
                </div>
            </div>
            
            <hr>
            
            <div class="col-md">
                <h3 class="col-12">Adresse client</h3>
                <div class="w-100"></div>
                <div class="col">
                    <label for="nRue">Numéro et rue</label>
                    <span style="background-color: #e9ecef;" class="form-control"><?= $ad[0]; ?></span>
                </div>
                <div class="col-12">
                    <label for="CP">Code postal</label>
                    <span style="background-color: #e9ecef;" class="form-control"><?= $ad[1]; ?></span>
                </div>
                <div class="col-12">
                    <label for="ville">Ville</label>
                    <span style="background-color: #e9ecef;" class="form-control" ><?= $ad[2]; ?></span>
                </div>
            </div>
        </div>
        
        <br>
        <div class="row justify-content-center">
            
            <?php if($_SESSION['role'] == 1 || trim($dateDep) <= date('Y-m-d')) { ?>
            <a onclick="return confirm('Etes vous sûr...');" href="resSuppr.php?client=<?= $idCl_get; ?>&chambre=<?= $idCh_get; ?>" class="btn btn-danger col-md-3 mx-3 mt-3" title="Supprimer la réservation du client <?= $prenom.' '.$nom; ?>">Supprimer</a>
            <?php } ?>

            <a href="admin.php" class="btn btn-info col-md-3 mx-3 mt-3">Précédent</a>
            <a href="resEdite.php?client=<?= $idCl_get; ?>&chambre=<?= $idCh_get; ?>" class="btn btn-warning col-md-3 mx-3 mt-3" title="Editer la réservation du client <?= $prenom.' '.$nom; ?>">Editer</a>
        </div>

        
    </div>
</div>

<?php require_once('../Partials/Footer.php'); ?>