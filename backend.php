<?php
require_once "parameters.php";
define("VERBOSE", false);

function manage_file($file)
{
    $targetFileName = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFileName, PATHINFO_EXTENSION));
    if ($imageFileType != "mdb") {
        if (VERBOSE)
            echo "Wrong file type<br />";
        return false;
    }
    $targetFile = MDB_UPLOAD_FOLDER_PATH . $targetFileName;
    if (file_exists($targetFile)) {
        if (VERBOSE)
            echo "Deletes file...<br />";
        delete_file($targetFileName);
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    } else {
        if (VERBOSE)
            echo "Couldn't move file from " . $file["tmp_name"] . " to " .  $targetFile . "<br />";
        return false;
    }
}

function delete_file($file)
{
    if (strpos($file, "..") === false) {
        if (file_exists(MDB_UPLOAD_FOLDER_PATH . $file)) {
            if (VERBOSE)
                echo "Deleted file<br />";
            unlink(MDB_UPLOAD_FOLDER_PATH . $file);
        } else {
            if (VERBOSE)
                echo "File couldn't be found in " . MDB_UPLOAD_FOLDER_PATH . $file;
        }
    } else {
        if (VERBOSE)
            echo "Found .. in filename.";
    }
}

if (isset($_POST["token"]) && isset($_POST["date"]) && isset($_POST["hour"])) {
    require_once "query.php";

    $token = $_POST["token"];
    $date = $_POST["date"];
    $hour = $_POST["hour"];
    require_once "key.php";
    $check_token = hash("sha256", $date . $secret_key . $hour);
    unset($secret_key);
    if ($token == $check_token) {
        if (VERBOSE)
            echo "YES WELL RECEIVED<br />";
        $file = $_FILES["file"];
        if (VERBOSE) {
            var_dump($file);
            echo "<br />";
            var_dump($file["error"] == UPLOAD_ERR_OK);
            echo "<br />";
        }
        $moveFile = manage_file($file);
        if ($moveFile !== false) {
            $query = query_file($file["name"]);
            if (VERBOSE) {
                var_dump($query);
                echo "Query executed<br />";
            }
            delete_file($file["name"]);
            if ($query !== false) {
                header('Content-Type: application/json');
                echo $query;
            }
        } else {
            if (VERBOSE)
                echo "Failed at managment";
        }
    } else {
        if (VERBOSE)
            echo "NOI";
    }
}
