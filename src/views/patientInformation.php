<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php'; // your PDO connection
require_once __DIR__ . '/../models/Department.php';

if (!isset($_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['user_id'])) {
    http_response_code(401);
    header("Location: /kardex_system/src/views/login.php");
}


$departmentModel = new Department($pdo);
$departments = $departmentModel->getAllDepartments();

$fullName = $_SESSION['lastname'] . ', ' . $_SESSION['firstname'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Patient Detail View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    spacing: {
                        'sidebar': '350px'
                    },
                    zIndex: {
                        'overlay': '40'
                    }
                }
            }
        }
    </script>
</head>
<script>
    const user_id = <?php echo json_encode($_SESSION['user_id']); ?>;
</script>

<body class="h-screen relative overflow-hidden">
    <!-- BACKDROP -->
    <div id="passcodeBackdrop" class="fixed inset-0 bg-black bg-opacity-50 z-[59] hidden"></div>
    <!-- PASSCODE POPUP -->
    <div id="passcodeContainer" class="fixed inset-0 flex items-center justify-center z-[60] hidden"></div>


    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-overlay" onclick="closeSidebar()"></div>
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-sky-700 text-white w-sidebar p-4 space-y-4 hidden md:block fixed md:static z-50 h-full md:h-auto transition-transform transform md:translate-x-0 -translate-x-full">

            <div class="flex justify-between items-center">
                <div class="flex justify-between items-center w-full">
                    <h2 id="displayName" class="text-3xl font-bold"></h2>
                    <img id="editPatientProfile" class="w-12 h-12 p-2 border-round border border-2 border-white rounded-[15px] hover:scale-[1.02] cursor-pointer" src="/kardex_system/src/public/images/edit-profile.svg">
                </div>
                <button class="md:hidden text-white text-2xl font-bold" onclick="closeSidebar()">&times;</button>
            </div>
            <div class="mt-4">
                <p><strong>BED #:</strong></p>
                <p><strong>Age:</strong></p>
                <p><strong>Sex:</strong></p>
                <p><strong>Status:</strong></p>
                <p><strong>Date of Birth:</strong></p>
                <p><strong>Nationality:</strong></p>
                <p><strong>Religion:</strong></p>
                <p><strong>Date of Admission:</strong></p>
                <p><strong>Time of Admission:</strong></p>
                <p><strong>Physician:</strong></p>
                <p><strong>Diagnosis:</strong></p>
            </div>
            <a href="endorsement.php"><button class="mt-8 bg-white text-sky-700 font-bold px-4 py-2 rounded-md">Back</button></a>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-auto w-full">
            <!-- Top Bar -->
            <header class="bg-sky-500 text-white p-4 flex justify-between items-center sticky top-0 shadow-md z-20">
                <div class="flex items-center">
                    <button class="md:hidden text-white mr-4" onclick="toggleSidebar()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="font-bold text-lg">🔔 A note has been assigned to you</span>
                </div>
                <button class="bg-white text-sky-500 px-4 py-1 rounded-md font-bold">Logout</button>
            </header>

            <!-- Scrollable Main Content -->
            <div id="mainContent" class="grid grid-cols-4 gap-0 mt-3 border border-black text-sm w-full max-w-4xl mx-auto [grid-auto-rows:minmax(0,auto)]">
                <!-- TREATMENTS + REFERRALS WRAPPED TOGETHER -->
                <div class="col-span-1 flex flex-col border border-black" style="height: 800px;">
                    <!-- TREATMENTS (Top - 550px) -->
                    <div class="h-[550px] border-b border-black overflow-y-auto">
                        <div class="border-b-2 p-1 border-black sticky top-0 bg-white flex justify-between items-center">
                            <span class="font-bold">TREATMENTS</span>
                            <img id="addTreatmentRow" class="w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out"
                                src="../public/images/edit.svg" alt="edit-treatments">
                        </div>
                        <div id="treatmentDetails" class="text-justify flex justify-center flex-col items-center">
                        </div>
                    </div>

                    <!-- REFERRALS (Bottom - 250px) -->
                    <div class="h-[250px] border-b border-black">
                        <div class="border-b-2 p-1 border-black sticky top-0 bg-white flex justify-between items-center">
                            <span class="font-bold">REFERRALS</span>
                            <img id="openReferrals"
                                class="w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out"
                                src="../public/images/edit.svg"
                                alt="edit-referrals">
                        </div>

                        <!-- Vertical layout: each field 50% -->
                        <div class="p-2 h-[calc(100%-2rem)] flex flex-col justify-between space-y-2">
                            <!-- DEPT Dropdown - takes 50% -->
                            <div class="h-[30%] flex flex-col">
                                <label for="deptSelect" class="text-sm font-medium">DEPT:</label>
                                <select id="deptSelect"
                                    class="w-full mt-1 py-2 text-sm h-full rounded focus:outline-none focus:ring-0 focus:border-transparent">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                            <?= htmlspecialchars($dept['deptname']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- RE Textarea - takes 50% -->
                            <div class="h-[70%] flex flex-col">
                                <label for="reason" class="text-sm font-medium">RE:</label>
                                <textarea id="reason"
                                    class="w-full mt-1 p-2 text-sm h-full resize-none overflow-y-auto overflow-x-hidden  rounded focus:outline-none focus:ring-0 focus:border-transparent"></textarea>
                            </div>
                        </div>
                    </div>


                </div>
                <script>
                    function lockPrefix(textarea, prefix) {
                        if (!textarea.value.startsWith(prefix)) {
                            textarea.value = prefix;
                        }
                    }
                </script>





                <!-- ATTACHMENTS Combined Block -->
                <div class="col-span-1 border-b border-black h-[800px] flex flex-col">
                    <!-- Top section (200px) -->
                    <div class="h-[200px] border-b border-t border-black  overflow-auto">
                        <div class="border-b-2 p-1 bg-white z-10 border-black sticky top-0 bg-white flex justify-between items-center">
                            <span class="font-bold ">ATTACHMENTS</span>
                            <img id="addAttachmentTopRow" class="w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out"
                                src="../public/images/edit.svg" alt="edit-treatments">
                        </div>
                        <div id="attachmentTopDetails" class="text-justify flex justify-center flex-col items-center w-full overflow-auto">
                            <!-- Content goes here -->
                        </div>
                    </div>


                    <!-- Bottom section (600px) -->
                    <div class="h-[600px] border-t border-b border-black overflow-y-auto">
                        <div class="border-b-2 p-1 border-black sticky top-0 bg-white flex justify-end items-center">
                            <img id="addAttachmentBottomRow" class="w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out"
                                src="../public/images/edit.svg" alt="edit-treatments">
                        </div>
                        <div id="attachmentBottomDetails" class="text-justify flex justify-center flex-col items-center">
                            <!-- More content goes here -->
                        </div>
                    </div>
                </div>

                <!-- IVF/INFUSIONS WRAPPER - Spans same columns but stacks two tables vertically -->
                <div class="col-span-2 col-start-3 flex flex-col h-[800px] overflow-auto">
                    <!-- First IVF Table -->
                    <div class="border border-black h-[400px] overflow-auto">
                        <div class="border-b-2 p-1 border-black sticky top-0 bg-white flex justify-center items-center">
                            <span class=" font-bold">IVF/INFUSIONS</span>
                            <img id="openInfusions" class=" ml-auto w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out" src="../public/images/edit.svg" alt="edit-treatments">
                        </div>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="border-b border-r border-black p-1">DATE</th>
                                    <th class="border-b border-r border-black p-1">BOTTLE NO.</th>
                                    <th class="border-b border-r border-black p-1">IVF</th>
                                    <th class="border-b border-black p-1">RATE</th>
                                </tr>
                            </thead>
                            <tbody id="infusionTableBody">
                                <tr id="addRow" class="border-b border-black h-6">
                                    <td colspan="4" class="text-center">
                                        <img class="w-8 h-8 mx-auto cursor-pointer hover:scale-105 transition duration-200"
                                            src="../public/images/add.svg"
                                            alt="Add new row"
                                            title="Add new IVF/Infusions"
                                            onclick="addInfusionRow('infusionTableBody', 'addRow')">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Second IVF Table -->
                    <div class="border border-black h-[400px] overflow-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="border-b border-r border-black p-1">DATE</th>
                                    <th class="border-b border-r border-black p-1">BOTTLE NO.</th>
                                    <th class="border-b border-r border-black p-1">IVF</th>
                                    <th class="border-b border-black p-1">RATE</th>
                                </tr>
                            </thead>
                            <tbody id="infusionTableBody2">
                                <tr id="addRow2" class="border-b border-black h-6">
                                    <td colspan="4" class="text-center">
                                        <img class="w-8 h-8 mx-auto cursor-pointer hover:scale-105 transition duration-200"
                                            src="../public/images/add.svg"
                                            alt="Add new row"
                                            title="Add new IVF/Infusions"
                                            onclick="addInfusionRow('infusionTableBody2', 'addRow2')">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PROCEDURES/OPERATIONS -->
                <div class="col-span-1 border border-black min-h-[800px]">
                    <div class="h-12 border-b-2 p-1 border-black sticky top-0 bg-white flex justify-center items-center">
                        <span class=" font-bold">PROCEDURES/OPERATIONS</span>
                        <img id="addProcedureRow" class=" ml-auto w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out" src="../public/images/edit.svg" alt="edit-treatments">
                    </div>
                    <div id="procedureDetails" class="text-justify flex justify-center flex-col items-center">
                        <!-- More content goes here -->
                    </div>
                </div>

                <!-- DATE CONTRAPTIONS STARTED -->
                <div class="col-span-1 border border-black min-h-[150px]">
                    <div class="h-12 border-b-2 px-1 border-black sticky top-0 bg-white flex justify-center items-center">
                        <span class=" font-bold">DATE CONTRAPTIONS STARTED</span>
                        <img id="addContraptionDateRow" class=" ml-auto w-6 h-6 hover:scale-[1.05] cursor-pointer transition duration-200 ease-in-out" src="../public/images/edit.svg" alt="edit-treatments">
                    </div>
                    <div id="dateContraptionDetails" class="text-justify flex justify-center flex-col items-center">
                        <!-- More content goes here -->
                    </div>
                </div>

                <!-- LABORATORY / DIAGNOSTIC TABLE -->
                <div class="col-span-2 border border-black overflow-x-auto min-h-[200px]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="h-12 border-b border-r border-black p-1">DATE</th>
                                <th class="h-12 border-b border-r border-black p-1">LABORATORY / DIAGNOSTIC</th>
                                <th class="h-12 border-b border-r border-black p-1">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="laboratoryBody">
                            <tr id="addLaboratoryRow" class="border-b border-black h-6">
                                <td colspan="4" class="text-center">
                                    <img class="w-8 h-8 mx-auto cursor-pointer hover:scale-105 transition duration-200"
                                        src="../public/images/add.svg"
                                        alt="Add new row"
                                        title="Add new Laboratory/Diagnostic"
                                        onclick="addLaboratoryRow()">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Note Popup -->
            <div id="noteContent" class="hidden absolute top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-30 z-[60]">
                <div class="bg-white border-2 border-gray-400 rounded-xl shadow-2xl w-[80%] max-w-4xl p-6 relative">

                    <!-- Header -->
                    <div class="flex justify-between items-center mb-4">
                        <span class="bg-blue-200 text-blue-800 text-lg font-semibold px-4 py-1 rounded-lg">Notes</span>
                        <button onclick="closeNotePopup()" class="text-xl text-gray-600 hover:text-gray-800">←</button>
                    </div>

                    <!-- Note Content -->
                    <div class="border-[3px] border-blue-500 rounded-lg p-6 mb-6 min-h-[160px]">
                        <p class="text-gray-800 mb-4">
                            Was not able to give px paracetamol due to no stocks. Please follow-up with pharmacy and give to px once available
                        </p>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <img src="https://via.placeholder.com/32" alt="Avatar" class="rounded-full mr-2 w-8 h-8" />
                                <span class="font-semibold text-gray-700">Balbuena, Mark Paul</span>
                            </div>
                            <div class="flex items-center space-x-1 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>2</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Button -->
                    <div class="text-right">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded flex items-center gap-1">
                            ↩️ Reply
                        </button>
                    </div>

                </div>
            </div>




            <!-- Footer -->
            <footer class="bg-white border-t border-black p-4 flex justify-between items-center sticky bottom-0 z-20">
                <button class="bg-gray-200 text-black px-4 py-2 rounded-md" onclick="showNotePopup()">Notes</button>
                <button id="saveBtn" class="bg-sky-500 text-white font-bold px-6 py-2 rounded-md hover:bg-sky-600 transition">Save</button>
            </footer>
        </div>



        <script>
            function showNotePopup() {
                document.getElementById('mainContent').classList.add('hidden');
                document.getElementById('noteContent').classList.remove('hidden');
            }

            function closeNotePopup() {
                document.getElementById('noteContent').classList.add('hidden');
                document.getElementById('mainContent').classList.remove('hidden');
            }
        </script>
    </div>

    <div id="errorModal" class="fixed inset-0 z-[100] hidden bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl text-center shadow-lg w-[90%] max-w-md animate-fadeScaleIn border-4 border-red-500 ">
            <div class="flex justify-center mb-4">
                <img src="/kardex_system/src/public/images/error-icon.png" alt="Error Icon" class="w-12 h-12" />
            </div>
            <h2 class="text-lg sm:text-3xl font-semibold text-red-600">Passcode is incorrect, please try again.</h2>
            <button
                onclick="closeErrorModal()"
                class="mt-6 px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 hover:shadow-xl transform hover:scale-105 transition duration-300">OK</button>
        </div>
    </div>

    <div id="successModal" class="fixed inset-0 z-[100] hidden bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl text-center shadow-lg w-[90%] max-w-md animate-fadeScaleIn border-4 border-green-500">
            <div class="flex justify-center mb-4">
                <img src="/kardex_system/src/public/images/success-icon.png" alt="Success Icon" class="w-13 h-12" />
            </div>
            <h2 class="text-lg sm:text-3xl font-semibold text-green-600">Changes saved Succesfully!</h2>
            <button
                onclick="closeSuccessModal()"
                class="mt-6 px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 hover:shadow-xl transform hover:scale-105 transition duration-300 ">OK</button>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <!-- Preview Modal -->
    <div id="imagePreviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-4 rounded shadow-md max-w-md w-full">
            <img id="previewImage" class="w-full h-auto object-contain" src="" alt="Image Preview" />
            <span id="previewFileName" class="text-center text-gray-700 text-sm font-medium"></span>
            <button onclick="closeImagePreview()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Close</button>
        </div>
    </div>
    <!-- Upload Popup -->
    <div id="uploadPopup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30">
        <div class="bg-white p-10 rounded-md shadow-md w-[500px] max-h-[80vh] overflow-y-auto">

            <!-- Title + Add Row Button -->
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-bold">Upload Attachment</h2>
                <button
                    id="addRowUploadPopup"
                    class="text-sm bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">
                    + Row
                </button>
            </div>

            <!-- Editable rows container -->
            <div id="popupAttachmentRows" class="mt-4 pb-8 border-t border-black space-y-4">

                <!-- Repeat this block for more file inputs -->
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-2">
                <button
                    onclick="closeUploadPopup(false)"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                    Ok
                </button>
                <button
                    onclick="closeUploadPopup(true)"
                    class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Popup -->
    <div id="editPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-30 hidden">
        <div class="bg-white p-10 rounded-md shadow-md w-[500px] max-h-[80vh] overflow-y-auto">
            <!-- Title + Add Row Button -->
            <div class="flex justify-between items-center mb-2">
                <h2 id="popupTitle" class="text-lg font-bold "></h2>
                <button
                    id="addRowInputPopup"
                    class="text-sm bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">
                    + Row
                </button>
            </div>

            <!-- Editable rows container -->
            <div id="popupInputRows" class="mt-4 pb-8 border-t border-black">
                <input type="text" class="w-full  border border-black p-2 focus:outline-none focus:ring-0" />
                <input type="text" class="w-full border-b border-l border-r border-black p-2 focus:outline-none focus:ring-0" />
                <input type="text" class="w-full border-b border-l border-r border-black p-2 focus:outline-none focus:ring-0" />
                <input type="text" class="w-full border-b border-l border-r border-black p-2 focus:outline-none focus:ring-0" />
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end space-x-2">
                <button
                    onclick="closePopup(false)"
                    class="bg-sky-500 text-white px-4 py-2 rounded hover:bg-sky-600 transition">
                    Ok
                </button>
                <button
                    onclick="closePopup(true)"
                    class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Save Changes Modal -->
    <div id="popupConfirmChanges" class="fixed inset-0 z-50 bg-black hidden bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-8 rounded-xl text-center shadow-lg w-[90%] max-w-md animate-fadeScaleIn border-4 border-[#5eacdd] ">
            <h2 class="text-lg sm:text-3xl text-[#5eacdd] font-semibold border-[#5eacdd]">Save Changes?</h2>
            <div class="flex justify-center space-x-4">
                <button id="saveChanges"
                    class="mt-6 px-6 py-2 bg-[#5eacdd] text-white rounded-lg hover:bg-[#2320d8] hover:shadow-xl transform hover:scale-105 transition duration-300">CONFIRM</button>
                <button id="cancelChanges"
                    class="mt-6 px-6 py-2 bg-[#5eacde] text-white rounded-lg hover:bg-[#2320d8] hover:shadow-xl transform hover:scale-105 transition duration-300">CANCEL</button>
            </div>
        </div>
    </div>

    <!-- Edit Profile Popup -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-xl space-y-4 relative">
            <h2 class="text-xl font-bold mb-4">Edit Patient Profile</h2>
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">BED #:</label>
                    <input type="text" id="editBedNumber" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Gender:</label>
                    <select id="editGender" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Non-binary">Non-binary</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                        <option value="Other">Other</option>
                    </select>
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Status:</label>
                    <select id="editStatus" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                        <option value="">Select Civil Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Separated">Separated</option>
                        <option value="Annulled">Annulled</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                        <option value="Other">Other</option>
                    </select>
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Date of Birth:</label>
                    <input type="date" id="editDOB" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Nationality:</label>
                    <input type="text" id="editNationality" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Religion:</label>
                    <input type="text" id="editReligion" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Physician:</label>
                    <input type="text" id="editPhysician" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="w-48 font-semibold">Diagnosis:</label>
                    <input type="text" id="editDiagnosis" disabled class="flex-1 border px-2 py-1 rounded-md disabled:bg-gray-100">
                    <button onclick="enableField(this)" class="text-blue-600">✏️</button>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button onclick="closeEditProfileModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-4 py-2 rounded">
                        Cancel
                    </button>
                    <button onclick="saveEditedProfile()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                        Okay
                    </button>
                </div>
            </div>

        </div>
        <script>
            function enableField(button) {
                const input = button.previousElementSibling;
                input.disabled = false;
                input.classList.remove('bg-gray-100');
            }

            function closeEditProfileModal() {
                document.getElementById('editProfileModal').classList.add('hidden');
            }

            function saveEditedProfile() {
                // Optional: Add logic to gather and save the edited data
                alert('Profile saved successfully!');
                closeEditProfileModal();
            }
        </script>


        <!-- Flowbite (for example) -->

        <script src="/kardex_system/src/public/js/utils.js"></script>
        <script src="/kardex_system/src/public/js/imageUpload.js"></script>

        <script src="/kardex_system/src/public/js/patientInformationHandler.js"></script>
        <script src="/kardex_system/src/public/js/patientInformationJs/editInformation.js"></script>
        <script src="/kardex_system/src/public/js/patientInformationJs/treatmentHandler.js"></script>
        <script src="/kardex_system/src/public/js/patientInformationJs/loadPatientInformation.js"></script>
        <script src="/kardex_system/src/public/js/patientInformationJs/submitChanges.js"></script>
        <script src="/kardex_system/src/public/js/passcode.js"></script>
</body>

</html>