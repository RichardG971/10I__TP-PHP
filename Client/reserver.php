<?php session_start(); ?>
<?php require_once('../Partials/Header.php'); ?>

<?php
require_once('../Connexion.php');

if(isset($_GET['id'])) {
    $idCh_get = (int)trim($_GET['id']);
    
    $inpReqOrRead = 'required';
    $dateArr_rec = $dateDep_rec = $nom_rec = $prenom_rec = $tel_rec = $adresse_rec = '';
    $ad_rec = ['', '', ''];
    
    $recap_donnees = [];
    $recap_donnees['Nouvelle réservation'] = 'Réservation non validée';

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


        // Sélection numéro de chambre, prix, et image de la chambre choisit par le client.
        $sql = "SELECT numChambre, prix, image FROM chambre WHERE numChambre = ?";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($res, 'i', $idCh_get);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nCh, $prix, $img);
        mysqli_stmt_fetch($res);
        mysqli_stmt_close($res);


        if(isset($_POST['reserver'])) {
            // print_r($_POST); echo '<br><br>';

            // Récupération et formatage des données du formulaire soumis.
            $nom_rec = f_ch_recupFormFormatUpper($_POST['nom']);
            $prenom_rec = f_ch_recupFormFormat($_POST['prenom']);
            $tel_rec = f_ch_recupFormFormat(preg_replace('/^00+/', '0', $_POST['tel']));
            $ad_rec = $_POST['ad'];
            
            foreach($ad_rec as $value) { $adresse[] = f_ch_recupFormFormat($value); }
            $adresse_rec = implode(", ", $adresse);

            // Mise en forme des données récupérées du formulaire pour l'envoi à la BDD.
            // htmlentities() - Convertit tous les caractères éligibles en entités HTML (exemple : à = &agrave;).
            // addslashes() - Ajout d'antislashs pour échapper les caractères susmentionnés dans une chaîne de caractères qui doit être évalué par PHP.
            $nom_send = addslashes(htmlentities($nom_rec));
            $prenom_send = addslashes(htmlentities($prenom_rec));
            $tel_send = addslashes(htmlentities($tel_rec));
            $adresse_send = addslashes(htmlentities($adresse_rec));
            // echo '$adresse_send : '.$adresse_send; echo '<br>';


            // Sélectionner le numéro client s'il existe.
            $sql = "SELECT numClient, nom, prenom, adresse FROM client";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_execute($res);
            mysqli_stmt_bind_result($res, $nCl_selCl, $nom_selCl, $prenom_selCl, $adresse_selCl);
            mysqli_stmt_store_result($res);
            $nbRow_res = mysqli_stmt_num_rows($res);

            // Contrôler s'il y a des clients en BDD, car 'mysqli_stmt_fetch($res)' ne retourne rien si la table 'client' est vide.
            if($nbRow_res == 0) {
                $nCl = 'Nouveau client';
                $recap_donnees['Client'] = $nCl; /*****/ $msgAccCl = 'Bienvenue ';
                $recap_donnees['Num Client'] = 'Non affecté';
            } else {
                while(mysqli_stmt_fetch($res)) {
                    // html_entity_decode() - Décode une chaîne encodée avec 'htmlentities()'.
                    // stripslashes() — Décode une chaîne encodée avec 'addcslashes()'.
                    if(f_ch_retourBddUpper(stripslashes(html_entity_decode($nom_selCl))) == f_ch_retourBddUpper($nom_rec)
                        && f_ch_retourBddUpper(stripslashes(html_entity_decode($prenom_selCl))) == f_ch_retourBddUpper($prenom_rec)
                        && f_ch_retourBddUpper(stripslashes(html_entity_decode($adresse_selCl))) == f_ch_retourBddUpper($adresse_rec))
                    {
                        $recap_donnees['Client'] = 'Client connu'; /*****/ $msgAccCl = 'Bon retour ';
                        $nCl = $nCl_selCl;
                        $recap_donnees['Num Client'] = $nCl;
                    break;
                    } else {
                        $nCl = 'Nouveau client';
                        $recap_donnees['Client'] = $nCl; /*****/ $msgAccCl = 'Bienvenue ';
                        $recap_donnees['Num Client'] = 'Non affecté';
                    }
                }
            }
            mysqli_stmt_free_result($res);
            mysqli_stmt_close($res);
            

            $recap_donnees['Dates saisies'] = 'Dates non renseignées'; /*****/ $dateInfo = 'Vous n\'avez pas renseigné de dates !';
            // Contrôle de la date d'arrivée si elle est inférieure à la date du jour actuel.
            if($_POST['dateArr'] < date('Y-m-d')) {
                $recap_donnees['Dates saisies'] = 'Date d\'arrivée ne doit pas être inférieure à la date d\'aujourd\'hui'; /*****/ $dateInfo = 'Votre date d\'arrivée ne peut pas être inférieur à la date d\'aujourd\'hui.';
            } else {
                $dateArr_rec = trim(addslashes(htmlentities($_POST['dateArr'])));
                
                // Contrôle de la date de départ si elle est inférieure ou égale à la date d'arrivée.
                if($_POST['dateDep'] <= $_POST['dateArr']) {
                    $recap_donnees['Dates saisies'] = 'Date de départ ne doit pas être inférieure ou égale à la date d\'arrivée.'; /*****/ $dateInfo = 'Votre date de départ ne doit pas être inférieur ou égale à votre date d\'arrivée.';
                } else {
                    $recap_donnees['Dates saisies'] = 'Dates choisies valides'; /*****/ $dateInfo = '';
                    $dateDep_rec = trim(addslashes(htmlentities($_POST['dateDep'])));

                    // Un client peut arriver le jour d'un départ d'un autre client et inversement,
                    // il faut donc le prendre en compte en calculant les dates pour la requête sql qui vient ensuite avec 'BETWEEN'.
                    // Calcul du temps d'une journée en seconde.
                    $t1jour = (24*60*60);
                    // Calcul du temps de 28 jours en seconde.
                    $t1mois = (28*24*60*60);
                    // Ajout de 24h à la date d'arrivée sélectionnée par le client.
                    $dateArr_recPlus1J = date('Y-m-d', (strtotime($dateArr_rec) + $t1jour));
                    // Ajout de 28 jours à la date d'arrivée sélectionnée par le client.
                    $dateArr_recPlus1M = date('Y-m-d', (strtotime($dateArr_rec) + $t1mois));
                    // Retrait de 24h à la date de départ sélectionnée par le client.
                    $dateDep_recMoins1J = date('Y-m-d', (strtotime($dateDep_rec) - $t1jour));
                    // echo $dateArr_rec.' ----- '.$dateDep_rec; echo '<br>';
                    // echo $dateArr_recPlus1J.' ----- '.$dateDep_recMoins1J; echo '<br>';

                    // Sélection des dates de réservations connues pour la chambre sélectionnée dans la période choisie par le client.
                    $sql = "SELECT dateArrivee, dateDepart FROM reservation WHERE numChambre = ?
                      AND (dateArrivee BETWEEN ? AND ? OR dateDepart BETWEEN ? AND ?) ORDER BY dateArrivee";
                    $res = mysqli_prepare($connect, $sql);
                    mysqli_stmt_bind_param($res, 'issss', $nCh, $dateArr_rec, $dateDep_recMoins1J, $dateArr_recPlus1J, $dateDep_rec);
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

                    if((!isset($_SESSION['role']) || $_SESSION['role'] != 1) && $dateDep_rec > $dateArr_recPlus1M) {
                        $recap_donnees['Dates saisies'] = 'Date de départ trop grande'; /*****/ $dateInfo = 'Votre date de départ ne peut aller au delà de 4 semaines de votre date d\'arrivée.';
                        $recap_donnees['Durée période'] = 'Durée de période sélectionnée sur le site ne doit pas exéder 4 semaines'; /*****/ $periode = 'La période que vous avez choisie est trop grande.<br>Pour plus de possibilités, contacter directement l\'établissement.';
                    } else {
                        $recap_donnees['Durée période'] = 'Durée de période sélectionnée sur le site acceptée';
                        if($exe) {
                            // Si la période est indisponible.
                            if($nbRow_res != 0) {
                                $recap_donnees['Période'] = 'Période indisponible'; /*****/ $periode = 'La période que vous avez choisie n\'est pas disponible.';
                            // Si la période est disponible.
                            } else {
                                $recap_donnees['Période'] = 'Période disponible';

                                // Si le client éxiste et que la réservation est possible, vérifier qu'il n'a pas déjà une réservation sur cette chambre.
                                if($nCl != 'Nouveau client') {
                                    $sql = "SELECT dateArrivee, dateDepart FROM reservation WHERE numChambre = ? && numClient = ?";
                                    $res = mysqli_prepare($connect, $sql);
                                    mysqli_stmt_bind_param($res, 'ii', $nCh, $nCl);
                                    mysqli_stmt_execute($res);
                                    mysqli_stmt_bind_result($res, $dateArr_selResClCh, $dateDep_selResClCh);
                                    // Pour connaitre le nombre de lignes renvoyées, utiliser :
                                    //    mysqli_stmt_store_result() --- stockage en mémoire du résultat de la requête
                                    //    mysqli_stmt_num_rows() ------- retourne le nombre de ligne d'une requête préparée
                                    //    mysqli_stmt_free_result() ---- libère le résultat obtenu par 'mysqli_stmt_store_result()'
                                    // 'mysqli_stmt_num_rows()' peut fonctionner avec 'mysqli_stmt_fetch()' si la méthode est utilisée avec 'while' : 'while(mysqli_stmt_fetch()) {}'
                                    mysqli_stmt_store_result($res);
                                    // si 'mysqli_stmt_fetch()' avant 'mysqli_stmt_store_result()', alors 'mysqli_stmt_store_result()' ne fonctionnera pas.
                                    mysqli_stmt_fetch($res);
                                    
                                    $nbRow_selResClCh = mysqli_stmt_num_rows($res);
                                    mysqli_stmt_free_result($res);
                                    mysqli_stmt_close($res);
                    
                                    // S'il y a déjà une réservation existante pour le client.
                                    if($nbRow_selResClCh != 0) {
                                        $recap_donnees['Réservation'] = 'Chambre déjà réservée'; /*****/ $reservConnue = 'Vous avez déjà une réservation connue pour cette chambre du ';
                                    // S'il n'y pas de réservation éxistante pour le client.
                                    } else { $recap_donnees['Réservation'] = 'Réservation possible'; }
                                } else {
                                    // Insérer un nouveau client.
                                    $sql = "INSERT INTO client (nom, prenom, tel, adresse) VALUES (?, ?, ?, ?)";
                                    $res = mysqli_prepare($connect, $sql);
                                    mysqli_stmt_bind_param($res, 'ssss', $nom_send, $prenom_send, $tel_send, $adresse_send);
                                    mysqli_stmt_execute($res);
                                    $lastId = mysqli_stmt_insert_id($res); // Récupérer l'id du nouveau client.
                                    mysqli_stmt_close($res);

                                    if($res) {
                                        // Sélection du numéro client du nouveau client.
                                        $nCl = $lastId;
                                        $recap_donnees['Num Client'] = $nCl;
                                        $recap_donnees['Réservation'] = 'Réservation possible';
                                    }
                                }

                                // Si un client connu n'a pas déjà de réservation pour cette chambre.
                                if($recap_donnees['Réservation'] != 'Chambre déjà réservée') {
                                    // Insertion d'une nouvelle réservation.
                                    $sql = "INSERT INTO reservation (numClient, numChambre, dateArrivee, dateDepart) VALUES (?, ?, ?, ?)";
                                    $res = mysqli_prepare($connect, $sql);
                                    mysqli_stmt_bind_param($res, 'iiss', $nCl, $nCh, $dateArr_rec, $dateDep_rec);
                                    mysqli_stmt_execute($res);
                                    mysqli_stmt_close($res);
                                    $recap_donnees['Nouvelle réservation'] = 'Réservation validée';
                                    $inpReqOrRead = 'readonly';
                                }
                            }
                        }
                    }
                }
            }
            if(isset($_SESSION['login'])) {
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
        
            <?php }
            if(!isset($_SESSION['login'])) {
                if($recap_donnees['Nouvelle réservation'] == 'Réservation validée') {
                    $formRes = 'formResFinal';
                } else {
                    $formRes = 'formRes';
                }
            }
        }
    } else { header('location:accueil.php'); }
} else { header('location:accueil.php'); }
?>

<div class="<?php if(isset($formRes)) { echo $formRes; } ?> modalBgc reserv">

    <div class="modalRes container col-md-8 bg-white rounded py-3">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h2>
                <?php
                if(isset($msgAccCl)) { echo $msgAccCl; };
                echo $prenom_rec.' '.$nom_rec ;?>
                </h2>
                <h3><?= $recap_donnees['Nouvelle réservation'] ?></h3>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-center">
                    <h3>Votre sélection</h3>
                    <div class="w-100"></div>
                    <div class="divImg col-md-6 text-center">
                        <span style="font-size: 1.5rem;"><b>CHAMBRE <?= $nCh ?></b></span>
                        <br><br><img src="../Img/<?= $img ?>" alt="<?= f_imgAlt($img) ?>" class="border border-primary rounded" style="box-shadow: 0 0 0 0.25rem #17a2b8;">
                    </div>
                        
                    <div class="col-md-6">
                        <div class="row justify-content-center" style="font-size: 1.5rem;">
                            <div class="col-md col-6 my-3">
                                <div class="row justify-content-between border rounded py-3 text-center">
                                    <div class="col-xl"><b>ARRIVEE&nbsp;:</b></div>
                                    <div class="col-xl"><?= dateFormat($dateArr_rec) ?></div>
                                </div>
                            </div>
                            <div class="w-100"></div>
                            <div class="col-md col-6 my-3">
                                <div class="row justify-content-between border rounded py-3 text-center">
                                    <div class="col-xl"><b>DEPART&nbsp;:</b></div>
                                    <div class="col-xl"><?= dateFormat($dateDep_rec) ?></div>
                                </div>
                            </div>

                            <?php if($recap_donnees['Nouvelle réservation'] == 'Réservation validée') { ?>
                            <div class="w-100"></div>
                            
                            <div class="col-md col-6 my-3">
                                <div class="row justify-content-between align-items-center border rounded py-3 text">
                                    <div class="col text-center"><b>PRIX :</b></div>
                                    <div class="col"><span class="prixTotal"></div>
                                </div>
                            </div>
                            <?php } ?>
                            
                        </div>
                    </div>
                </div>
                
                <?php if($recap_donnees['Nouvelle réservation'] == 'Réservation non validée') { ?>
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center mt-4" style="font-size: 1.2rem;">

                    <?php
                    if(isset($dateInfo) && !empty($dateInfo)) { echo '<p><b>'.$dateInfo.'</b></p>'; }
                    if(isset($periode) && !empty($periode)) { echo '<p><b>'.$periode.'</b></p>'; }
                    if(isset($recap_donnees['Période']) && $recap_donnees['Période'] == 'Période indisponible') {
                        echo '<b><u>Période(s) disponible(s) sur la période que vous avez choisie&nbsp;:</u></b><br>';
                        for($i = 0; $i < count($dateIndispoArr)-1; $i++) {
                            if($dateIndispoDep[$i] != $dateIndispoArr[$i+1]) {
                                echo $dateIndispoDep[$i].' au '.$dateIndispoArr[++$i].'<br>';
                            }
                        }
                        echo 'A partir du '.$dateIndispoDep[count($dateIndispoDep)-1].'<br>';
                    }
                    if(isset($reservConnue) && !empty($reservConnue)) { echo '<p><b>'.$reservConnue.'<br>'.dateFormat($dateArr_selResClCh).'</b> au <b>'.dateFormat($dateDep_selResClCh).'</b></p>'; }
                    ?>

                    </div>
                </div>

                <div class="text-center">
                    <a class="btnModalClose btn btn-info text-white col-md-4 col-4 mt-3" style="font-size: 1.2rem;">Modifier</a>
                </div>
                <div class="text-right">
                    <a onclick="return confirm('Voulez-vous vraiment quitter la réservation ?')" href="../Client/accueil.php" class="btn btn-warning col-md-4 col-4 mt-3" style="font-size: 1.2rem;">Retour à l'accueil</a>
                </div>
                <?php } ?>

                <?php if($recap_donnees['Nouvelle réservation'] == 'Réservation validée') { ?>
                <div class="text-right">
                    <a href="../Client/accueil.php" class="btn btn-success col-md-4 col-4 mt-3">Quitter</a>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
            
</div>

<h2 class="text-center">Booking-AFPA.com</h2>
<div class="reserv posCenter card">
    <div class="card-header text-center"><h3>Réservation de votre Séjour</h3></div>
    <div class="row justify-content-center">
        <div class="col-md-8 mt-3"><b><span class="text-danger">*</span> Tous les champs sont requis</b></div>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row justify-content-center align-items-center text-center border rounded bg-light ">
                <h3>Votre sélection</h3>
                <div class="w-100"></div>
                <div class="divImg col-md-4 col-8">
                    <span>
                        <img src="../Img/<?= $img ?>" alt="<?= f_imgAlt($img) ?>">
                        <br><img src="../Img/<?= $img ?>" class="imgHov rounded" alt="<?= f_imgAlt($img) ?>">
                    </span>
                </div>
                
                <div class="col-md-4 col-8 text-left py-3" style="font-size: 1.5rem;">
                    <div class="row justify-content-around">
                        <div class="col">
                            Chambre :
                        </div>
                        <div class="col">
                            <?= $nCh ?>
                        </div>
                    </div>
                    <div class="row justify-content-around">
                        <div class="col">
                            Prix :
                        </div>
                        <div class="col">
                            <span id="prix"><?= $prix ?></span> € / nuit
                        </div>
                    </div>
                    <div class="row justify-content-around align-items-center bg-primary text-white my-3 py-3 border rounded">
                        <div class="col">
                            Prix total :
                        </div>
                        <div class="col">
                            <b><div class="prixTotal text-center"></div></b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center my-5 text-center">
                <div class="col-md-4 col-12">
                    <label for="dateArr text-center"><span style="font-size: 1.5rem;">Date d'arrivée</span></label>
                    <input type="date" class="form-control text-center" id="dateArr" value="<?= $dateArr_rec ?>" min="<?= date('Y-m-d'); ?>" name="dateArr" <?= $inpReqOrRead ?>>
                </div>
                <div class="col-md-4 col-12">
                    <label for="dateDep text-center"><span style="font-size: 1.5rem;">Date de départ</span></label>
                    <input type="date" class="form-control text-center" id="dateDep" value="<?= $dateDep_rec ?>" name="dateDep" <?= $inpReqOrRead ?> title="Pour un séjour au delà de 28 jours, contacter directement l'établissement">
                </div>
            </div>

            <hr>
            
            <div class="row justify-content-center">
                <h3>Votre identité</h3>
                <div class="w-100"></div>
                <div class="col-md-4">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" value="<?= $nom_rec; ?>" <?= $inpReqOrRead ?>>
                </div>
                <div class="col-md-4">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Votre prénom" value="<?= $prenom_rec; ?>" <?= $inpReqOrRead ?>>
                </div>
                <div class="w-100"></div>
                <div class="col-md-4">
                    <label for="tel">Téléphone</label>
                    <input type="tel" class="form-control" id="tel" name="tel" placeholder="Numéro de téléphone" value="<?= $tel_rec; ?>" pattern="[0-9]{8,10}" title="Veuillez inscrire 8 à 10 chiffres entre 0 et 9" <?= $inpReqOrRead ?>>
                </div>
            </div>
            
            <hr>
            
            <div class="row justify-content-center">
                <h3>Votre adresse</h3>
                <div class="w-100"></div>
                <div class="col-md-8">
                    <label for="nRue">Numéro et rue</label>
                    <input type="text" class="form-control" id="nRue" name="ad[]" placeholder="Numéro et rue" value="<?= f_ch_recupFormFormat($ad_rec[0]); ?>" <?= $inpReqOrRead ?>>
                </div>
                <div class="w-100"></div>
                <div class="col-md-3 col-5">
                    <label for="CP">Code postal</label>
                    <input type="text" class="form-control" id="CP" name="ad[]" placeholder="Code postale" value="<?= f_ch_recupFormFormat($ad_rec[1]); ?>" pattern="[0-9]{2,5}" title="Veuillez inscrire un nombre compris entre 2 et 5 chiffres" <?= $inpReqOrRead ?>>
                </div>
                <div class="col-md-5 col">
                    <label for="ville">Ville</label>
                    <input type="text" class="form-control" id="ville" name="ad[]" placeholder="Ville" value="<?= f_ch_recupFormFormat($ad_rec[2]); ?>" <?= $inpReqOrRead ?>>
                </div>
            </div>

            <br>
            <div class="row justify-content-center">
                
                <?php if($recap_donnees['Nouvelle réservation'] != 'Réservation validée') { ?>
                <a href="chambre_detail.php?id=<?= $nCh ?>" class="btn btn-info col-md-3 mx-3 mt-3">Précédent</a>
                <button type="submit" class="btn btn-success col-md-3 mx-3 mt-3" name="reserver">Valider</button>
                <?php } ?>

            </div>

        </form>

        <div class="text-right">
            <a onclick="return confirm('Quitter la réservation...')" href="accueil.php" class="btn btn-warning col-md-2 col-4 mt-3">Accueil</a>
            
            <?php if(isset($_SESSION['login'])) { ?>
            <a href="../Admin/admin.php" class="btn btn-success col-md-2 col-4 mt-3">RESERVATIONS</a>
            <?php } ?>

        </div>

    </div>
</div>

<?php require_once('../Partials/Footer.php'); ?>