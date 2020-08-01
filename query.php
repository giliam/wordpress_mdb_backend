<?php
require_once "parameters.php";

function esc_sql($s)
{
    return $s;
}

function query_file($file)
{
    if (VERBOSE)
        print_r($file);
    $dbName = MDB_UPLOAD_FOLDER_PATH . $file;

    try {
        $driver = 'MDBTools';
        $dbh = new  PDO("odbc:Driver=" . $driver . ";DBQ=" . $dbName . ";");
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e1) {
        if (VERBOSE) {
            echo "Error with MDB <br />";
            print_r($e1->getMessage());
        }

        try {
            $driver = "Microsoft Access Driver (*.mdb)";
            $dbh = new  PDO("odbc:Driver=" . $driver . ";DBQ=" . $dbName . ";");
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e2) {
            print_r('ExceptionPDOCreation -> ' . $e2->getMessage());
            return false;
        }
    }
    if (VERBOSE) {
        echo "Loading succeeded<br />";
    }

    try {
        $sql = "SELECT * FROM tContacts";  // The rules are the same as above
        $sth = $dbh->prepare($sql);
        $sth->execute();
    } catch (PDOException $e) {
        print_r('ExceptionContactsRetrieval -> ' . $e->getMessage());
        return false;
    }

    $users_pk = array();

    while ($flg = $sth->fetch(PDO::FETCH_ASSOC)) {
        $users_pk[] = $flg;
    }

    // T_Operation contient les "opérations" faites lors des fermetures de caisses.

    try {
        $sql = "SELECT * FROM T_Operation";  // The rules are the same as above
        $sth = $dbh->prepare($sql);
        $sth->execute();
    } catch (Exception $e) {
        print_r('ExceptionT_Operation -> ' . $e->getMessage());
        return false;
    }
    $operations = array();

    while ($flg = $sth->fetch(PDO::FETCH_ASSOC)) {
        $operations[] = $flg;
    }

    // tAccomptes contient les "accomptes" versés par les personnes.

    try {
        $sql = "SELECT * FROM tAccomptes";  // The rules are the same as above
        $sth = $dbh->prepare($sql);
        $sth->execute();
    } catch (Exception $e) {
        print_r('ExceptiontAccomptes -> ' . $e->getMessage());
        return false;
    }
    $accomptes = array();

    while ($flg = $sth->fetch(PDO::FETCH_ASSOC)) {
        $accomptes[] = $flg;
    }

    // T_DetailOperation contient les détails sur les produits des opérations

    try {
        $sql = "SELECT * FROM T_DetailOperation";  // The rules are the same as above
        $sth = $dbh->prepare($sql);
        $sth->execute();
    } catch (Exception $e) {
        print_r('ExceptionT_DetailOperation -> ' . $e->getMessage());
        return false;
    }
    $detail_operations = array();

    while ($flg = $sth->fetch(PDO::FETCH_ASSOC)) {
        $detail_operations[] = $flg;
    }

    return json_encode(array(
        "users" => $users_pk,
        "operations" => $operations,
        "accomptes" => $accomptes,
        "detail_operations" => $detail_operations,
    ));
}

if (isset($_GET["file_mdb"])) {
    $file = $_GET["file_mdb"];
    if (file_exists("uploads/" . $file)) {
        query_file($file);
    }
}
