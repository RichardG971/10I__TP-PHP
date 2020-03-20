<?php require_once('../Admin/Authentification/securite.php'); ?>

<?php
require_once('../Connexion.php');

if($_SESSION['role'] == 1) {
    $login = '';

    if($connect) {
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


        $sql = "SELECT login FROM utilisateurs";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nLogAll);
        while(mysqli_stmt_fetch($res)) {
            $nLogAllCompar[] = $nLogAll;
        }
        mysqli_stmt_close($res);

        $sql = "SELECT DISTINCT role FROM utilisateurs";
        $res = mysqli_prepare($connect, $sql);
        mysqli_stmt_execute($res);
        mysqli_stmt_bind_result($res, $nRole);
        while(mysqli_stmt_fetch($res)) {
            (int)$nRole === 1 ? $statutRole[] = 'Administrateur' : $statutRole[] = 'Réceptionniste';
            $roleAll[] = $nRole;
        }
        mysqli_stmt_close($res);

        
        if(isset($_POST['ajouter'])) {
            foreach($_POST as $key => $val) {
                if($key != 'ajouter' && empty($val)) {
                    $reponse =
                        '<div class="alert alert-danger text-center">
                            <strong>Attention !</strong> Tous les champs sont requis !
                        </div>';
                    break;
                }
            }
            if(!isset($reponse)) {
                $login = f_ch_recupFormFormat($_POST['login']);

                foreach($nLogAllCompar as $value) {
                    if(f_ch_retourBddUcwords($value) == $login) {
                        $reponse = 
                            '<div class="alert alert-danger text-center">
                                <strong>Attention !</strong> Ce login éxiste déjà,<br>veuillez en choisir un autre.
                            </div>';

                        break;
                    }
                }

                if(!isset($reponse)) {
                    if($_POST['pwd'] !== $_POST['pwd2']) {
                        $reponse = 
                            '<div class="alert alert-danger text-center">
                                <strong>Attention !</strong> Les mots de passe ne sont pas identiques !
                            </div>';
                    } else {
                        $login = htmlspecialchars(addslashes($login));
                        $pwd = md5(trim(htmlspecialchars(addslashes($_POST['pwd']))));
                        $role = (int)trim(htmlspecialchars(addslashes($_POST['role'])));
        
                        $sql = "INSERT INTO utilisateurs(login, pass, role) VALUES (?, ?, ?)";
                        $res = mysqli_prepare($connect, $sql);
                        mysqli_stmt_bind_param($res, 'ssi', $login, $pwd, $role);
                        mysqli_stmt_execute($res);
                        $lastId = mysqli_stmt_insert_id($res);
                        mysqli_stmt_close($res);

                        (int)$role === 1 ? $statut = 'Administrateur': $statut = 'Réceptionniste';
                        
                        $reponse =
                            '<div class="alert alert-success text-center">
                                <strong>Nouvel utilisateur créé avec succès !</strong><br>
                                Id : '.$lastId.'<br>
                                Role : '.$statut.'<br>
                                Login : '.f_ch_retourBddUcwords($login).'
                            </div>'
                        ;
                    }
                }
            }
        }
?>

<?php require_once('../Partials/Header.php'); ?>

<div class="posCenter col-md-6">
    <div class="card">
        <h3 class="card-header text-center">Ajouter un utilisateur</h3>
        <div class="card-body">
            <form action="" method="post">
                <div class="form-group">
                    <label for="role" class="text-center w-100">ROLE</label>
                    <select class="form-control" id="role" name="role">
                        <option value="<?= $roleAll[1] ?>" hidden><?= $statutRole[1] ?></option>

                        <?php foreach($roleAll as $key => $row) { ?>
                        <option value="<?= $row ?>"><?= $statutRole[$key] ?></option>
                        <?php } ?>
                        
                    </select>
                </div>
                <div class="form-group">
                    <label for="login" class="text-center w-100">LOGIN</label>
                    <input type="text" class="form-control text-center" placeholder="Entrer votre login" id="login" name="login" value="<?= $login ?>" pattern="[A-Za-z][A-Za-z0-9]{3,9}" title="4 à 10 caractères de 'a' à 'Z' commençant par une lettre, chiffres ensuite si vous le souhaiter" required>
                </div>
                <div class="form-group">
                    <label for="pwd" class="text-center w-100">MOT DE PASSE</label>
                    <input type="password" class="form-control text-center" placeholder="Entrer votre mot de passe" id="pwd" name="pwd" required>
                </div>
                <div class="form-group">
                    <label for="pwd2" class="text-center w-100">CONFIRMER LE MOT DE PASSE</label>
                    <input type="password" class="form-control text-center" placeholder="Entrer votre mot de passe" id="pwd2" name="pwd2" >
                </div>

                <button type="submit" class="btn btn btn-primary btn-block" name="ajouter">Ajouter</button>
            </form>
        </div>
    </div>
    <?php if(isset($reponse)) { echo $reponse; } ?>
</div>

<?php
        require_once('../Partials/Footer.php');
    } else { echo "<script>alert('Connexion perdue');</script>"; }
} else {
    header('location:../Admin/index.php');
}