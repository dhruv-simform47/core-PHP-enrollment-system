<?php
session_start();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Courses</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-book me-1"></i> Courses List
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Course</button>
                </div>
                <div class="card-body">
                    <table id="courseTable" class="table table-striped w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Instructor</th>
                                <th>Duration</th>
                                <th>Vacant</th>
                                <th>Max</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form (Simplified) -->
    <div class="modal fade" id="mainModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" id="course_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instructor</label>
                            <select class="form-select" name="instructor_id" id="instructor_id" required>
                                <option value="">Loading...</option>
                            </select>
                        </div>
                    </div>
                    <!-- ... Other fields same as before ... -->
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
        // 1. Initialize Table
        const adminTable = $('#courseTable').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            ajax: {
                url: '../admin_api/courses_api.php',
                type: 'POST',
                data: {
                    action: 'list'
                }
            },
            columns: [{
                    data: 'course_name'
                },
                {
                    data: 'instructor_name'
                },
                {
                    data: 'duration_weeks',
                    render: (data) => `${data} wks`
                },
                {
                    data: 'vacant_seats'
                },
                {
                    data: 'max_seats'
                },
                {
                    data: 'id',
                    render: (data, type, row) => `
                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", ${JSON.stringify(row)})'>Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteRecord('${data}')">Delete</button>`
                }
            ]
        });

        // 2. Load Instructors into Dropdown via AJAX
        function loadInstructors() {
            $.post('../admin_api/courses_api.php', {
                action: 'get_instructors'
            }, function(data) {
                let html = '<option value="" disabled selected>-- Select --</option>';
                data.forEach(inst => {
                    html += `<option value="${inst.uuid}">${inst.user_name}</option>`;
                });
                $('#instructor_id').html(html);
            }, 'json');
        }

        function openModal(action, data = null) {
            $('#mainForm')[0].reset();
            $('#formAction').val(action);
            loadInstructors(); // Refresh dropdown when opening modal

            if (action === 'edit') {
                $('#modalTitle').text('Edit Course');
                $('#recordId').val(data.id);
                $('#course_name').val(data.course_name);
                // Wait slightly for dropdown to load before setting value
                setTimeout(() => $('#instructor_id').val(data.instructor_id), 100);
                $('#duration_weeks').val(data.duration_weeks);
                $('#max_seats').val(data.max_seats);
            } else {
                $('#modalTitle').text('Add New Course');
            }
            $('#mainModal').modal('show');
        }

        $('#mainForm').on('submit', function(e) {
            e.preventDefault();
            $.post('../admin_api/courses_api.php', $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    $('#mainModal').modal('hide');
                    adminTable.ajax.reload(); // REFRESH WITHOUT RELOAD
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        function deleteRecord(id) {
            if (confirm('Delete?')) {
                $.post('../admin_api/courses_api.php', {
                    action: 'delete',
                    id: id
                }, () => adminTable.ajax.reload());
            }
        }
    </script>
    <?php require_once "./includes/footer.php"; ?>