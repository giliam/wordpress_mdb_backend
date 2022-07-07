
<?php
require_once "parameters.php";

function __log($text, $prefixe = "")
{
    if (is_array($text)) {
        $prefixe .= "\t";
        foreach ($text as $key => $value) {
            __log($prefixe . $key);
            __log($value, $prefixe);
        }
    } else {
        file_put_contents("../logs/consigne.log", $prefixe . $text . "\n", FILE_APPEND);
    }
}

function manage_file($file)
{
    $targetFileName = basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFileName, PATHINFO_EXTENSION));
    if ($imageFileType != "mdb") {
        if (VERBOSE)
            echo "Wrong file type<br />";
        else {
            __log("Wrong file type<br />");
        }
        return false;
    }
    $targetFile = MDB_UPLOAD_FOLDER_PATH . $targetFileName;
    if (file_exists($targetFile)) {
        if (VERBOSE)
            echo "Deletes file...<br />";
        else {
            __log("Deletes file...<br />");
        }
        delete_file($targetFileName);
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    } else {
        if (VERBOSE)
            echo "Couldn't move file from " . $file["tmp_name"] . " to " .  $targetFile . "<br />";
        else {
            __log("Couldn't move file from " . $file["tmp_name"] . " to " .  $targetFile);
        }
        return false;
    }
}

function delete_file($file)
{
    if (strpos($file, "..") === false) {
        if (file_exists(MDB_UPLOAD_FOLDER_PATH . $file)) {
            if (VERBOSE)
                echo "Deleted file<br />";
            else {
                __log("Deleted file<br />");
            }
            unlink(MDB_UPLOAD_FOLDER_PATH . $file);
        } else {
            if (VERBOSE)
                echo "File couldn't be found in " . MDB_UPLOAD_FOLDER_PATH . $file;
            else {
                __log("File couldn't be found in " . MDB_UPLOAD_FOLDER_PATH . $file);
            }
        }
    } else {
        if (VERBOSE)
            echo "Found .. in filename.";
        else {
            __log("Found .. in filename.");
        }
    }
}

if (VERBOSE) {
    var_dump($_POST);
    var_dump($_GET);
    var_dump($_FILES);
} else {
    __log($_POST, "---post---");
    __log($_GET, "---get---");
    __log($_FILES, "---files---");
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
        else
            __log("YES WELL RECEIVED");
        $file = $_FILES["file"];
        if (VERBOSE) {
            var_dump($file);
            echo "<br />";
            var_dump($file["error"] == UPLOAD_ERR_OK);
            echo "<br />";
        } else {
            __log($file);
            __log($file['error']);
        }
        $moveFile = manage_file($file);
        if ($moveFile !== false) {
            $query = query_file($file["name"]);
            if (VERBOSE) {
                var_dump($query);
                echo "Query executed<br />";
            } else {
                __log("Query executed");
            }
            //delete_file($file["name"]);
            if ($query !== false) {
                header('Content-Type: application/json');
                echo trim($query);
            }
        } else {
            if (VERBOSE)
                echo "Failed at managment";
            else {
                __log("Failed at moving file");
            }
        }
    } else {
        if (VERBOSE)
            echo "NOI";
        else
            __log("Failed checking tokens");
    }
} else {
    if (VERBOSE) {
        echo "Missing paramater.";
        var_dump($_POST);
        var_dump($_GET);
        var_dump($_FILES);
    } else {
        __log("Missing parameter");
    }
}
