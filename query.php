<?php
// ini_set('memory_limit', -1);
require_once "parameters.php";

function esc_sql($s)
{
    return $s;
}

function print_table($t)
{
    echo "<pre>";
    var_dump($t);
    echo "</pre>";
}

function parse_file($tableFile)
{
    $output = array();
    if (($handle = fopen($tableFile, "r")) !== FALSE) {
        $row = 0;
        $keys = array();
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            if ($row == 0) {
                $keys = $data;
            } else {
                $output[] = array_combine($keys, $data);
            }
            $row++;
        }
        fclose($handle);
    }
    return $output;
}

function query_file($file)
{
    if (VERBOSE)
        print_r($file . "<br />");
    $dbName = MDB_UPLOAD_FOLDER_PATH . $file;

    shell_exec('../mdb-export-all.sh ' . $dbName);

    if (VERBOSE) {
        echo "Loading succeeded<br />";
    }

    $path_exports = MDB_EXPORT_FOLDER_PATH . str_replace('.mdb', '', $file) . "/";
    $users_pk = parse_file($path_exports . "tContacts.csv");

    // T_Operation contient les "opérations" faites lors des fermetures de caisses.
    $operations = parse_file($path_exports . "T_Operation.csv");


    // tAccomptes contient les "accomptes" versés par les personnes.
    $accomptes = parse_file($path_exports . "tAccomptes.csv");

    // tAccomptes contient les "accomptes" versés par les personnes.
    $allproducts = parse_file($path_exports . "T_Produit.csv");

    $products = array();
    foreach ($allproducts as $k => $product) {
        $products[] = array(
            "IdProduit" => $product["IdProduit"],
            "DesignationProduit" => $product["DesignationProduit"]
        );
    }


    // T_DetailOperation contient les détails sur les produits des opérations
    $alldetail_operations = parse_file($path_exports . "T_DetailOperation.csv");

    $detail_operations = array();
    foreach ($alldetail_operations as $k => $product) {
        $detail_operations[] = array(
            "IdOperation" => intval($product["IdOperation"]),
            "idFournisseur" => intval($product["idFournisseur"]),
            "Quantite" => strval($product["Quantite"]),
            "PrixUnitaire" => strval($product["PrixUnitaire"]),
            "Poduits_FKID" => intval($product["Poduits_FKID"]),
        );
    }

    return json_encode(array(
        "users" => $users_pk,
        "operations" => $operations,
        "accomptes" => $accomptes,
        "detail_operations" => $detail_operations,
        "products" => $products,
    ));
}

if (isset($_GET["file_mdb"])) {
    $file = $_GET["file_mdb"];
    if (file_exists(MDB_UPLOAD_FOLDER_PATH . $file)) {
        echo "Executes " . MDB_UPLOAD_FOLDER_PATH . $file . "<br />";
        query_file($file);
    } else {
        echo "Fails for " . MDB_UPLOAD_FOLDER_PATH . $file . "<br />";
    }
}
