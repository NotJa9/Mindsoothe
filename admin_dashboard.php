<!-- admin_dashboard.php -->
<?php
 // Prevent browser caching (so back button won't work)
 header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
 header("Pragma: no-cache");
 header("Expires: 0");

// Add proxy endpoint for API fetching at the top to prevent any output before headers
if (isset($_GET['fetch']) && $_GET['fetch'] === 'departments') {
    // Clean any output buffers to prevent warnings being sent before JSON
    if (ob_get_level()) ob_clean();

    header("Content-Type: application/json");

    // Create cache directory if it doesn't exist
    if (!file_exists('cache')) {
        mkdir('cache', 0755, true);
    }

    $endpoint = "https://apidev.usl.edu.ph/api/PublicAPI/Departments";
    $cacheFile = 'cache/' . md5($endpoint) . '.json';
    $cacheTime = 3600; // 1 hour cache

    // Use cached data if available and not expired
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        echo file_get_contents($cacheFile);
        exit();
    }

    // Otherwise fetch from API
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: text/plain",
        "cache-control: no-cache"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    curl_close($ch);

    // Handle errors
    if ($err || $httpCode != 200) {
        echo json_encode(['error' => "HTTP Error: $httpCode, cURL Error: $err"]);
        exit();
    }

    // Verify that the response is valid JSON
    $data = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If not valid JSON, return error
        echo json_encode(['error' => 'Invalid JSON response from API', 'raw' => substr($response, 0, 255)]);
        exit();
    }

    // Cache successful responses
    file_put_contents($cacheFile, $response);

    // Output the API response
    echo $response;
    exit();
}

require_once 'admin_mhp_acc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addCounselor($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['department']);
                if ($result['status'] === 'error' && $result['message'] === 'Email already exists') {
                    echo "<script>alert('The email already exists. Please use a different email.');</script>";
                } elseif ($result['status'] === 'success') {
                    echo "<script>alert('Counselor added successfully.'); window.location.href='admin_dashboard.php';</script>";
                } else {
                    echo "<script>alert('Error: Could not add counselor. Please try again.');</script>";
                }
                break;
            case 'update':
                updateCounselor($_POST['id'], $_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['department']);
                break;
            case 'delete':
                deleteCounselor($_POST['id']);
                break;
        }
        if ($_POST['action'] !== 'add') {
            header('Location: admin_dashboard.php');
            exit;
        }
    }
}

// Add this line to get all counselors
$MHP = getAllCounselors();

// We'll use the fallback departments only if JavaScript fails to load departments
$fallbackDepartments = array(
    array('code' => 'SACE', 'name' => 'SACE'),
    array('code' => 'SABH', 'name' => 'SABH'),
    array('code' => 'SEAS', 'name' => 'SEAS'),
    array('code' => 'SHAS', 'name' => 'SHAS'),
    array('code' => 'BES', 'name' => 'BES')
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Counselor Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Guidance Counselor Management</h1>
    <form method="POST" action="logout.php">
        <button type="submit" 
                class="inline-block mt-6 bg-white text-[#1cabe3] font-bold border-2 border-[#1cabe3] py-3 px-6 rounded-lg hover:bg-[#1cabe3] hover:text-white transition duration-300">
            Logout
        </button>
    </form>
</div>
        
        <!-- Add New Counselor Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Add New Counselor</h2>
            <form id="addForm" method="POST" class="space-y-4">

                <input type="hidden" name="action" value="add">
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="fname" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="lname" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <select name="department" id="departmentSelect" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Loading departments...</option>
                    </select>
                    <div id="dept-status" class="text-sm mt-1 text-gray-500">Loading departments...</div>
                </div>
                <button type="submit" class="inline-block mt-6 bg-white text-[#1cabe3] font-bold border-2 border-[#1cabe3] py-3 px-6 rounded-lg hover:bg-[#1cabe3] hover:text-white transition duration-300">Add Counselor</button>
            </form>
        </div>

        <div class="mb-4 relative w-1/3">
    <input type="text" 
           id="counselorSearch" 
           placeholder="Search counselors by name, email, or department..." 
           class="w-full px-4 py-2 pl-10 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 18a8 8 0 100-16 8 8 0 000 16zm6.32-1.9l4.24 4.24"></path>
    </svg>
</div>

        <!-- Counselors List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Counselors List</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">First Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="counselorsTableBody">
    <?php foreach ($MHP as $MHP): ?>
    <tr class="counselor-row" 
        data-fname="<?php echo htmlspecialchars(strtolower($MHP['fname'])); ?>"
        data-lname="<?php echo htmlspecialchars(strtolower($MHP['lname'])); ?>"
        data-email="<?php echo htmlspecialchars(strtolower($MHP['email'])); ?>"
        data-department="<?php echo htmlspecialchars(strtolower($MHP['department'])); ?>">
        <td class="px-6 py-4"><?php echo htmlspecialchars($MHP['fname']); ?></td>
        <td class="px-6 py-4"><?php echo htmlspecialchars($MHP['lname']); ?></td>
        <td class="px-6 py-4"><?php echo htmlspecialchars($MHP['email']); ?></td>
        <td class="px-6 py-4"><?php echo htmlspecialchars($MHP['department']); ?></td>
        <td class="px-6 py-4">
            <button onclick="editCounselor(<?php echo $MHP['id']; ?>)" 
                    class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
            <!-- <button onclick="deleteCounselor(<?php echo $MHP['id']; ?>)" 
                    class="text-red-600 hover:text-red-900">Delete</button> -->
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Edit Counselor</h3>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="fname" id="edit_fname" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="lname" id="edit_lname" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="edit_email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <select name="department" id="edit_department" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Loading departments...</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Function to fetch departments from our proxy endpoint
    async function fetchDepartments() {
        try {
            // Update status
            document.getElementById('dept-status').textContent = 'Loading departments...';
            
            // Fetch departments from our proxy endpoint
            const response = await fetch('admin_dashboard.php?fetch=departments');
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Check if data is an error response
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Clear the selects
            const departmentSelect = document.getElementById('departmentSelect');
            const editDepartmentSelect = document.getElementById('edit_department');
            
            departmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';
            editDepartmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';
            
            // Populate department dropdowns
            if (Array.isArray(data)) {
                data.forEach(dept => {
                    // Find the appropriate property names from the API response
                    const code = dept.departmentCode || dept.code || '';
                    const name = dept.departmentName || dept.name || code;
                    
                    // Add to department dropdown
                    const option1 = document.createElement('option');
                    option1.value = code;
                    option1.textContent = name;
                    departmentSelect.appendChild(option1);
                    
                    // Add to edit form dropdown
                    const option2 = document.createElement('option');
                    option2.value = code;
                    option2.textContent = name;
                    editDepartmentSelect.appendChild(option2);
                });
                
                // Update status
                document.getElementById('dept-status').textContent = `${data.length} departments loaded successfully`;
            } else {
                throw new Error('Invalid data format: not an array');
            }
        } catch (error) {
            console.error('Error fetching departments:', error);
            document.getElementById('dept-status').textContent = `Error: ${error.message}. Using fallback departments.`;
            
            // Use fallback departments as defined in PHP
            const fallbackDepts = <?php echo json_encode($fallbackDepartments); ?>;
            
            // Clear the selects
            const departmentSelect = document.getElementById('departmentSelect');
            const editDepartmentSelect = document.getElementById('edit_department');
            
            departmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';
            editDepartmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';
            
            // Populate with fallback departments
            fallbackDepts.forEach(dept => {
                // Add to department dropdown
                const option1 = document.createElement('option');
                option1.value = dept.code;
                option1.textContent = dept.name;
                departmentSelect.appendChild(option1);
                
                // Add to edit form dropdown
                const option2 = document.createElement('option');
                option2.value = dept.code;
                option2.textContent = dept.name;
                editDepartmentSelect.appendChild(option2);
            });
        }
    }

    function editCounselor(id) {
        $.get('admin_get_mhp.php', {id: id}, function(data) {
            const MHP = JSON.parse(data);
            $('#edit_id').val(MHP.id);
            $('#edit_fname').val(MHP.fname);
            $('#edit_lname').val(MHP.lname);
            $('#edit_email').val(MHP.email);
            $('#edit_department').val(MHP.department);
            $('#editModal').removeClass('hidden');
        });
    }

    function closeEditModal() {
        $('#editModal').addClass('hidden');
    }

    function deleteCounselor(id) {
        if (confirm('Are you sure you want to delete this counselor?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Search functionality
    $(document).ready(function() {
        // Load departments as soon as the page loads
        fetchDepartments();
        
        // Search functionality
        $('#counselorSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            
            if (searchTerm === '') {
                // If search is empty, show all rows
                $('.counselor-row').show();
                $('#noResultsRow').hide();
                return;
            }
            
            // Filter through each row
            $('.counselor-row').each(function() {
                const $row = $(this);
                const fname = $row.data('fname');
                const lname = $row.data('lname');
                const email = $row.data('email');
                const department = $row.data('department');
                
                // Check if any field contains the search term
                const matches = fname.includes(searchTerm) ||
                              lname.includes(searchTerm) ||
                              email.includes(searchTerm) ||
                              department.includes(searchTerm);
                
                // Show/hide row based on match
                $row.toggle(matches);
            });
            
            // Show a message if no results found
            const visibleRows = $('.counselor-row:visible').length;
            const noResultsRow = $('#noResultsRow');
            
            if (visibleRows === 0) {
                if (noResultsRow.length === 0) {
                    const colspan = $('.counselor-row:first td').length || 5;
                    $('#counselorsTableBody').append(`
                        <tr id="noResultsRow">
                            <td colspan="${colspan}" class="px-6 py-4 text-center text-gray-500">
                                No counselors found matching "${searchTerm}"
                            </td>
                        </tr>
                    `);
                } else {
                    noResultsRow.show();
                }
            } else {
                noResultsRow.hide();
            }
        });
    });
    </script>
</body>
</html>