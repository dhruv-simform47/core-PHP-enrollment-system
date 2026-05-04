<?php
// ini_set('display_errors', 0);
// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// 1. MUST start the session to use $_SESSION
session_start();

require_once "../db.php";

class fileOpenException extends Exception
{
    public $ErrorFile;
    public function __construct($fileName)
    {
        $this->ErrorFile = $fileName;
        parent::__construct("File Opening Error");
    }
    public function getCustomMessage()
    {
        return "File :" . $this->getFile() . "<br> Line Number :" . $this->getLine() . " <br> Error Opening :-" . $this->ErrorFile . "<br>Sorry!";
    }
}

class FileExtensionError extends Exception
{
    public function getCustomMessage()
    {
        return "<br> Sorry! You have uploaded the wrong file type.";
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['save']))) {
    //set response type
    header("content-type: application/json");
    $errors = [];

    // Safely grab file info (if no file was uploaded, this prevents a crash)
    $file_name = $_FILES['csvFile']['name'] ?? '';
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $tmp_file = $_FILES['csvFile']['tmp_name'] ?? '';
    $target = "../assets/files/" . $file_name;

    // Initialize variables as false
    $sourcefile = false;
    // $validfile = false;
    // $errorlog = false;

    // Reset session flags for a fresh start
    $_SESSION['errors'] = [];
    // $_SESSION["showContent"] = false;

    try {
        // Check for built-in PHP upload errors (like exceeding the 2MB size limit)
        if (isset($_FILES['csvFile']['error']) && $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("PHP Upload Error Code: " . $_FILES['csvFile']['error'] . " (Check file size limits or if a file was actually selected).");
        }

        if ($extension == "csv") {
            // Attempt to move the file
            if (move_uploaded_file($tmp_file, $target)) {
                $sourcefile = @fopen($target, "r");

                if ($sourcefile != false) {

                    $header = fgetcsv($sourcefile,0,',','"',"\\");
                    //check all columns available or not ?
                    // uuid, user_name, email, password_hash, is_verified


                    $row_num = 1;
                    $emails=[];
                    while (($line = fgetcsv($sourcefile,0,',','"',"\\")) != false) {
                        include_once "../uuid_generator.php";
                        $uuid = generateUUIDv4();
                        $name = $line[0];
                        $email = $line[1];
                        $psw = substr($name, 0, 4);
                        $psw .= substr($email, 0, 4);
                        $password_hash = password_hash($psw, PASSWORD_DEFAULT);

                        $isError = false;
                        $row_data = [];
                        
                        if (!empty($name)) {
                            $row_data[] = $name;
                        } else {
                            $isError = true;
                            $row_data[] = "undefined";
                            $errors[] = "Name is empty on: row" . $row_num;
                        }

                        if (!empty($email)) {
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $isError = true;
                                $errors[] = "Email is not-valid". $email;
                            }
                            $row_data[] = $email;
                        } else {
                            $isError = true;
                            $row_data[] = "undefined";
                            $errors[] = "Email is empty on: row" . $row_num;;
                        }

                        // Write to db
                        // if error found then return from here json 
                        if ($isError) {
                            echo json_encode(['status' => 'error', 'errors' => $errors]);
                            exit();
                        }
                        $emails[]=$email;
                        $stmt = $pdo->prepare("
                                INSERT INTO users(uuid, user_name, email, password_hash, is_verified) 
                                VALUES (:uuid, :user_name, :email, :password_hash, :is_verified)
                                ");

                        $stmt->execute([
                            ':uuid' => $uuid,
                            ':user_name' => $name,
                            ':email' => $email,
                            ':password_hash' => $password_hash,
                            ':is_verified' => true
                        ]);
                    }

                    require_once "../auth/mail.php";

                    $mailer = new Emailnotification();
                    $subject = "Enrollment Account credentials";
                    

                    $message = "Hello Student you can now access student System with your crednetial as follow:\n Password format: \n 1)<b> First 4 inital of your name and 4 initial of email </b> \n  <u><i>You have to login into the system and chnage the password for Future Use! </i></u>";

                    $mailer->compose("ranadhruv842@gmail.com", $subject, $message,"",$emails);

                    echo json_encode(['status' => 'success', 'message' => 'Students added successfully!']);
                    exit();
                   
                } else {
                    throw new fileOpenException($file_name);
                }
            } else {
                throw new Exception("Server Permission Error: Could not save the uploaded file to the 'upload/' directory.");
            }
        } else {
            throw new FileExtensionError();
        }
    }catch (FileExtensionError $f) {
    echo json_encode([
        'status' => 'error',
        'errors' => [$f->getCustomMessage()]
    ]);
    exit();
}
catch (fileOpenException $e) {
    echo json_encode([
        'status' => 'error',
        'errors' => [$e->getCustomMessage()]
    ]);
    exit();
}
catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'errors' => [$e->getMessage()]
    ]);
    exit();
    } finally {
        // ALWAYS close resources safely
        if ($sourcefile) fclose($sourcefile);
        // if ($validfile) fclose($validfile);
        // if ($errorlog) fclose($errorlog);
    }

    // --- THE FIX ---
    // 1. Force PHP to lock in the Session Data right now
    session_write_close();
    
}
