<?php
require_once __DIR__ . '/../../config/database.php'; // your PDO connection
require_once __DIR__ . '/../models/Department.php';

$departmentModel = new Department($pdo);
$departments = $departmentModel->getAllDepartments();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kardex Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#5EACDE'
                    }
                }
            }
        }
    </script>
</head>

<body class="h-screen flex bg-gray-100 relative overflow-hidden">
    <!-- Sidebar Backdrop (Mobile Only) -->
    <div id="backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300 md:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-[#5EACDE] text-white shadow-md p-4 fixed top-0 left-0 h-full z-40 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out">
        <!-- Close Button (Mobile Only) -->
        <button id="closeBtn" class="md:hidden mb-4 text-white focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <h1 class="text-xl font-bold mb-6">Admin Panel</h1>
        <nav class="space-y-2">
            <button class="w-full text-left px-4 py-2 rounded hover:bg-white/20" onclick="showSection('nurses')">View Nurses</button>
            <button class="w-full text-left px-4 py-2 rounded hover:bg-white/20" onclick="showSection('patients')">View Patients</button>
            <button class="w-full text-left px-4 py-2 rounded hover:bg-white/20" onclick="showSection('beds')">Manage Beds</button>
            <button class="w-full text-left px-4 py-2 rounded hover:bg-white/20" onclick="showSection('departments')">Manage Departments</button>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 overflow-auto w-full md:ml-0">
        <section id="nurses" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <button class="hamburger-btn md:hidden text-white bg-primary p-2 rounded focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-2xl font-semibold text-primary ml-2">Nurses</h2>
            </div>
            <label class="block mb-2">Filter by Department:</label>
            <select class="mb-4 p-2 border rounded">
                <option>All</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                        <?= htmlspecialchars($dept['deptname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="bg-white p-4 shadow rounded overflow-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="px-4 py-4 uppercase">Full Name</th>
                            <th class="px-4 py-4 uppercase">Email</th>
                            <th class="px-4 py-4 uppercase">Assigned Department</th>
                            <th class="px-4 py-4 uppercase">Contact</th>
                            <th class="px-4 py-4 uppercase">Created At</th>
                            <th class="px-4 py-4 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="nurseTableBody" class="text-gray-700">
                        <!-- Dynamic rows will go here -->
                    </tbody>
                </table>
            </div>

        </section>

        <section id="patients" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <button class="hamburger-btn md:hidden text-white bg-primary p-2 rounded focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-2xl font-semibold text-primary ml-2">Patients</h2>
            </div>
            <label class="block mb-2">Filter by Department:</label>
            <select class="mb-4 p-2 border rounded">
                <option>All</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                        <?= htmlspecialchars($dept['deptname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <table class="min-w-full text-sm text-left">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="px-4 py-4 uppercase">Full Name</th>
                        <th class="px-4 py-4 uppercase">Gender</th>
                        <th class="px-4 py-4 uppercase">Physician</th>
                        <th class="px-4 py-4 uppercase">Bed Assigned</th>
                        <th class="px-4 py-4 uppercase">Admission Date/Time</th>
                        <th class="px-4 py-4 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientBody" class="bg-white p-4 shadow rounded">
                    <!-- Dynamic rows will go here -->
                </tbody>
            </table>

        </section>

        <section id="beds" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <button class="hamburger-btn md:hidden text-white bg-primary p-2 rounded focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-2xl font-semibold text-primary ml-2">Beds Management</h2>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <label class="block mb-1 text-sm text-gray-700">Filter by Department:</label>
                    <select id="bedDepartmentFilter" class="p-2 border rounded w-48">
                        <option>All</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                <?= htmlspecialchars($dept['deptname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button onclick="openBedModal()"
                    class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-200">
                    Add New Bed
                </button>
            </div>

            <div class="bg-white p-4 shadow rounded overflow-auto">
                <table class="min-w-full text-md text-left">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="px-4 py-4">Bed Number</th>
                            <th class="px-4 py-4">Department</th>
                            <th class="px-4 py-4">Status</th>
                            <th class="px-4 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bedTableBody" class="text-gray-700">
                        <!-- Dynamic bed rows go here -->
                    </tbody>
                </table>
            </div>
        </section>

        <section id="departments" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <button class="hamburger-btn md:hidden text-white bg-primary p-2 rounded focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="text-2xl font-semibold text-primary ml-2">Departments</h2>
            </div>
            <form class="mb-4">
                <input type="text" placeholder="Department Name" class="p-2 border rounded mr-2" />
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Add Department</button>
            </form>
            <div class="bg-white p-4 shadow rounded">List of departments with options to edit/delete...</div>
        </section>

        <!-- Modal Background -->
        <div id="bedModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
                <h3 class="text-lg font-semibold mb-4 text-primary">Add New Bed</h3>
                <form id="bedForm" novalidate>
                    <div class="mb-3">
                        <label class="block mb-2 text-sm">Bed Number</label>
                        <input type="number" id="bedNumberInput" class="w-full p-2  border rounded" required>
                    </div>

                    <div class="mb-3">
                        <label class="block mb-2 text-sm">Department</label>
                        <select id="departmentSelect" class="w-full p-2 border rounded" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                    <?= htmlspecialchars($dept['deptname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeBedModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-600">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>


    <!-- Scripts -->
    <script src="/kardex_system/src/public/js/validationUtils.js"></script>
    <script src="../public/js/adminPanelJs/managePatients.js"></script>
    <script src="../public/js/adminPanelJs/manageNurses.js"></script>
    <script src="../public/js/adminPanelJs/manageBeds.js"></script>
    <script src="../public/js/adminPanelJs/manageDepartments.js"></script>
    <script src="../public/js/adminPanelJs/adminUtils.js"></script>
</body>


</html>