<?php
// Handle proxy logic BEFORE outputting any HTML
if (isset($_GET['fetch'])) {
    header("Content-Type: application/json");

    $endpoint = $_GET['fetch'] === 'departments'
        ? "https://apidev.usl.edu.ph/api/PublicAPI/Departments"
        : "https://apidev.usl.edu.ph/api/PublicAPI/Courses";

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: text/plain"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Departments & Courses Modal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<!-- Button to open modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dataModal">
  Open Departments & Courses Modal
</button>

<!-- Modal -->
<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Department & Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label for="departments" class="form-label">Departments</label>
          <select id="departments" class="form-select">
            <option selected disabled>Loading...</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="courses" class="form-label">Courses</label>
          <select id="courses" class="form-select">
            <option selected disabled>Loading...</option>
          </select>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const departmentsSelect = document.getElementById('departments');
  const coursesSelect = document.getElementById('courses');

  async function fetchDepartments() {
    try {
      const res = await fetch('?fetch=departments');
      const data = await res.json();
      console.log("Departments:", data);
      departmentsSelect.innerHTML = '<option selected disabled>Select Department</option>';
      data.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.departmentCode;
        opt.textContent = item.departmentName;
        departmentsSelect.appendChild(opt);
      });
    } catch (err) {
      console.log('Error fetching departments:', err);
    }
  }

  async function fetchCourses(departmentCode = null) {
        try {
            const res = await fetch('?fetch=courses');
            const data = await res.json();
            console.log("Raw courses response:", data);

            const courses = data.data || []; // Correctly access the courses array
            coursesSelect.innerHTML = '<option selected disabled>Select Program</option>';

            // Optional filtering by department
            const filteredCourses = departmentCode
            ? courses.filter(course => course.departmentCode === departmentCode)
            : courses;

            filteredCourses.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.courseCode;
            opt.textContent = item.programTitle;
            coursesSelect.appendChild(opt);
            });
        } catch (err) {
            console.log('Error fetching courses:', err);
        }
    }




  document.getElementById('dataModal').addEventListener('show.bs.modal', () => {
    fetchDepartments();
    fetchCourses();
  });
</script>

</body>
</html>
