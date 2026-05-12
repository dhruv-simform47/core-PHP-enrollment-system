<?php
session_start();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Students</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-graduate me-1"></i> Students List
                    <div class="float-end">
                        <a href="./addStudent_csv.php" class="btn btn-secondary btn-sm me-2">Upload CSV</a>
                        <button class="btn btn-primary btn-sm" onclick="openModal('add')">Add New Student</button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="studentTable" class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form (IDs kept consistent with your logic) -->
    <div class="modal fade" id="mainModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        // Initialize DataTable
        const studentTable = $('#studentTable').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',

            ajax: {
                url: '../admin_api/students_api.php',
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
                    render: (data) => data == 1 ?
                        '<span class="badge bg-success">Verified</span>' :
                        '<span class="badge bg-warning">Pending</span>'
                },
                {
                    data: 'created_at',
                    render: (data) => new Date(data).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    })
                },
                {
                    data: 'uuid',
                    render: (data, type, row) => `
                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", ${JSON.stringify(row)})'>Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRecord('${data}')">Delete</button>`
                }
            ]
        });

        function openModal(action, data = null) {
            $('#mainForm')[0].reset();
            $('#alertBox').hide();
            $('#formAction').val(action);

            if (action === 'edit') {
                $('#modalTitle').text('Edit Student');
                $('.password-group').hide();
                $('.edit-only').show();
                $('#password, #confirm_password').removeAttr('required').attr('name', 'ignore_pass');
                $('#user_name').attr('name', 'user_name');

                $('#recordId').val(data.uuid);
                $('#user_name').val(data.user_name);
                $('#email').val(data.email);
                $('#is_verified').prop('checked', data.is_verified == 1);
            } else {
                $('#modalTitle').text('Add New Student');
                $('.password-group').show();
                $('.edit-only').hide();
                $('#password, #confirm_password').attr('required', true).attr('name', function() {
                    return this.id;
                });
                $('#user_name').attr('name', 'name');
            }
            $('#mainModal').modal('show');
        }

        $('#mainForm').on('submit', function(e) {
            e.preventDefault();
            $('#submitBtn').prop('disabled', true).text('Saving...');
            $.post('../admin_api/students_api.php', $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    $('#mainModal').modal('hide');
                    studentTable.ajax.reload();
                    $('#submitBtn').prop('disabled', false).text('Save changes');
                } else {
                    $('#alertBox').removeClass('alert-success').addClass('alert-danger').text(res.message).show();
                    $('#submitBtn').prop('disabled', false).text('Save changes');
                }
            }, 'json');
        });

        function deleteRecord(id) {
            if (confirm('Delete student?')) {
                $.post('../admin_api/students_api.php', {
                    action: 'delete',
                    id: id
                }, function(res) {
                    if (res.status === 'success') studentTable.ajax.reload();
                    else alert(res.message);
                }, 'json');
            }
        }
    </script>
    <?php require_once "./includes/footer.php"; ?>