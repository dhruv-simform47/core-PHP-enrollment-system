<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Admins</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-shield me-1"></i> Admins List
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Admin</button>
                </div>
                <div class="card-body">
                    <table id="adminTable" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables fills this using ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Add/Edit -->
    <div class="modal fade" id="mainModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>

                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="user_name" id="user_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>

                    <div class="password-group">
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password">
                        </div>
                    </div>

                    <div class="mb-3 form-check edit-only">
                        <input type="checkbox" class="form-check-input" name="is_verified" id="is_verified" value="1">
                        <label class="form-check-label">Verified Account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        // 1. Initialize DataTable with Layout fix
        const adminTable = $('#adminTable').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            ajax: {
                url: '../admin_api/admins_api.php',
                type: 'POST',
                data: {
                    action: 'list'
                }
            },
            columns: [{
                    data: 'user_name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'is_verified',
                    render: function(data) {
                        return data == 1 ?
                            '<span class="badge bg-success">Verified</span>' :
                            '<span class="badge bg-warning">Pending</span>';
                    }
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        if (!data) return "N/A";
                        let date = new Date(data);
                        return date.toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: 'uuid',
                    render: function(data, type, row) {
                        return `
                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", ${JSON.stringify(row)})'>Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRecord('${data}')">Delete</button>
                `;
                    }
                }
            ],
            language: {
                // This fixes the squashed look of the search/length labels
                search: "_INPUT_",
                searchPlaceholder: "Search admins...",
                lengthMenu: "Show _MENU_ entries"
            }
        });
        // 2. Open Modal Logic
        function openModal(action, data = null) {
            $('#mainForm')[0].reset();
            $('#alertBox').hide();
            $('#formAction').val(action);
            $('#submitBtn').prop('disabled', false).text('Save changes');

            if (action === 'edit' && data) {
                $('#modalTitle').text('Edit Admin');
                $('.password-group').hide();
                $('.edit-only').show();
                $('#password, #confirm_password').removeAttr('required').attr('name', 'ignore_pass');
                $('#user_name').attr('name', 'user_name'); // API edit uses user_name

                $('#recordId').val(data.uuid);
                $('#user_name').val(data.user_name);
                $('#email').val(data.email);
                $('#is_verified').prop('checked', data.is_verified == 1);
            } else {
                $('#modalTitle').text('Add New Admin');
                $('.password-group').show();
                $('.edit-only').hide();
                $('#password, #confirm_password').attr('required', true).attr('name', function() {
                    return this.id;
                });
                $('#user_name').attr('name', 'name'); // API add uses name
            }
            $('#mainModal').modal('show');
        }
        // 3. Form Submission (Add/Edit) with Validation
        $('#mainForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous alerts
            $('#alertBox').hide().removeClass('alert-danger alert-success');

            //validations
            const action = $('#formAction').val();
            const name = $('#user_name').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let errors = [];

            if (name.length < 3) errors.push("Name must be at least 3 characters long.");
            if (!emailRegex.test(email)) errors.push("Please enter a valid email address.");

            // Validation only for 'Add' action
            if (action === 'add') {
                if (password.length < 6) {
                    errors.push("Password must be at least 6 characters long.");
                }
                if (password !== confirmPassword) {
                    errors.push("Passwords do not match.");
                }
            }

            
            if (errors.length > 0) {
                let errorHtml = '<strong>Please fix the following:</strong><ul class="mb-0">';
                errors.forEach(err => errorHtml += `<li>${err}</li>`);
                errorHtml += '</ul>';

                $('#alertBox').addClass('alert-danger').html(errorHtml).show();
                return; // Stop here
            }

    
            $('#submitBtn').prop('disabled', true).text('Saving...');

            $.post('../admin_api/admins_api.php', $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    $('#mainModal').modal('hide');
                    
                    adminTable.ajax.reload(null, false);
                } else {
                    $('#alertBox').addClass('alert-danger').text(res.message).show();
                    $('#submitBtn').prop('disabled', false).text('Save changes');
                }
            }, 'json').fail(function() {
                $('#alertBox').addClass('alert-danger').text("Server error. Please try again.").show();
                $('#submitBtn').prop('disabled', false).text('Save changes');
            });
        });
        // 4. Delete Logic
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this admin?')) {
                $.post('../admin_api/admins_api.php', {
                    action: 'delete',
                    id: id
                }, function(res) {
                    if (res.status === 'success') {
                        adminTable.ajax.reload(null, false);
                    } else {
                        alert(res.message);
                    }
                }, 'json');
            }
        }
    </script>
    <?php require_once "./includes/footer.php"; ?>
</div>