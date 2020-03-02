<?php
$erreur = "";
if(isset($_POST['soumettre'])) {

    if(!empty($_POST['login']) && !empty($_POST['pwd'])) {
        $login = trim(htmlspecialchars($_POST['login']));
        $pass = md5(trim(htmlspecialchars($_POST['pwd'])));

        require_once('../Connexion.php');
    
        if($connect) {
            $sql = "SELECT * FROM utilisateurs WHERE login = ? AND pass = ?";
            $res = mysqli_prepare($connect, $sql);
            mysqli_stmt_bind_param($res, 'ss', $login, $pass);
            mysqli_stmt_execute($res);
            mysqli_stmt_bind_result($res, $id, $login, $pass, $role);
            mysqli_stmt_store_result($res);
            if($res) {
                if(mysqli_stmt_num_rows($res) != 0) {
                    mysqli_stmt_fetch($res);
                    session_start();
                    $_SESSION['role'] = $role;
                    $_SESSION['login'] = $login;
                    header('location:../Admin/admin.php'); // Pourquoi revenir au dossier parent ? Ajax n'est pas activé si la casse de l'url n'est pas respectée.
                } else {
                    $erreur =
                        '<div class="alert alert-danger text-center">
                            <strong>Attention !</strong> Le login ou le mot de passe est incorrect !
                        </div>';
                }
            }
            mysqli_stmt_free_result($res);
            mysqli_stmt_close($res);
        } else { echo "<script>alert('Connexion perdue')</script>"; }
    } else {
        $erreur = '
            <div class="alert alert-danger text-center">
                <strong>Le login ou le mot de passe est vide!</strong> 
            </div>';
    }
}
?>

<?php require_once('../Partials/Header.php'); ?>

<div class="posCenter col-md-6">
    <div class="card">
        <h3 class="card-header text-center">Page d'authentification</h3>
        <div class="card-body">
            <form action="" method="post">
                <div class="form-group">
                    <label for="login" class="text-center w-100">LOGIN</label>
                    <input type="text" class="form-control text-center" placeholder="Entrer votre login" id="login" name="login">
                </div>
                <div class="form-group">
                    <label for="pwd" class="text-center w-100">MOT DE PASSE</label>
                    <input type="password" class="form-control text-center" placeholder="Entrer votre mot de passe" id="pwd" name="pwd">
                </div>

                <button type="submit" class="btn btn btn-primary btn-block" name="soumettre">Connexion</button>
            </form>
        </div>
    </div>
    <?= $erreur ?>
</div>

<?php require_once('../Partials/Footer.php'); ?>