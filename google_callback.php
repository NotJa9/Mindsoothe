<!-- google_callback.php -->

<?php
// Control error display - only show errors if debugging is enabled
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Add proxy endpoint for API fetching - must be at the top to prevent any output before headers
if (isset($_GET['fetch'])) {
    // Clean any output buffers to prevent warnings being sent before JSON
    if (ob_get_level()) ob_clean();

    header("Content-Type: application/json");

    // Create cache directory if it doesn't exist
    if (!file_exists('cache')) {
        mkdir('cache', 0755, true);
    }

    $endpoint = $_GET['fetch'] === 'departments'
        ? "https://apidev.usl.edu.ph/api/PublicAPI/Departments"
        : "https://apidev.usl.edu.ph/api/PublicAPI/Courses";

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

// Now we can include required files and setup
require_once __DIR__ . '/vendor/autoload.php';
include 'connect.php'; // Ensure your database connection is included

// Check if debugging is enabled
$debug = isset($_GET['debug']) && $_GET['debug'] == 1;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Access credentials from the .env file
$clientId = $_ENV['CLIENT_ID'];
$clientSecret = $_ENV['CLIENT_SECRET'];

$redirectUri = 'http://localhost/Mindsoothe/google_callback.php';

$client = new Google\Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Function to download and save profile image locally
function saveProfileImage($imageUrl, $idNumber)
{
    $directory = 'user_images';
    $localPath = "$directory/$idNumber.jpg";

    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    // Download and save the image
    $imageContent = @file_get_contents($imageUrl);
    if ($imageContent !== false) {
        file_put_contents($localPath, $imageContent);
        return $localPath;
    }

    // Return a default image path if download fails
    return "assets/default_profile.jpg";
}

// Handling the callback from Google
if (isset($_GET['code'])) {
    // Exchange authorization code for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        // Create a service instance to get user profile data
        $oauth2 = new Google\Service\Oauth2($client);
        $userInfo = $oauth2->userinfo->get(); // Fetch user profile information

        // Extract user details
        $email = $userInfo->getEmail();
        $firstName = $userInfo->getGivenName();
        $lastName = $userInfo->getFamilyName();
        $googlePicture = $userInfo->getPicture(); // URL to user's profile picture

        // Check if email ends with "@usl.edu.ph"
        if (substr($email, -11) !== "@usl.edu.ph") {
            echo "<script type='text/javascript'>
                    alert('This website is only available to USL affiliates. Please use your corporate email to sign in.');
                    window.location.href = 'Login.html'; // Redirect to login page
                </script>";
            exit();
        }

        // Extract ID number from email (assuming format is "ID@usl.edu.ph")
        $idNumber = explode('@', $email)[0];

        // Check if ID is only letters (likely faculty/staff) or specifically an MHP
        if (ctype_alpha($idNumber) || stripos($idNumber, 'mhp') !== false) {
            echo "<script type='text/javascript'>
                    alert('This sign-in method is only for students. Faculty, staff, and Mental Health Professionals must use the standard login form.');
                    window.location.href = 'Login.html'; // Redirect to login page
                </script>";
            exit();
        }


        // Save profile picture locally to avoid 429 errors
        $picture = saveProfileImage($googlePicture, $idNumber);

        // Check if user exists in the database
        $checkUser = "SELECT * FROM Users WHERE email=?";
        $stmt = $conn->prepare($checkUser);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, log them in
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            $_SESSION['picture'] = $picture;
            header("Location: gracefulThread.php");
        } else {
            // User does not exist, show modal for additional information
            session_start();
            // Store temporary data in session
            $_SESSION['temp_email'] = $email;
            $_SESSION['temp_firstName'] = $firstName;
            $_SESSION['temp_lastName'] = $lastName;
            $_SESSION['temp_picture'] = $picture;
            $_SESSION['temp_idNumber'] = $idNumber;

            // Display HTML with modal form
?>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Complete Your Profile</title>
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body {
                        background-color: #f8f9fa;
                        font-family: Arial, sans-serif;
                    }

                    .profile-container {
                        max-width: 600px;
                        margin: 50px auto;
                        padding: 30px;
                        background-color: #fff;
                        border-radius: 10px;
                        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                    }

                    .profile-picture {
                        width: 100px;
                        height: 100px;
                        border-radius: 50%;
                        object-fit: cover;
                        margin-bottom: 20px;
                    }

                    .welcome-text {
                        margin-bottom: 30px;
                    }

                    .form-label {
                        font-weight: 600;
                    }

                    .api-error {
                        color: #721c24;
                        background-color: #f8d7da;
                        border-color: #f5c6cb;
                        padding: 10px;
                        margin-bottom: 15px;
                        border-radius: 5px;
                    }

                    #debug-info {
                        margin-top: 20px;
                        padding: 10px;
                        background-color: #f8f9fa;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                        font-family: monospace;
                        font-size: 12px;
                        display: none;
                    }

                    .status-indicator {
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-size: 0.8rem;
                        margin-left: 10px;
                    }

                    .status-loading {
                        background-color: #e2e3e5;
                        color: #41464b;
                    }

                    .status-success {
                        background-color: #d1e7dd;
                        color: #0f5132;
                    }

                    .status-error {
                        background-color: #f8d7da;
                        color: #842029;
                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <div class="profile-container">
                        <div class="text-center">
                            <img src="<?php echo $picture; ?>" alt="Profile Picture" class="profile-picture">
                            <h2>Welcome, <?php echo $firstName . ' ' . $lastName; ?>!</h2>
                            <p class="welcome-text">Please complete your profile to continue</p>
                        </div>

                        <form action="complete_profile.php" method="post">
                            <div class="mb-3">
                                <label for="idNumber" class="form-label">ID Number</label>
                                <input type="text" class="form-control" id="idNumber" name="idNumber" value="<?php echo $idNumber; ?>" readonly="true">
                            </div>

                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="" selected disabled>Loading departments...</option>
                                    </select>
                                    <span id="dept-status" class="status-indicator status-loading">Loading...</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="program" class="form-label">Program</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-select" id="program" name="program" required>
                                        <option value="" selected disabled>Select department first</option>
                                    </select>
                                    <span id="prog-status" class="status-indicator status-loading">Waiting...</span>
                                </div>
                                <div id="program-count" class="form-text text-muted"></div>
                            </div>

                            <div class="mb-3">
                                <label for="year" class="form-label">Year Level</label>
                                <input type="text" class="form-control" id="year" name="year" placeholder="Enter your year level (e.g. 1, 2, 3, 4)" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Create Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Password must be at least 8 characters long</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Complete Registration</button>
                            </div>
                        </form>

                        <!-- Debug info section (hidden by default) -->
                        <!-- <div id="debug-info"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-3" onclick="toggleDebug()">
                            Toggle Debug Info
                        </button> -->
                    </div>
                </div>

                <!-- Bootstrap JS Bundle with Popper -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const departmentSelect = document.getElementById('department');
                        const programSelect = document.getElementById('program');
                        const deptStatus = document.getElementById('dept-status');
                        const progStatus = document.getElementById('prog-status');
                        const debugInfo = document.getElementById('debug-info');

                        // Initialize allCourses variable
                        let allCourses = [];

                        // Debug function to log information
                        function logDebug(message) {
                            console.log(message);
                            if (debugInfo) {
                                debugInfo.innerHTML += message + '<br>';
                                // Show debug info automatically when errors occur
                                if (message.includes('Error')) {
                                    debugInfo.style.display = 'block';
                                }
                            }
                        }

                        // Function to update status indicators
                        function updateStatus(element, status, message) {
                            element.className = 'status-indicator status-' + status;
                            element.textContent = message;
                        }

                        // Fetch departments using the proxy endpoint
                        async function fetchDepartments() {
                            try {
                                updateStatus(deptStatus, 'loading', 'Loading...');

                                const response = await fetch('?fetch=departments');

                                if (!response.ok) {
                                    throw new Error(`HTTP error! Status: ${response.status}`);
                                }

                                const text = await response.text();

                                try {
                                    // Try to parse as JSON
                                    const data = JSON.parse(text);

                                    // Check if the response contains an error
                                    if (data.error) {
                                        throw new Error(data.error);
                                    }

                                    logDebug(`Fetched ${Array.isArray(data) ? data.length : 'unknown'} departments`);

                                    // Clear and populate departments dropdown
                                    departmentSelect.innerHTML = '<option value="" selected disabled>Select your department</option>';

                                    if (Array.isArray(data)) {
                                        data.forEach(dept => {
                                            const option = document.createElement('option');
                                            option.value = dept.departmentCode;
                                            option.textContent = dept.departmentName;
                                            departmentSelect.appendChild(option);
                                        });
                                        updateStatus(deptStatus, 'success', 'Loaded');
                                        return true;
                                    } else {
                                        throw new Error('Invalid data format: not an array');
                                    }
                                } catch (parseError) {
                                    // If parsing fails, log the error and raw response
                                    logDebug(`JSON Parse Error: ${parseError.message}`);
                                    logDebug(`Raw Response (first 100 chars): ${text.substring(0, 100)}`);
                                    throw parseError;
                                }
                            } catch (error) {
                                logDebug(`Error fetching departments: ${error.message}`);
                                updateStatus(deptStatus, 'error', 'Error!');
                                departmentSelect.innerHTML = '<option value="" selected disabled>Error loading departments</option>';
                                return false;
                            }
                        }

                        // Fetch all courses and filter them based on department
                        async function fetchCourses() {
                            try {
                                updateStatus(progStatus, 'loading', 'Loading...');

                                const response = await fetch('?fetch=courses');

                                if (!response.ok) {
                                    throw new Error(`HTTP error! Status: ${response.status}`);
                                }

                                const text = await response.text();

                                try {
                                    // Try to parse as JSON
                                    const data = JSON.parse(text);

                                    // Check if the response contains an error
                                    if (data.error) {
                                        throw new Error(data.error);
                                    }

                                    // Store all courses - handle both array and object with data property
                                    allCourses = Array.isArray(data) ? data : (data.data || []);

                                    logDebug(`Fetched ${allCourses.length} courses`);

                                    updateStatus(progStatus, 'success', 'Ready');
                                    document.getElementById('program-count').textContent =
                                        `${allCourses.length} programs available (select a department first)`;
                                    return true;
                                } catch (parseError) {
                                    // If parsing fails, log the error and raw response
                                    logDebug(`JSON Parse Error: ${parseError.message}`);
                                    logDebug(`Raw Response (first 100 chars): ${text.substring(0, 100)}`);
                                    throw parseError;
                                }
                            } catch (error) {
                                logDebug(`Error fetching courses: ${error.message}`);
                                updateStatus(progStatus, 'error', 'Error!');
                                document.getElementById('program-count').textContent =
                                    'Error loading programs. Please try again later.';
                                return false;
                            }
                        }

                        // Create a mapping between department codes in dropdown and numeric codes in course data
                        function filterProgramsByDepartment(departmentCode) {
                            logDebug(`Selected department: ${departmentCode}`);

                            // Clear program dropdown
                            programSelect.innerHTML = '<option value="" selected disabled>Select your program</option>';

                            if (!departmentCode) {
                                document.getElementById('program-count').textContent =
                                    'Please select a department first';
                                return;
                            }

                            // Department code mapping - map dropdown codes to numeric codes used in the course data
                            // You'll need to complete this mapping for all departments
                            const departmentMapping = {
                                'SHVED': '0001', // Example mapping, confirm the actual code
                                'SABH': '0002', // Example mapping, confirm the actual code
                                'SACE': '0003', // Based on your debug logs
                                'SICS': '0004', // Based on your debug logs
                                'SECAP': '0005', // Based on your debug logs
                                'SHAS': '0006', // Based on your debug logs
                                'SAS': '0007', // Based on your debug logs
                                'HS': '0008', // Based on your debug logs
                                'HSS': '0009', // Based on your debug logs
                                'ELEM': '0010', // Based on your debug logs
                                'DKLC': '0011', // Based on your debug logs
                                // Add more mappings as you identify them
                            };

                            // Get numeric department code
                            const numericDeptCode = departmentMapping[departmentCode];
                            logDebug(`Mapped department ${departmentCode} to numeric code: ${numericDeptCode}`);

                            // Filter courses based on the numeric department code
                            const filteredPrograms = allCourses.filter(course =>
                                course.department === numericDeptCode
                            );

                            logDebug(`Filtered ${filteredPrograms.length} programs for department ${departmentCode} (${numericDeptCode})`);

                            // Populate the dropdown with filtered results
                            if (filteredPrograms.length > 0) {
                                filteredPrograms.forEach(prog => {
                                    const option = document.createElement('option');
                                    option.value = prog.courseCode || '';

                                    // Get the program title and major
                                    const title = prog.programTitle || prog.courseName || prog.courseCode || '';
                                    const major = prog.major || '';

                                    // Format as "Program Title (Major)" if major exists, otherwise just show program title
                                    option.textContent = major && major !== 'null' ? `${title} (${major})` : title;

                                    programSelect.appendChild(option);
                                });

                                document.getElementById('program-count').textContent =
                                    `${filteredPrograms.length} programs available for this department`;
                            } else {
                                programSelect.innerHTML += '<option value="" disabled>No programs found for this department</option>';
                                document.getElementById('program-count').textContent =
                                    'No programs found for this department';

                                // If mapping doesn't exist or no programs found, show helpful message
                                if (!numericDeptCode) {
                                    logDebug(`WARNING: No mapping defined for department code ${departmentCode}`);
                                }
                            }
                        }

                        // Add a function to help you determine the correct department mapping
                        function findDepartmentMappings() {
                            // Extract all department codes from courses
                            const departmentCodesInCourses = new Set();
                            allCourses.forEach(course => {
                                if (course.department) {
                                    departmentCodesInCourses.add(course.department);
                                }
                            });

                            // Log all department codes found in courses
                            logDebug("All numeric department codes found in courses: " +
                                Array.from(departmentCodesInCourses).join(', '));

                            // This will help identify which numeric codes to map to your dropdown values

                            // Optional: Count courses per department for verification
                            const coursesPerDepartment = {};
                            allCourses.forEach(course => {
                                if (course.department) {
                                    coursesPerDepartment[course.department] =
                                        (coursesPerDepartment[course.department] || 0) + 1;
                                }
                            });

                            logDebug("Courses per department: " + JSON.stringify(coursesPerDepartment));
                        }

                        // Call this function during initialization to help establish the mapping
                        document.addEventListener('DOMContentLoaded', function() {
                            // Add this call to your existing initialization
                            const existingInitFunction = initializeData;

                            // Replace initializeData with an enhanced version
                            initializeData = async function() {
                                await existingInitFunction();

                                // After data is loaded, find department mappings
                                findDepartmentMappings();
                            };
                        });

                        // Add change event listener for department dropdown
                        departmentSelect.addEventListener('change', function() {
                            const selectedDepartment = departmentSelect.value;
                            if (selectedDepartment) {
                                filterProgramsByDepartment(selectedDepartment);
                            }
                        });

                        // Enhanced password validation
                        document.querySelector('form').addEventListener('submit', function(e) {
                            const password = document.getElementById('password').value;
                            const confirmPassword = document.getElementById('confirmPassword').value;

                            if (password.length < 8) {
                                e.preventDefault();
                                alert('Password must be at least 8 characters long.');
                                return;
                            }

                            if (password !== confirmPassword) {
                                e.preventDefault();
                                alert('Passwords do not match. Please try again.');
                            }
                        });

                        // Initialize data loading
                        async function initializeData() {
                            // First, fetch all departments
                            const deptSuccess = await fetchDepartments();
                            // Then fetch all courses
                            const coursesSuccess = await fetchCourses();

                            logDebug(`Initial data loading complete. Departments: ${deptSuccess ? 'Success' : 'Failed'}, Courses: ${coursesSuccess ? 'Success' : 'Failed'}`);
                        }

                        // Start loading data
                        initializeData();
                    });

                    // Function to toggle debug info visibility
                    function toggleDebug() {
                        const debugInfo = document.getElementById('debug-info');
                        if (debugInfo.style.display === 'none' || debugInfo.style.display === '') {
                            debugInfo.style.display = 'block';
                        } else {
                            debugInfo.style.display = 'none';
                        }
                    }
                </script>
            </body>

            </html>
<?php
            exit();
        }
    } else {
        // Handle error during access token exchange
        echo "Error fetching access token: " . $token['error_description'];
    }
} else {
    echo "No authentication code provided!";
}
?>