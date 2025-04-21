document.getElementById('saveBtn').addEventListener('click', () => {
    document.getElementById("popupConfirmChanges").classList.remove("hidden");
    document.getElementById("popupConfirmChanges").classList.add("flex");
});

document.getElementById('saveChanges').addEventListener('click', () => {
    document.getElementById("popupConfirmChanges").classList.add("hidden");
    showPasscodeUI('Enter Passcode', "confirmPasscode");
});
document.getElementById('cancelChanges').addEventListener('click', () => {
    document.getElementById("popupConfirmChanges").classList.add("hidden");
});

function getCurrentDetails(elementId) {
    console.log(elementId);
    const container = document.getElementById(elementId);
    const inputs = container.querySelectorAll('input');
    const values = Array.from(inputs)
    .map(input => input.value?.trim())
    .filter(value => value && value !== "");
   
    console.log(values);
    return values;
}


function getFileUploadDetails(category) {
    const container = document.getElementById(category);
    const inputs = container.querySelectorAll('input[type="file"]');

    const files = Array.from(inputs)
    .map(input => input.files[0])  // Grab the first selected file
    .filter(file => file);   

    console.log(files); // Array of File objects
    return files;
}

function getEditedProfileDetails() {
    return {
     // IT DOESNT MATCH THE MODEL SINCE NO NAME, ETC
      bedNumber: document.getElementById("editBedNumber").value.trim(),
      gender: document.getElementById("editGender").value,
      status: document.getElementById("editStatus").value,
      date_of_birth: document.getElementById("editDOB").value,
      nationality: document.getElementById("editNationality").value.trim(),
      religion: document.getElementById("editReligion").value.trim(),
      physician: document.getElementById("editPhysician").value.trim(),
      diagnosis: document.getElementById("editDiagnosis").value.trim()
    };
  }
  
function getInfusionData(infusionBody, rowId) {
    const tbody = document.getElementById(infusionBody);
    const rows = Array.from(tbody.querySelectorAll('tr'));

    const infusionData = [];

    rows.forEach(row => {
        // Skip the last "Add Row"
        if (row.id === rowId) return;

        const inputs = row.querySelectorAll('input');

        infusionData.push({
            date: inputs[0]?.value || '',
            bottle_no: inputs[1]?.value.trim() || '',
            ivf: inputs[2]?.value.trim() || '',
            rate: inputs[3]?.value.trim() || ''
        });
    });

    return infusionData;
}
function getLaboratoryData() {
    const tbody = document.getElementById('laboratoryBody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    const laboratoryData = [];

    rows.forEach(row => {
        // Skip the last "Add Row"
        if (row.id === 'addLaboratoryRow') return;

        const inputs = row.querySelectorAll('input, select');

        laboratoryData.push({
            date: inputs[0]?.value || '',
            diagnostic: inputs[1]?.value.trim() || '',
            status: inputs[2]?.value.trim() || ''
        });
    });

    return laboratoryData;
}
async function submitChanges(){
    console.log("SUBMIT");
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    const treatmentContent = getCurrentDetails('treatmentDetails');
    const attachment1 = getFileUploadDetails('attachmentTopDetails');
    const attachment2 = getFileUploadDetails('attachmentBottomDetails');
    const infusionContent = getInfusionData('infusionTableBody', 'addRow');
    const infusion2Content = getInfusionData('infusionTableBody2', 'addRow2');
    const procedureContent = getCurrentDetails('procedureDetails');
    const dateContraptionContent = getCurrentDetails('dateContraptionDetails');
    const laboratoryContent = getLaboratoryData();
    const refferedDept = document.getElementById('deptSelect').value;
    const reason = document.getElementById('reason').value;

    const procedure = procedureContent.map((name, index) => {
        const labData = laboratoryContent[index] || {}; // fallback to empty object
        console.log(labData);
        return {
            patient_id: id,
            procedure_name: name,
            contraption_start_date: dateContraptionContent[index] || null,
            procedure_date: labData.date || null,
            laboratory_diagnostic: labData.diagnostic || '',
            status: labData.status || ''
        };
    });
    // Add display_table to each attachment
    const attachment1Content = attachment1.map(file => ({
    file,
    display_table: '1'
    }));

    const attachment2Content = attachment2.map(file => ({
    file,
    display_table: '2'
    }));

    // Combine both arrays
    const attachment = [...attachment1Content, ...attachment2Content];

    const saveData = {
        treatment: {
          patient_id: id,
          treatment_text: treatmentContent,
        },
        referral: {
            patient_id: id,
            department_id: refferedDept,
            reason: reason,
        },
        infusions: {
            patient_id: id,
            ifusions: infusionContent.map(infusion => ({
                ...infusion,
                display_table: String(1)
            }))
        },
        infusions2: {
            patient_id: id,
            ifusions:infusion2Content.map(infusion2 => ({
                ...infusion2,
                display_table: String(2)
            }))
        },
        procedure: procedure
      };
      
      const profileDetails = getEditedProfileDetails();
  
      const profileRequest = fetch(`/kardex_system/src/routes/index.php/updatePatient/${id}`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(profileDetails)
      });

    // Treatments
    const treatmentRequest = fetch(`/kardex_system/src/routes/index.php/addTreatment/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(saveData.treatment)
    });

    const referralRequest = fetch(`/kardex_system/src/routes/index.php/addReferral/${id}`, {
        method: "POST",
        body: JSON.stringify(saveData.referral)
    });

    // Procedure/Operaations
    const procedureRequest = fetch(`/kardex_system/src/routes/index.php/addProcedure/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(saveData.procedure)
    });

    // Second fetch: send attachment using FormData
    const formData = new FormData();
    formData.append("patient_id", id);
    
    attachment.forEach((item, index) => {
        formData.append("files[]", item.file); // ✅ Correct File object
        formData.append("display_tables[]", item.display_table); // optional: include which group
    });
    
    const attachmentRequest = fetch(`/kardex_system/src/routes/index.php/addAttachment/${id}`, {
        method: "POST",
        body: formData
    });



    submitInfusions(id, saveData.infusions, saveData.infusions2);
    // Await all
    const results = await Promise.allSettled([profileRequest, treatmentRequest, referralRequest, attachmentRequest, procedureRequest ]);

    results.forEach((result, index) => {
        if (result.status === "fulfilled") {
            console.log(`Request ${index + 1} succeeded`, result.value);
        } else {
            console.error(`Request ${index + 1} failed`, result.reason);
        }
    });
}

async function  submitInfusions(id, infusions, infusions2) {
    // Step 1: Delete current infusions
    const deleteResponse = await fetch(`/kardex_system/src/routes/index.php/deleteIVF/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(id)
    });

    if (!deleteResponse.ok) {
        console.error("Failed to delete current infusions");
        return;
    }

    // Step 2: Add first infusion table
    const infusionResponse = await fetch(`/kardex_system/src/routes/index.php/addIVF/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(infusions)
    });

    if (!infusionResponse.ok) {
        console.error("Failed to add first infusion table");
        return;
    }

    // Step 3: Add second infusion table
    const infusion2Response = await fetch(`/kardex_system/src/routes/index.php/addIVF/${id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(infusions2)
    });

    if (!infusion2Response.ok) {
        console.error("Failed to add second infusion table");
        return;
    }

}


