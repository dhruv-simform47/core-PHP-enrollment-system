<?php
// ini_set('display_errors', 0);
// error_reporting(E_ALL);
session_start();

?>
<?php require_once "./includes/header.php"; ?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Populate Student Records</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="./students.php">Students</a></li>
                <li class="breadcrumb-item active">using csv file</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>
                    Upload students csv file
                </div>
                <div class="card-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <form action="./addStudent_csv.php" method="POST" id="csvForm" enctype="multipart/form-data">

                        <input type="hidden" name="action" value="save">

                        <div class="mb-3 w-25">
                            <label for="uploadFile" class="form-label">Student Record File</label>
                            <input type="file" class="form-control" id="uploadFile" name="uploadFile" accept=".csv" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                            <a href="./students.php" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>


    <?php require_once "./includes/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#csvForm').on('submit', function(e) {
                e.preventDefault();
                const fileInput = $("#uploadFile")[0];
                console.log("debug1");
                let file = fileInput.files[0];
                 let alertBox = $('#alertBox');
                // check file is uploaded or not 
                if (!file) {
                    console.log("debug2");
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('First Upload the File.').slideDown();
                    return;
                }
                //check extension of file 
                if (file.type !== "text/csv")
                {
                    console.log("debug3");
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('Invalid file Extension!').slideDown();
                    return;
                }


                
                let submitBtn = $('#submitBtn');


                submitBtn.prop('disabled', true);
                console.log("debug4");
                alertBox.slideUp(); // Hide old alerts
                let formData=new FormData();
                formData.append("csvFile",$("#uploadFile")[0].files[0]);
                formData.append("save",true);

                //Send AJAX Request 
                $.ajax({
                    url: "file_validator.php",
                    type: "POST",
                    data: formData,
                    processData:false,
                    contentType:false,
                    dataType: "json",
                    success: function(response) {

                        if (response.status === 'success') {
                            console.log("debug6");
                            alertBox.removeClass('alert-danger').addClass('alert-success')
                                .html(response.message).slideDown();
                            $('#csvForm')[0].reset();
                            window.location.href = 'students.php';

                        } else {
                            // Show validation/backend errors
                            console.log("debug7");
                            let errorHtml = response.errors.join("<br>");
                            alertBox.removeClass('alert-success').addClass('alert-danger')
                                .html(errorHtml).slideDown();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        console.log("debug8");
                        console.log("STATUS:", status);
                        console.log("ERROR:", error);
                        console.log("RESPONSE TEXT:", xhr.responseText);
                        alertBox.removeClass('alert-success').addClass('alert-danger')
                            .html('A server error occurred. Please check the console.').slideDown();
                    },
                    complete: function() {
                        // Restore button state
                        console.log("debug5");
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                    }
                });
            });
        });
    </script>