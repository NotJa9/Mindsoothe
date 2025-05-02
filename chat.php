<?php
    include("auth.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css'>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f4f7f6;
        }
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .sidebar {
            transition: width 0.3s ease;
            width: 256px;
            min-width: 256px;
        }
        .sidebar.collapsed {
            width: 80px;
            min-width: 80px;
        }
        .main-content {
            transition: margin-left 0.3s ease;
            margin-left: 256px;
        }
        .main-content.expanded {
            margin-left: 80px;
        }
        .menu-item {
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            background-color: #f3f4f6;
        }
        .menu-item.active {
            color: #1cabe3;
            background-color: #eff6ff;
            border-right: 4px solid #1cabe3;
        }
        .menu-text {
            transition: opacity 0.3s ease;
        }
        .sidebar.collapsed .menu-text {
            opacity: 0;
            display: none;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .content-section {
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed top-0 left-0 h-screen bg-white shadow-lg z-10">
        <!-- Logo Section -->
        <div class="flex items-center p-6 border-b">
            <div class="w-15 h-10 rounded-full flex items-center justify-center">
                <a href="#"><img src="images/Mindsoothe(2).svg" alt="Mindsoothe Logo"></a>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="mt-6">
            <a href="#" class="menu-item flex items-center px-6 py-3" data-section="dashboard" id="gracefulThreadItem">
                <img src="images/gracefulThread.svg" alt="Graceful Thread" class="w-5 h-5">
                <span class="menu-text ml-3">Graceful Thread</span>
            </a>
            <a href="#" class="menu-item flex items-center px-6 py-3 text-gray-600" data-section="appointments" id="MentalWellness">
                <img src="images/Vector.svg" alt="Mental Wellness Companion" class="w-5 h-5">
                <span class="menu-text ml-3">Mental Wellness Companion</span>
            </a>
            <a href="#" class="menu-item flex items-center px-6 py-3 text-gray-600" data-section="profile" id="ProfileItem">
                <img src="images/Vector.svg" alt="Mental Wellness Companion" class="w-5 h-5">
                <span class="menu-text ml-3">Profile</span>
            </a>
            <a href="#" class="menu-item active flex items-center px-6 py-3 text-gray-600" data-section="chat" id="chatItem">
                <img src="images/Vector.svg" alt="Mental Wellness Companion" class="w-5 h-5">
                <span class="menu-text ml-3">Chat</span>
            </a>
        </nav>

        <!-- User Profile and Logout Section -->
        <div class="absolute bottom-0 w-full border-t">
            <!-- User Profile -->
            <a href="#" class="menu-item flex items-center px-6 py-4 text-gray-600">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="w-8 h-8 rounded-full">
                <span class="menu-text ml-3"><?php echo htmlspecialchars($fullName); ?></span>
            </a>

            <!-- Logout -->
            <a href="landingpage.html" class="menu-item flex items-center px-6 py-4 text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="menu-text ml-3">Logout</span>
            </a>  
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        
    <div id="chats-section" class="section">
    <div class="flex h-screen" style="margin-left: 256px; width: calc(100% - 256px);">
        <!-- Sidebar for Contacts -->
        <div class="w-1/4 bg-white border-r">
            <div class="p-4 border-b">
              <!-- Change H1 to search bar for students -->
                <div class="relative">
                    <input type="text" placeholder="Search students..." class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    <svg class="absolute top-2 right-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a4 4 0 11-8 0 4 4 0 018 0zM21 21l-4.35-4.35"></path>
                    </svg>
                </div>
            </div>
            <ul class="overflow-y-auto">
                <li class="p-4 flex items-center hover:bg-gray-100 cursor-pointer">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div class="flex-1">
                        <p class="font-semibold">Student Name</p>
                        <p class="text-sm text-gray-500">Latest message preview...</p>
                    </div>
                    <span class="text-sm text-gray-400">10:37 AM</span>
                </li>
            </ul>
        </div>
        <!-- Chat Content -->
        <div class="flex-1 flex flex-col">
            <div class="p-4 border-b bg-white flex items-center justify-between">
                <h2 class="text-lg font-semibold">Chat with User</h2>
                <button class="text-gray-500">...</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="flex">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div class="bg-gray-100 rounded-lg p-3">
                        <p>Sample message text.</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <div class="bg-blue-500 text-white rounded-lg p-3">
                        <p>Response message text.</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t bg-white flex items-center">
                <input type="text" placeholder="Type your message..." class="flex-1 p-3 border rounded-lg">
                <button class="ml-3 p-3 bg-blue-500 text-white rounded-lg">Send</button>
            </div>
        </div>
        <div class="w-full lg:w-1/4 bg-white border-l p-4">
            <h2 class="text-lg font-semibold mb-4">Vacants</h2>
            <div class="border rounded-lg p-4">
                <div class="aspect-w-4 aspect-h-3">
                    <iframe 
                        src="https://calendar.google.com/calendar/embed?src=pablojaninekarla%40gmail.com&ctz=Asia%2FManila" 
                        style="border: 0;" 
                        class="w-full h-full rounded-lg" 
                        frameborder="0" 
                        scrolling="no">
                    </iframe>
                </div>
            </div>
            <h2 class="text-lg font-semibold mb-4">PHQ9 Results</h2>
        </div>
        
    </div>
</div></div>
    <script>
        // Section switching functionality
        const menuItems = document.querySelectorAll('.menu-item');
        const sections = document.querySelectorAll('.section');
        
        menuItems.forEach(item => {
          item.addEventListener('click', function(e) {
            if (this.getAttribute('data-section')) {
              e.preventDefault();
              
              menuItems.forEach(mi => mi.classList.remove('active'));
              sections.forEach(section => section.classList.remove('active'));
              
              this.classList.add('active');
              
              const sectionId = this.getAttribute('data-section');
              document.getElementById(`${sectionId}-section`).classList.add('active');
            }
          });
        });
    </script>
    <script src="sidebarnav.js"></script>
</body>
<script>
     // Section switching functionality
     const menuItems = document.querySelectorAll('.menu-item');
            const sections = document.querySelectorAll('.section');
            
            menuItems.forEach(item => {
              item.addEventListener('click', function(e) {
                if (this.getAttribute('data-section')) {
                  e.preventDefault();
                  
                  menuItems.forEach(mi => mi.classList.remove('active'));
                  sections.forEach(section => section.classList.remove('active'));
                  
                  this.classList.add('active');
                  
                  const sectionId = this.getAttribute('data-section');
                  document.getElementById(`${sectionId}-section`).classList.add('active');
                }
              });
            });
</script>
<script src="sidebarnav.js"></script>
</html>