<?php
session_start();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Enrollments</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i> Enrollment Records
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Enrollment</button>
                </div>
                <div class="card-body">
                    <table id="enrollmentTable" class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <div class="modal fade" id="mainModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">

                    <div class="add-only">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student_id" id="student_id">
                                <option value="">Loading students...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select class="form-select" name="course_id" id="course_id">
                                <option value="">Loading courses...</option>
                            </select>
                        </div>
                    </div>

                    <div class="edit-only">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Student:</label>
                            <div id="displayStudent" class="form-control-plaintext border-bottom"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Course:</label>
                            <div id="displayCourse" class="form-control-plaintext border-bottom"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enrollment Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
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
        // Initialize Table
        const enrollTable = $('#enrollmentTable').DataTable({
            processing: true,
            serverSide: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            ajax: {
                url: '../admin_api/enrollments_api.php',
                type: 'POST',
                data: {
                    action: 'list'
                }
            },
            columns: [{
                    data: 'student'
                },
                {
                    data: 'course'
                },
                {
                    data: 'enrolled_date',
                    render: (data) => new Date(data).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    })
                },
                {
                    data: 'status',
                    render: function(data) {
                        let badge = 'bg-primary';
                        if (data === 'completed') badge = 'bg-success';
                        if (data === 'cancelled') badge = 'bg-danger';
                        return `<span class="badge ${badge}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: null,
                    render: (data, type, row) => `
                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", ${JSON.stringify(row)})'>Update Status</button>`
                }
            ]
        });

        // Load Dropdowns (Students & Courses)
        function loadDependencies() {
            $.post('../admin_api/enrollments_api.php', {
                action: 'get_dependencies'
            }, function(res) {
                let sHtml = '<option value="" disabled selected>-- Select Student --</option>';
                res.students.forEach(s => sHtml += `<option value="${s.uuid}">${s.user_name}</option>`);
                $('#student_id').html(sHtml);

                let cHtml = '<option value="" disabled selected>-- Select Course --</option>';
                res.courses.forEach(c => cHtml += `<option value="${c.id}">${c.course_name}</option>`);
                $('#course_id').html(cHtml);
            }, 'json');
        }

        function openModal(action, data = null) {
            $('#mainForm')[0].reset();
            $('#alertBox').hide();
            $('#formAction').val(action);

            if (action === 'edit') {
                $('#modalTitle').text('Update Enrollment Status');
                $('.add-only').hide();
                $('.edit-only').show();
                $('#recordId').val(data.id);
                $('#displayStudent').text(data.student);
                $('#displayCourse').text(data.course);
                $('#status').val(data.status);
            } else {
                $('#modalTitle').text('Add New Enrollment');
                $('.add-only').show();
                $('.edit-only').hide();
                loadDependencies(); // Only load list when adding
            }
            $('#mainModal').modal('show');
        }

        $('#mainForm').on('submit', function(e) {
            e.preventDefault();
            $('#submitBtn').prop('disabled', true).text('Saving...');
            $.post('../admin_api/enrollments_api.php', $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    $('#mainModal').modal('hide');
                    enrollTable.ajax.reload();
                    $('#submitBtn').prop('disabled', false).text('Save changes');
                } else {
                    $('#alertBox').text(res.message).show();
                    $('#submitBtn').prop('disabled', false).text('Save changes');
                }
            }, 'json');
        });
    </script>
    <?php require_once "./includes/footer.php"; ?>