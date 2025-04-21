loadPatientInformation();
let deletedFiles = [];


function fillUpDetails(elementId, details, inputType = 'text') {
    const container = document.getElementById(elementId);
    
    container.innerHTML = ""; // Clear existing inputs if needed

    if (Array.isArray(details) && details.length !== 0) {
        details.forEach(value => {

            const input = document.createElement("input");
            input.type =  (inputType === 'date') ? 'date' : 'text';
            input.value = value;

            input.className = "w-full border-b border-black px-2 focus:outline-none focus:ring-0";
            container.appendChild(input);
        });
    }    
}

function fillUpAttachments(fileList = []) {
    const topContainer = document.getElementById("attachmentTopDetails");
    const bottomContainer = document.getElementById("attachmentBottomDetails");

    // Clear previous content
    topContainer.innerHTML = "";
    bottomContainer.innerHTML = "";
    fileList.forEach(file => {
        const wrapper = document.createElement("div");
        wrapper.className = "flex items-center justify-between w-full border-b border-black px-1";
    
        const fileName = document.createElement("span");
        fileName.className = "truncate max-w-[150px]";
        fileName.textContent = file.file_name;
    
        const controls = document.createElement("div");
        controls.className = "flex items-center gap-1";
    
        // Eye icon
        const previewIcon = document.createElement("img");
        previewIcon.src = '/kardex_system/src/public/images/eye.svg';
        previewIcon.className = "w-4 h-4 cursor-pointer";
        previewIcon.dataset.image = file.file_path;
        previewIcon.onclick = () => previewImage(previewIcon);
    
        // Remove button (X)
        const removeBtn = document.createElement("button");
        removeBtn.textContent = "✖";
        removeBtn.className = "text-xs text-red-500 hover:text-red-700 cursor-pointer";
        removeBtn.onclick = () => {
            deletedFiles.push(file); // Track this for backend deletion
            wrapper.remove(); // Remove visually
        };
    
        // Append icons to control div
        controls.appendChild(previewIcon);
        controls.appendChild(removeBtn);
    
        // Final assembly
        wrapper.appendChild(fileName);
        wrapper.appendChild(controls);
    
        // Append to the correct container
        if (file.display_table == '1') {
            topContainer.appendChild(wrapper);
        } else if (file.display_table == '2') {
            bottomContainer.appendChild(wrapper);
        }
    });
    
    
}


function fillInfusionTable(dataArray, infusionBodyId, addRowId) {
    const tbody = document.getElementById(infusionBodyId);
    const addRow = document.getElementById(addRowId);

    // Clear all but the add row
    tbody.innerHTML = '';
    tbody.appendChild(addRow);

    dataArray.forEach(infusion => displayInfusionRow(infusion, infusionBodyId, addRowId));
}

function displayInfusionRow(data, infusionBodyId, addRowId) {
    const tbody = document.getElementById(infusionBodyId);
    const addRow = document.getElementById(addRowId);

    const newRow = document.createElement('tr');
    newRow.className = "border-b border-black";

    newRow.innerHTML = `
        <td class="p-1 border-r border-black">
            <input type="date" class="w-full focus:outline-none focus:ring-0" value="${data.date || ''}" />
        </td>
        <td class="p-1 border-r border-black">
            <input type="text" class="w-full focus:outline-none focus:ring-0" value="${data.bottle_no || ''}" />
        </td>
        <td class="p-1 border-r border-black">
            <input type="text" class="w-full focus:outline-none focus:ring-0"  value="${data.ivf || ''}" />
        </td>
        <td class="p-1 border-black">
            <input type="text" class="w-full focus:outline-none focus:ring-0"  value="${data.rate || ''}" />
        </td>
    `;

    tbody.insertBefore(newRow, addRow);
}


function fillLaboratoryTable(dataArray, laboratoryBodyId, addRowId) {
    const tbody = document.getElementById(laboratoryBodyId);
    const addRow = document.getElementById(addRowId);

    // Clear all but the add row
    tbody.innerHTML = '';
    tbody.appendChild(addRow);

    dataArray.forEach(lab => displayLaboratoryRow(lab, laboratoryBodyId, addRowId));
}

function displayLaboratoryRow(data, laboratoryBodyId, addRowId) {
    const tbody = document.getElementById(laboratoryBodyId);
    const addRow = document.getElementById(addRowId);
    console.log(data);
    const newRow = document.createElement('tr');
    newRow.className = "border-b border-black";

    newRow.innerHTML = `
        <td class="p-1 border-r border-black">
            <input type="date" class="w-full focus:outline-none focus:ring-0" value="${data.procedure_date || ''}" />
        </td>
        <td class="p-1 border-r border-black">
            <input type="text" class="w-full focus:outline-none focus:ring-0" value="${data.laboratory_diagnostic || ''}" />
        </td>
        <td class="p-1 border-black">
            <input type="text" class="w-full focus:outline-none focus:ring-0" value="${data.status || ''}" />
        </td>
    `;

    tbody.insertBefore(newRow, addRow);
}


async function loadPatientInformation() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    try {
        const treatmentRequest = fetch(`/kardex_system/src/routes/index.php/treatments/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const referralRequest = fetch(`/kardex_system/src/routes/index.php/referrals/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const attachmentRequest = fetch(`/kardex_system/src/routes/index.php/attachments/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const infusionRequest = fetch(`/kardex_system/src/routes/index.php/infusions/display_table1/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const infusion2Request = fetch(`/kardex_system/src/routes/index.php/infusions/display_table2/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const procedureRequest = fetch(`/kardex_system/src/routes/index.php/procedures/${id}`, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });


        const [treatmentResult, referralResult, attachmentResult, infusionResult, infusion2Result, procedureResult ] = await Promise.allSettled([treatmentRequest, 
                                                                                                referralRequest,
                                                                                               attachmentRequest, 
                                                                                               infusionRequest, 
                                                                                               infusion2Request,
                                                                                               procedureRequest]);

                                                                      
        
        // 🔹 TREATMENT
        let treatment = null;
        if (treatmentResult.status === "fulfilled") {
            treatment = await treatmentResult.value.json();  
            if (Array.isArray(treatment) && treatment.length > 0) {
                const rawTreatments = treatment[0].treatment_text; 
                loadDataToForm(rawTreatments, 'treatmentDetails');
            }
        } else {
            console.error("Failed to load treatment:", treatmentResult.reason);
        }

        if (referralResult.status === "fulfilled") {
            const referrals = await referralResult.value.json();
            if (Array.isArray(referrals) && referrals.length > 0) {
                document.getElementById('deptSelect').value = referrals[0].department_id;
                document.getElementById('reason').value = referrals[0].reason;
            } else {
                console.warn("No referral data found.");
                // Optionally clear fields
                document.getElementById('deptSelect').value = '';
                document.getElementById('reason').value = '';
            }
        } else {
            console.error("Failed to load referrals:", referralResult.reason);
        }
        
        // 🔹 ATTACHMENTS
        if (attachmentResult.status === "fulfilled") {
            const attachments = await attachmentResult.value.json();
            topAttachmentDbFiles = attachments
            fillUpAttachments(attachments);
        } else {
            console.error("Failed to load attachments:", attachmentResult.reason);
        }

        console.log('PROCEDURE RESULT' + procedureResult)
            // 🔹 PROCEDURES
        let procedures = null;
        if (procedureResult.status === "fulfilled") {
            procedures = await procedureResult.value.json(); 
            if (Array.isArray(procedures) && procedures.length > 0) {
                const procedureNames = procedures.map(p => p.procedure_name);
                const startDates = procedures.map(p => p.contraption_start_date);
                const laboratoryDetails = procedures.map(p => {
                    console.log(p);
                    return {
                        laboratory_diagnostic: p.laboratory_diagnostic || '',
                        procedure_date: p.procedure_date || '',
                        status: p.status || ''
                    };
                });
                
                
        

                loadDataToForm(procedureNames, 'procedureDetails');
                fillLaboratoryTable(laboratoryDetails, 'laboratoryBody', 'addLaboratoryRow');
                loadDataToForm(startDates, 'dateContraptionDetails', 'date');
            }
        } else {
            console.error("Failed to load procedures:", procedureResult.reason);
        }



        // INFUSIONS
        if (infusionResult.status === "fulfilled") {
            const infusionData = await infusionResult.value.json();
            console.log("Infusion data:", infusionData);

            if (Array.isArray(infusionData) && infusionData.length > 0) {
                fillInfusionTable(infusionData, 'infusionTableBody', 'addRow');
            } else {
                console.log("No infusion data found.");
            }
        } else {
            console.error("Failed to load infusions:", infusionResult.reason);
        }

        if (infusion2Result.status === "fulfilled") {
            const infusion2Data = await infusion2Result.value.json();
            console.log("Infusion data:", infusion2Data);

            if (Array.isArray(infusion2Data) && infusion2Data.length > 0) {
                fillInfusionTable(infusion2Data, 'infusionTableBody2', 'addRow2');
            } else {
                console.log("No infusion data found.");
            }
        } else {
            console.error("Failed to load infusions:", infusion2Result.reason);
        }


        return true;
    } catch (err) {
        console.error("Unexpected error:", err);
        return false;
    }
}
async function loadDataToForm(data, category, inputType = 'text') {
    console.log("Received:", data);
    
    let parsed = [];

    try {
        parsed = Array.isArray(data) ? data : JSON.parse(data);
    } catch (e) {
        console.warn("Failed to parse JSON, using raw data:", data);
        parsed = [data];
    }

    // 🔥 Flatten if it's a 2D array
    if (Array.isArray(parsed) && parsed.every(item => Array.isArray(item))) {
        parsed = parsed.flat();
    }

    if (Array.isArray(parsed)) {
        fillUpDetails(category, parsed, inputType);
    }
}
