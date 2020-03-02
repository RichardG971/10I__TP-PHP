<?php
require_once('../Connexion.php');

if($connect) {
    function f_ch_retourBdd($str) {
        $str = stripslashes(html_entity_decode($str));
        return $str;
    }
    
    // Pour ajouter l''alt' aux images sans écrire leur extension avec 'substr($string, $start, $lenght)' :
    //   $string = la variable contenant le nom de l'image,
    //   $start = '0' car on prends le nom complet,
    //   $length = 'strrpos($img, '.')' permet de récupérer la position du dernier '.' s'il y en a plusieurs et donc de donner cette longueur à la chaîne.
    function f_imgAlt($img) {
        return substr($img, 0, strrpos($img, '.'));
    }

    
    $sql = "SELECT image, numChambre, description, prix FROM chambre";
    // Préparation de la requête
    $res = mysqli_prepare($connect, $sql);

    // Exécution de la requête
    mysqli_stmt_execute($res);
} else { echo "<script>alert('Connexion perdue')</script>"; }
?>

<section class="accueil">
    
    <?php
    if($res) {
        // Liaison de données
        mysqli_stmt_bind_result($res, $img, $nCh, $descr, $prix);
        mysqli_stmt_store_result($res);
        
        if(mysqli_stmt_num_rows($res) == 0) {
        ?>
        
        <div class="posCenter text-center bg-warning rounded py-3" style="font-size: 1.2rem;"><b>Aucun résultat...<br>Prendre contact avec l'établissement</b></div>
        
        <?php
        } else {
            // Récupération et affichage
            while(mysqli_stmt_fetch($res)) {
        ?>

    <div class="row align-items-center border rounded my-3">
        <div class="divImg col-sm-4 text-center py-1">
            <span><img src="../Img/<?= $img ?>" class="rounded" alt="<?= f_imgAlt($img) ?>">
            <br><img src="../Img/<?= $img ?>" class="imgHov rounded" alt="<?= f_imgAlt($img) ?>"></span>
        </div>
        <div class="col-sm-8">
            <div class="row align-items-center">
                <div class="col-lg-9 col-md-8 py-3 bg-light rounded">
                    <b>Chambre <?= $nCh ?></b>
                    <br>
                    <b>Description :</b>
                    <br><?= strlen(f_ch_retourBdd($descr)) > 200 ? substr(f_ch_retourBdd($descr), 0, 200)." ..." : f_ch_retourBdd($descr) ?>
                </div>
                <div class="col-lg-3 col-md-4 text-center py-3">
                    <div>
                        <b>Prix : <?= $prix ?> €</b>
                    </div>    
                    <div>
                        <a href="chambre_detail.php?id=<?= $nCh ?>" class="btn btn-primary text-white mt-3" title="Détails de la Chambre <?= $nCh ?>"><i class="fas fa-info"> Détail</i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
        }}
        mysqli_stmt_free_result($res);
        mysqli_stmt_close($res);
    }
    ?>

</section>