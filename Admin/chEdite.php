<?php require_once('../Admin/Authentification/securite.php'); ?>

<?php
require_once('../Connexion.php');

if($_SESSION['role'] == 1) {
    if(isset($_GET['chambre'])) {
        $idSel = (int)htmlentities(trim($_GET['chambre']));
        
        if($connect) {
            function f_ch_retourBdd($str) {
                $str = stripslashes(html_entity_decode($str));
                return $str;
            }

            // Pour ajouter l''alt' aux images sans écrire leur extension avec 'substr($string, $start, $lenght)' :
            //   $string = la variable contenant le nom de l'image,
            //   $start = '0' car on prends le nom complet,
            //   $length = 'strrpos($img, '.')' permet de récupérer la position du dernier '.' (celui avant l'extension)) s'il y en a plusieurs et donc de donner cette longueur à la chaîne.
            function f_imgAlt($img) {
                return substr($img, 0, strrpos($img, '.'));
            }


            $sqlSel = "SELECT * FROM chambre WHERE numChambre = ?";
            $resSel = mysqli_prepare($connect, $sqlSel);
            mysqli_stmt_bind_param($resSel, 'i', $idSel);
            mysqli_stmt_execute($resSel);

            if($resSel) {
                mysqli_stmt_bind_result($resSel, $nCh, $prix, $nLits, $nPers, $conf, $imgSel, $descr);
                mysqli_stmt_fetch($resSel);
            }
            mysqli_stmt_close($resSel);
        } else { header('location:../Admin/chambre.php'); }
    }

    if(isset($_POST['valider'])
        && !empty($_POST['prix'])
        && !empty($_POST['conf']))
    {
        // print_r($_POST);
        
        $prix = (int)$_POST['prix'];
        $nLits = (int)$_POST['nLits'];
        $nPers = (int)$_POST['nPers'];
        $conf = trim(addslashes(htmlentities($_POST['conf'])));
        $img = $_FILES['image']['name'];
        $descr = trim(addslashes(htmlentities($_POST['descr'])));
        
        $destination = '../Img/';
        move_uploaded_file($_FILES['image']['tmp_name'],
        $destination.$_FILES['image']['name']);
        // print_r($_FILES);

        if($img == "") {
            $sql = "UPDATE chambre SET prix = ?, nbLits = ?, nbPers = ?, confort = ?, description = ? WHERE numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'iiissi', $prix, $nLits, $nPers, $conf, $descr, $nCh);
        } else {
            $sql = "UPDATE chambre SET prix = ?, nbLits = ?, nbPers = ?, confort = ?, image = ?, description = ? WHERE numChambre = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'iiisssi', $prix, $nLits, $nPers, $conf, $img, $descr, $nCh);
        }
        
        $exe = mysqli_stmt_execute($res);

        if($exe) {
            if(($img != '') && ($img != $imgSel)) {
                unlink('../Img/'.$imgSel);
            }
            header('location:../Admin/chambre.php');
            $upSuccess = '<h2 class="text-center">Modification réussie</h2>';
        } else { echo '<script>alert("Echec de la modification...");</script>'; }
        /* print_r('$imgSel '.$imgSel); echo '<br>';
        print_r('$img '.$img); */
        mysqli_stmt_close($res);
    }
?>

<?php require_once('../Partials/Header.php'); ?>

<h2 class="text-center">Administration</h2>

<?php if(isset($upSuccess)) { echo $upSuccess; ?>
<div class="input-group my-1 justify-content-end" style="margin-bottom: 0 !important;">
    <div>
        <a href="./index.php?action=list_chambre" class="btn btn-success">CHAMBRES</a>
    </div>
</div>
<?php } ?>

<div class="chEdit card posCenter">
    <div class="card-header text-center"><h4>Editer la chambre <?= $nCh ?></h4></div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <label for="nLits">Nombre de lits :</label>
                    <input type="text" name="nLits" id="nLits" class="form-control text-center" value="<?= $nLits ?>" min="1" pattern="[1-8]" title="chiffre de 1 à 4" required>
                </div>
                <div class="col-md-4">
                    <label for="nPers">Nombre de personnes :</label>
                    <input type="text" name="nPers" id="nPers" class="form-control text-center" value="<?= $nPers ?>" min="1" pattern="[1-9]|1[0-5]" title="nombre de 1 à 15" required>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="form-group text-center col-md-8">
                    <label for="image">Image :</label>
                    <input type="file" name="image" class="form-control-file border rounded">
                    <div class="divImg pl-3">
                        <span>
                            <img src="../Img/<?= $imgSel ?>" alt="<?= f_imgAlt($imgSel) ?>" class="my-3">
                            <br><img src="../Img/<?= $imgSel ?>" class="imgHov rounded" alt="<?= f_imgAlt($imgSel) ?>">
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="conf">Confort :</label>
                <textarea name="conf" id="conf" class="form-control" rows="2" required><?= f_ch_retourBdd($conf) ?></textarea>
            </div>

            <div class="form-group">
                <label for="descr">Description :</label>
                <textarea name="descr" id="descr" class="form-control" rows="4" required><?= f_ch_retourBdd($descr) ?></textarea>
            </div>

            <div class="row justify-content-center">
                <div class="form-group col-md-3 col-6">
                    <label for="prix">Prix :</label>
                    <input type="text" name="prix" id="prix" class="form-control text-center" value="<?= $prix ?>" pattern="[^0a-zA-Z][0-9]{1,3}" title="Nombre de 2 à 4 chiffres ne commençant pas par 0" required>
                    <!-- Pour le pattern '[^0][0-9]{1,3}', on demande 4 chiffres maximum. '[^0a-zA-Z]' compte pour un chiffre c'est pour cela que '[0-9]{1,3}' on ne demande que 3 chiffres maximum pour avoir un total de 4. -->
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" name="valider">Valider</button>

        </form>

        <div class="text-right">
            <a onclick="return confirm('Annuler les modifications ?')" href="../Admin/chambre.php" class="btn btn-warning col-md-3 col-4 my-1">Annuler</a>

            <?php if(isset($upSuccess)) { ?>
            <a href="../Admin/chambre.php" class="btn btn-success col-md-3 col-4 my-1">CHAMBRES</a>
            <?php } ?>

        </div>

    </div>
</div>

<?php
require_once('../Partials/Footer.php');
} else {
    header('location:../Admin/index.php');
}
