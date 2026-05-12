<?php
session_start();
require_once "../uuid_generator.php";
require_once "../db.php";
require_once "../auth/mail.php";

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
    header("content-type: application/json");
    $errors = [];
    $file_name = $_FILES['csvFile']['name'] ?? '';
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $tmp_file = $_FILES['csvFile']['tmp_name'] ?? '';
    $target = "../assets/files/" . $file_name;

    $sourcefile = false;

    try {
        if (isset($_FILES['csvFile']['error']) && $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("PHP Upload Error.");
        }

        if ($extension == "csv") {
            if (move_uploaded_file($tmp_file, $target)) {
                $sourcefile = @fopen($target, "r");

                if ($sourcefile != false) {
                    fgetcsv($sourcefile); 

                    $row_num = 1;
                    $emails = [];
                    $insert_array = []; 

                    while (($line = fgetcsv($sourcefile, 0, ',', '"', "\\")) != false) {
                        $name = trim($line[0] ?? '');
                        $email = trim($line[1] ?? '');

                        // Validation
                        if (empty($name)) $errors[] = "Row $row_num: Name is empty.";
                        if (empty($email)) $errors[] = "Row $row_num: Email is empty.";
                        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Row $row_num: Invalid email ($email).";

                        if (count($errors) > 0) break; 

                        $uuid = generateUUIDv4();
                        $psw = substr($name, 0, 4) . substr($email, 0, 4);
                        $password_hash = password_hash($psw, PASSWORD_DEFAULT);

                        // 1. Add to the Bulk Array (Array of Arrays)
                        $insert_array[] = [
                            'uuid' => $uuid,
                            'user_name' => $name,
                            'email' => $email,
                            'password_hash' => $password_hash,
                            'is_verified' => 1
                        ];

                        $emails[] = $email;
                        $row_num++;
                    }

                    if (count($errors) > 0) {
                        echo json_encode(['status' => 'error', 'errors' => $errors]);
                        exit();
                    }

                    // 2. CONSTRUCT BULK INSERT QUERY
                    if (!empty($insert_array)) {
                        $columns = ['uuid', 'user_name', 'email', 'password_hash', 'is_verified'];
                        $col_string = implode(',', $columns);
                        
                        // Create placeholders: (?,?,?,?,?), (?,?,?,?,?) ...
                        $row_placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                        $all_placeholders = implode(',', array_fill(0, count($insert_array), $row_placeholders));

                        $sql = "INSERT INTO users ($col_string) VALUES $all_placeholders";
                        $stmt = $pdo->prepare($sql);

                        // 3. FLATTEN THE ARRAY FOR PDO
                        // PDO execute needs a flat array [val1, val2, val3...]
                        $flat_values = [];
                        foreach ($insert_array as $row) {
                            foreach ($row as $value) {
                                $flat_values[] = $value;
                            }
                        }

                        $stmt->execute($flat_values);
                    }

                    // Email notification
                    $mailer = new Emailnotification();
                    $mailer->compose("ranadhruv842@gmail.com", "Enrollment Credentials", "Hello...", "", $emails);

                    echo json_encode(['status' => 'success', 'message' => 'Students added successfully!']);
                } else {
                    throw new fileOpenException($file_name);
                }
            }
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'errors' => [$e->getMessage()]]);
    } finally {
        if ($sourcefile) fclose($sourcefile);
    }
}