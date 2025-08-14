<div id="settings" class="page hidden">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Settings</h1>

            <div class="bg-white rounded-2xl shadow-lg">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <button class="settings-tab active py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium" onclick="showSettingsTab('profile')">Profile</button>
                        <button class="settings-tab py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium" onclick="showSettingsTab('notifications')">Notifications</button>
                        <button class="settings-tab py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium" onclick="showSettingsTab('preferences')">Preferences</button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Profile Tab -->
                    <div id="profile-tab" class="settings-content">
                        <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
                        <form class="space-y-4">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" value="John" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" value="Doe" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" value="john.doe@example.com" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                <textarea rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Tell us about yourself..."></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="settings-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Notification Preferences</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h4 class="font-medium">Task Assignments</h4>
                                    <p class="text-sm text-gray-600">Get notified when tasks are assigned to you</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h4 class="font-medium">Sprint Updates</h4>
                                    <p class="text-sm text-gray-600">Receive updates about sprint progress</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences Tab -->
                    <div id="preferences-tab" class="settings-content hidden">
                        <h3 class="text-lg font-semibold mb-4">Application Preferences</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h4 class="font-medium">Dark Mode</h4>
                                    <p class="text-sm text-gray-600">Switch to dark theme</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="darkModeToggle" class="sr-only peer" onchange="toggleDarkMode()">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
