<?php require_once('../Admin/Authentification/securite.php'); ?>
<?php require_once('../Partials/Header.php'); ?>

<?php
require_once('../Connexion.php');

if(isset($_GET['client']) && isset($_GET['chambre'])) {
    $idCl_get = (int)trim($_GET['client']);
    $idCh_get = (int)trim($_GET['chambre']);
    
    // $inpReqOrRead = 'required';
    $dateArr_rec = $dateDep_rec = $nom_rec = $prenom_rec = $tel_rec = '';
    $ad_rec = ['', '', ''];
    
    $recap_donnees = [];
    
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
        mysqli_stmt_bind_result($res, $nCl_bdd, $nCh_bdd, $dateArr_bdd, $dateDep_bdd);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);

        $dateArr_compar = trim($dateArr_bdd);
        $dateDep_compar = trim($dateDep_bdd);

        // Calcul du temps d'une journée en secondes.
        $t1jour = (24*60*60);
        // Calcul du temps de 28 jours en seconde.
        $t1mois = (28*24*60*60);

        $dateArr_min = $dateArr_max = '';
        $dateDep_min = $dateDep_max = '';

        // Affection des valeurs max et min des dates selon la date d'arrivée et le rôle.
        if($dateArr_compar < date('Y-m-d')) {
            $dateArr_min = $dateArr_max = $dateArr_compar;

            if($_SESSION['role'] == 1) {
                $dateDep_min = date('Y-m-d');
            } else {
                $dateDep_min = $dateDep_compar;
                $dateDep_max = date('Y-m-d', (strtotime($dateArr_compar) + $t1mois));
            }
        } else {
            if($_SESSION['role'] == 1) {
                $dateArr_min = date('Y-m-d');
            } else {
                $dateArr_min = $dateArr_max = $dateArr_compar;
                $dateDep_min = $dateDep_compar;
                $dateDep_max = date('Y-m-d', (strtotime($dateArr_compar) + $t1mois));
            }
        }

        // Champs concerné requis ou en simple lecture selon le rôle.
        if($_SESSION['role'] == 1) {
            $inpReqOrRead = 'required';
        } else {
            $inpReqOrRead = 'readonly';
        }
        

        // Sélection du client.
        $sql = "SELECT * FROM client WHERE numClient = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'i', $nCl_bdd);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nCl_bdd, $nom_bdd, $prenom_bdd, $tel_bdd, $adresse_bdd);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);

        $adresse_bddTab = explode(", ", f_ch_retourBddUcwords($adresse_bdd));
        foreach ($adresse_bddTab as $value) { $ad_bdd[] = $value; }
        

        if(isset($_POST['modifier'])) {
            // print_r($_POST); echo '<br><br>';
            
            // Récupération et formatage des données du formulaire soumis.
            $nom_rec = f_ch_recupFormFormatUpper($_POST['nom']);
            $prenom_rec = f_ch_recupFormFormat($_POST['prenom']);
            $tel_rec = f_ch_recupFormFormat(preg_replace('/^00+/', '0', $_POST['tel']));
            $ad_rec = $_POST['ad'];

            foreach($ad_rec as $value) { $adresse[] = f_ch_recupFormFormat($value); }
            $adresse_rec = implode(", ", $adresse);

            $nom_send = addslashes(htmlentities($nom_rec));
            $prenom_send = addslashes(htmlentities($prenom_rec));
            $tel_send = addslashes(htmlentities($tel_rec));
            $adresse_send = addslashes(htmlentities($adresse_rec));

            
            // Traiter s'il y a mise à jour du client.
            if(f_ch_retourBddUpper(stripslashes(html_entity_decode($nom_bdd))) != f_ch_retourBddUpper($nom_rec)
                || f_ch_retourBddUpper(stripslashes(html_entity_decode($prenom_bdd))) != f_ch_retourBddUpper($prenom_rec)
                || f_ch_retourBddUpper(preg_replace('/^00+/', '0', stripslashes(html_entity_decode($tel_bdd)))) != f_ch_retourBddUpper($tel_rec)
                || f_ch_retourBddUpper(stripslashes(html_entity_decode($adresse_bdd))) != f_ch_retourBddUpper($adresse_rec))
            {
                if($_SESSION['role'] == 1) {
                    $sql = "UPDATE client SET nom = ?, prenom = ?, tel = ?, adresse = ? WHERE numClient = ?";
                    $res = mysqli_prepare($connect, $sql);
                    mysqli_stmt_bind_param($res, 'ssssi', $nom_send, $prenom_send, $tel_send, $adresse_send, $nCl_bdd);
                    $exe = mysqli_stmt_execute($res);
                    mysqli_stmt_close($res);
                    $nom_bdd = $nom_send;
                    $prenom_bdd = $prenom_send;
                    $tel_bdd = $tel_send;
                    
                    $adresse_bddTab = explode(", ", f_ch_retourBddUcwords($adresse_send));
                    foreach ($adresse_bddTab as $key => $value) { $ad_bdd[$key] = $value; }

                    $recap_donnees['Mise à jour client'] = 'Oui';
                } else {
                    $recap_donnees['Mise à jour client'] = 'Vous n\'êtes pas autorisé à modifier les données du clients.<br>Plus d\'options, contacter un administrateur.';
                }
            } else {
                $recap_donnees['Mise à jour client'] = 'Non';
            }
            
            if($_SESSION['role'] != 1 && $_POST['dateArr'] != $dateArr_compar) {
                $recap_donnees['Dates saisies'] = 'Vous n\'êtes pas autorisé à modifier la date d\'arrivée.<br>Plus d\'options, contacter un administrateur.';
            } else {
                // Traîter s'il y a mise à jour des dates.
                if($_POST['dateArr'] != $dateArr_compar || $_POST['dateDep'] != $dateDep_compar) {
                    $recap_donnees['Dates saisies'] = 'Dates modifiées';
                    // Contrôle de la date d'arrivée si le séjour est en cours.
                    if($_POST['dateArr'] != $dateArr_compar && $dateArr_compar < date('Y-m-d')) {
                        $recap_donnees['Dates saisies'] = 'Date d\'arrivée ne peut pas être modifier pour un séjour en cours';
                    } else {
                        $dateArr_bdd = trim(addslashes(htmlentities($_POST['dateArr'])));
                        
                        // Contrôle de la date de départ selon le role.
                        if(trim($_POST['dateDep']) < $dateDep_min) {
                            if($_SESSION['role'] == 1) {
                                if($dateDep_min == date('Y-m-d')) {
                                    $recap_donnees['Dates saisies'] = 'Date de départ ne doit pas être inférieure à la date d\'aujourd\'hui.';
                                } else {
                                    $recap_donnees['Dates saisies'] = 'Date de départ ne doit pas être inférieure ou égale à la date d\'arrivée.';
                                }
                            } else {
                                $recap_donnees['Dates saisies'] = 'Date de départ ne doit pas être inférieure à la date de départ initialement enregistrée.';
                            }
                        } else {
                            $recap_donnees['Dates saisies'] = 'Dates renseignées valides';

                            $dateDep_bdd = trim(addslashes(htmlentities($_POST['dateDep'])));

                            // Ajout de 24h à la date d'arrivée sélectionnée par le client.
                            $dateArr_bddPlus1J = date('Y-m-d', (strtotime($dateArr_bdd) + $t1jour));
                            // Retrait de 24h à la date de départ sélectionnée par le client.
                            $dateDep_bddMoins1J = date('Y-m-d', (strtotime($dateDep_bdd) - $t1jour));
                            // echo $dateArr_bdd.' ----- '.$dateDep_bdd; echo '<br>';
                            // echo $dateArr_bddPlus1J.' ----- '.$dateDep_bddMoins1J; echo '<br>';

                            // Sélection des dates de réservations connues pour la modification de réservation choisie par le client.
                            $sql = "SELECT dateArrivee, dateDepart FROM reservation WHERE numChambre = ? && numClient != ?
                            && (dateArrivee BETWEEN ? AND ? OR dateDepart BETWEEN ? AND ?)";
                            $res = mysqli_prepare($connect, $sql);
                            mysqli_stmt_bind_param($res, 'iissss', $nCh_bdd, $nCl_bdd, $dateArr_bdd, $dateDep_bddMoins1J, $dateArr_bddPlus1J, $dateDep_bdd);
                            $exe = mysqli_stmt_execute($res);
                            mysqli_stmt_bind_result($res, $dateArr_sql, $dateDep_sql);
                            mysqli_stmt_store_result($res);
                            $nbRow_res = mysqli_stmt_num_rows($res);
                            while(mysqli_stmt_fetch($res)) {
                                $dateIndispoArr[] = '<b>'.dateFormat($dateArr_sql).'</b>';
                                $dateIndispoDep[] = '<b>'.dateFormat($dateDep_sql).'</b>';
                            }
                            mysqli_stmt_free_result($res);
                            mysqli_stmt_close($res);

                            if($exe) {
                                // Si la période est indisponible.
                                if($nbRow_res != 0) {
                                    $recap_donnees['Période'] = 'Période indisponible';
                                // Si la période est disponible.
                                } else {
                                    $recap_donnees['Période'] = 'Période disponible';
                                    if($recap_donnees['Mise à jour client'] != 'Vous n\'êtes pas autorisé à modifier les données du clients.<br>Plus d\'options, contacter un administrateur.') {
                                        // Mise à jour de la réservation.
                                        if($_SESSION['role'] == 1) {
                                            $sql = "UPDATE reservation SET dateArrivee = ?, dateDepart = ? WHERE numClient = ?";
                                            $res = mysqli_prepare($connect, $sql);
                                            mysqli_stmt_bind_param($res, 'ssi', $dateArr_bdd, $dateDep_bdd, $nCl_bdd);
                                        } else {
                                            $sql = "UPDATE reservation SET dateDepart = ? WHERE numClient = ?";
                                            $res = mysqli_prepare($connect, $sql);
                                            mysqli_stmt_bind_param($res, 'si', $dateDep_bdd, $nCl_bdd);
                                        }
                                        mysqli_stmt_execute($res);
                                        mysqli_stmt_close($res);
                                        $recap_donnees['Réservation modifiée'] = 'Validée';
                                        $upSuccess = 'Modification réussie';
                                    }
                                }
                            }
                        }
                    }
                } else { $recap_donnees['Dates saisies'] = 'Dates non modifiées'; }
            }
            ?>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Etapes</th>
                        <th>Résultats</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recap_donnees as $key => $value) { ?>
                    <tr>
                        <td><?= $key ?></td>
                        <?php if($value != 'Période indisponible') { ?>
                        <td><?= $value ?></td>
                        <?php } else { ?>
                        <td>
                            <?= $value ?><br>
                            <?php
                            echo 'Disponibilité(s) sur la période sélectionnée :<br>';
                            for($i = 0; $i < count($dateIndispoArr)-1; $i++) {
                                if($dateIndispoDep[$i] != $dateIndispoArr[$i+1]) {
                                    echo $dateIndispoDep[$i].' au '.$dateIndispoArr[++$i].'<br>';
                                }
                            }
                            echo 'A partir du '.$dateIndispoDep[count($dateIndispoDep)-1].'<br>';} ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        
        <?php
        }
    } else { header('location:accueil.php'); }
} else { header('location:accueil.php'); }
?>

<h2 class="text-center">Administration</h2>
<div class="posCenter card">
    <div class="card-header text-center"><h3>Réservation du client <?= $prenom_bdd; ?> <?= $nom_bdd; ?></h3></div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row justify-content-center">
                <div class="w-100"></div>
                <div class="text-center">
                    <h4>Chambre <?= $nCh_bdd ?></h4>
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
                            <div class="prixTotal"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-around">
                <div class="col-md-4 col-12">
                    <label for="dateArr">Date d'arrivée</label>
                    <input type="date" class="form-control text-center" id="dateArr" value="<?= $dateArr_bdd ?>" min="<?= $dateArr_min ?>" max="<?= $dateArr_max ?>" name="dateArr" <?= $inpReqOrRead ?>>
                </div>
                <div class="col-md-4 col-12">
                    <label for="dateDep">Date de départ</label>
                    <input type="date" class="form-control text-center" id="dateDep" value="<?= $dateDep_bdd ?>" name="dateDep" min="<?= $dateDep_min; ?>" max="<?= $dateDep_max; ?>" required>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md">
                    <h3 class="col">Identité client</h3>
                    <div class="w-100"></div>
                    <div class="col-12">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" value="<?= f_ch_retourBddUpper(stripslashes(html_entity_decode($nom_bdd))); ?>" <?= $inpReqOrRead ?>>
                    </div>
                    <div class="col-12">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Votre prénom" value="<?= f_ch_retourBddUcwords($prenom_bdd); ?>" <?= $inpReqOrRead ?>>
                    </div>
                    <div class="col-12">
                        <label for="tel">Téléphone</label>
                        <input type="tel" class="form-control" id="tel" name="tel" placeholder="Numéro de téléphone" value="<?= f_ch_retourBddUcwords($tel_bdd); ?>" pattern="[0-9]{8,10}" title="Veuillez inscrire 8 à 10 chiffres entre 0 et 9" <?= $inpReqOrRead ?>>
                    </div>
                </div>
                
                <hr>
                
                <div class="col-md">
                    <h3 class="col-12">Adresse client</h3>
                    <div class="w-100"></div>
                    <div class="col">
                        <label for="nRue">Numéro et rue</label>
                        <input type="text" class="form-control" id="nRue" name="ad[]" placeholder="Numéro et rue" value="<?= $ad_bdd[0]; ?>" <?= $inpReqOrRead ?>>
                    </div>
                    <div class="col-12">
                        <label for="CP">Code postal</label>
                        <input type="text" class="form-control" id="CP" name="ad[]" placeholder="Code postale" value="<?= $ad_bdd[1]; ?>" pattern="[0-9]{2,5}" title="Veuillez inscrire un nombre compris entre 2 et 5 chiffres" <?= $inpReqOrRead ?>>
                    </div>
                    <div class="col-12">
                        <label for="ville">Ville</label>
                        <input type="text" class="form-control" id="ville" name="ad[]" placeholder="Ville" value="<?= $ad_bdd[2]; ?>" <?= $inpReqOrRead ?>>
                    </div>
                </div>
            </div>
            
            <br>
            <div class="row justify-content-center">
                <a onclick="return confirm('Annuler les modifications...')" href="admin.php" class="btn btn-danger col-md-3 mx-3 mt-3">Annuler</a>
                <button onclick="return confirm('Valider les modifications...')" type="submit" class="btn btn-success col-md-3 mx-3 mt-3" name="modifier">Valider</button>
            </div>

            <?php if(isset($upSuccess)) { ?>
            <div class="text-right">
                <a href="../Admin/admin.php" class="btn btn-info col-md-3 col-4 my-1">RESERVATIONS</a>
            </div>
            <?php } ?>

        </form>
        
    </div>
</div>

<?php require_once('../Partials/Footer.php'); ?>