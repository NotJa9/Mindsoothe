<div id="chats-section" class="section">
    <div class="flex h-screen">
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
</div>