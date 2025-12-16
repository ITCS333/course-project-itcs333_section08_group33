/*
  Requirement: Add interactivity and data management to the Admin Portal.

  Instructions:
  1. Link this file to your HTML using a <script> tag with the 'defer' attribute.
     Example: <script src="manage_users.js" defer></script>
  2. Implement the JavaScript functionality as described in the TODO comments.
  3. All data management will be done by manipulating the 'students' array
     and re-rendering the table.
*/

// --- Global Data Store ---
// This array will be populated with data fetched from 'students.json'.
let students = [];

// --- Element Selections ---
// We can safely select elements here because 'defer' guarantees
// the HTML document is parsed before this script runs.

// TODO: Select the student table body (tbody).
let tbody = document.querySelector('tbody');
// TODO: Select the "Add Student" form.
// (You'll need to add id="add-student-form" to this form in your HTML).
let addStudentForm = document.getElementById('add-student-form');
// TODO: Select the "Change Password" form.
// (You'll need to add id="password-form" to this form in your HTML).
let passwordForm = document.getElementById('password-form');
// TODO: Select the search input field.
// (You'll need to add id="search-input" to this input in your HTML).
let searchInput = document.getElementById('search-input');
// TODO: Select all table header (th) elements in thead.
let thAll = document.querySelectorAll('thead th');
// --- Functions ---

/**
 * TODO: Implement the createStudentRow function.
 * This function should take a student object {name, id, email} and return a <tr> element.
 * The <tr> should contain:
 * 1. A <td> for the student's name.
 * 2. A <td> for the student's ID.
 * 3. A <td> for the student's email.
 * 4. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and a data-id attribute set to the student's ID.
 * - A "Delete" button with class "delete-btn" and a data-id attribute set to the student's ID.
 */
function createStudentRow(student) {
  // ... your implementation here ...
  let tr = document.createElement('tr');
  let td0 = document.createElement('td');
  let td1 = document.createElement('td');
  let td2 = document.createElement('td');
  let td3 = document.createElement('td');
  let buttonEdit = document.createElement('button');
  let buttonDelete = document.createElement('button');

  td0.innerHTML = student.name;
  td1.innerHTML = student.id;
  td2.innerHTML = student.email;

  buttonDelete.innerText = "Delete";
  buttonEdit.innerHTML = "Edit";
  buttonDelete.className = 'delete-btn';
  buttonEdit.dataset.id = student.id;
  buttonDelete.dataset.id = student.id;
  buttonEdit.className = 'edit-btn';

  td3.appendChild(buttonEdit);
  td3.appendChild(buttonDelete);

  tr.appendChild(td0);
  tr.appendChild(td1);
  tr.appendChild(td2);
  tr.appendChild(td3);

  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * This function takes an array of student objects.
 * It should:
 * 1. Clear the current content of the `studentTableBody`.
 * 2. Loop through the provided array of students.
 * 3. For each student, call `createStudentRow` and append the returned <tr> to `studentTableBody`.
 */
function renderTable(studentArray) {
  // ... your implementation here ...
  tbody.innerHTML = "";

  studentArray.forEach(student => {
    let newTr = createStudentRow(student);
    tbody.appendChild(newTr);
  });
}

/**
 * TODO: Implement the handleChangePassword function.
 * This function will be called when the "Update Password" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "current-password", "new-password", and "confirm-password" inputs.
 * 3. Perform validation:
 * - If "new-password" and "confirm-password" do not match, show an alert: "Passwords do not match."
 * - If "new-password" is less than 8 characters, show an alert: "Password must be at least 8 characters."
 * 4. If validation passes, show an alert: "Password updated successfully!"
 * 5. Clear all three password input fields.
 */
function handleChangePassword(event) {
  // ... your implementation here ...
  event.preventDefault();
  let currPass = document.getElementById('current-password');
  let newPass = document.getElementById('new-password');
  let confPass = document.getElementById('confirm-password');
  let txtCurrPass = currPass.value;
  let txtNewPass = newPass.value;
  let txtConfPass = confPass.value;
  if (txtConfPass != txtNewPass) { alert("Passwords do not match."); return; }else
  if (txtNewPass.length < 8) { alert('Password must be at least 8 charcters.'); return; }
  else{
    alert("Password updated successfully");
    currPass.value = '';
    newPass.value = '';
    confPass.value = '';
  }
}

/**
 * TODO: Implement the handleAddStudent function.
 * This function will be called when the "Add Student" button is clicked.
 * It should:
 * 1. Prevent the form's default submission behavior.
 * 2. Get the values from "student-name", "student-id", and "student-email".
 * 3. Perform validation:
 * - If any of the three fields are empty, show an alert: "Please fill out all required fields."
 * - (Optional) Check if a student with the same ID already exists in the 'students' array.
 * 4. If validation passes:
 * - Create a new student object: { name, id, email }.
 * - Add the new student object to the global 'students' array.
 * - Call `renderTable(students)` to update the view.
 * 5. Clear the "student-name", "student-id", "student-email", and "default-password" input fields.
 */
function handleAddStudent(event) {
  // ... your implementation here ...
  event.preventDefault();
  let stuName = document.getElementById('student-name');
  let stuId = document.getElementById('student-id');
  let stuEmail = document.getElementById('student-email');
  let defPassword = document.getElementById('default-password');
  let txtStuName = stuName.value;
  let txtStuId = stuId.value;
  let txtStuEmail = stuEmail.value


  if (txtStuName == "" || txtStuId == "" || txtStuEmail == "") { alert('Please fill out all required fields') }
  let flag = true;
  students.forEach(student => { if (txtStuId == student.id) { return; } });
  if (flag == false) {
    alert("ID exists")
    return;
  } else {

    let stu = {
      name: txtStuName,
      id: txtStuId,
      email: txtStuEmail
    }
    students.push(stu);
    renderTable(students);
    stuName.value = '';
    stuId.value = '';
    stuEmail.value = '';
    defPassword.value = '';

  }
}

/**
 * TODO: Implement the handleTableClick function.
 * This function will be an event listener on the `studentTableBody` (event delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it is a "delete-btn":
 * - Get the `data-id` attribute from the button.
 * - Update the global 'students' array by filtering out the student with the matching ID.
 * - Call `renderTable(students)` to update the view.
 * 3. (Optional) Check for "edit-btn" and implement edit logic.
 */
function handleTableClick(event) {
  // ... your implementation here ...
  console.log('deleted');
  let tar = event.target;
  console.log(tar);
  if (tar.classList.contains('delete-btn')) {
    const id = tar.dataset.id;
    console.log(id);
    students = students.filter(student => {return student.id != id})
    console.log("TAG:", tar.tagName, "CLASS:", tar.className, "DATA:", tar.dataset);
    console.log(students);
    renderTable(students);
  }

}

/**
 * TODO: Implement the handleSearch function.
 * This function will be called on the "input" event of the `searchInput`.
 * It should:
 * 1. Get the search term from `searchInput.value` and convert it to lowercase.
 * 2. If the search term is empty, call `renderTable(students)` to show all students.
 * 3. If the search term is not empty:
 * - Filter the global 'students' array to find students whose name (lowercase)
 * includes the search term.
 * - Call `renderTable` with the *filtered array*.
 */
function handleSearch(event) {
  // ... your implementation here ...
  const lwc = searchInput.value.toLowerCase();
  const regxLwc = new RegExp(lwc);
  if (lwc == '') { renderTable(students); }
  else {
    const subset = students.filter(student => { return regxLwc.test(student.name.toLowerCase()); });
    renderTable(subset);
  }

}

/**
 * TODO: Implement the handleSort function.
 * This function will be called when any `th` in the `thead` is clicked.
 * It should:
 * 1. Identify which column was clicked (e.g., `event.currentTarget.cellIndex`).
 * 2. Determine the property to sort by ('name', 'id', 'email') based on the index.
 * 3. Determine the sort direction. Use a data-attribute (e.g., `data-sort-dir="asc"`) on the `th`
 * to track the current direction. Toggle between "asc" and "desc".
 * 4. Sort the global 'students' array *in place* using `array.sort()`.
 * - For 'name' and 'email', use `localeCompare` for string comparison.
 * - For 'id', compare the values as numbers.
 * 5. Respect the sort direction (ascending or descending).
 * 6. After sorting, call `renderTable(students)` to update the view.
 */
function handleSort(event) {
  // ... your implementation here ...
  const col = event.currentTarget.cellIndex;
  let colName = '';
  switch (col) {
    case 0:
      thAll.forEach(th => {
        let dir = th.dataset.sortDir || 'asc';
        dir = dir === "asc" ? "desc" : "asc";
        th.dataset.sortDir = dir;
        if (dir == 'asc') { students.sort((a, b) => a.name.localeCompare(b.name)); } else
          if (dir == 'desc') { students.sort((a, b) => b.name.localeCompare(a.name)); }
      })
      renderTable(students);
      break;
    case 1:
      thAll.forEach(th => {
        let dir = th.dataset.sortDir || 'asc';
        dir = dir === "asc" ? "desc" : "asc";
        th.dataset.sortDir = dir;
        if (dir == 'asc') { students.sort((a, b) => b.id - a.id); } else
          if (dir == 'desc') { students.sort((a, b) => a.id - b.id); }
      })
      renderTable(students);
      break;
    case 2:
      thAll.forEach(th => {
        let dir = th.dataset.sortDir || 'asc';
        dir = dir === "asc" ? "desc" : "asc";
        th.dataset.sortDir = dir;
        if (dir == 'asc') { students.sort((a, b) => a.email.localeCompare(b.email)); } else
          if (dir == 'desc') { students.sort((a, b) => b.email.localeCompare(a.email)); }
      })
      renderTable(students);
      break;
    default:
      alert("handleSort dosen't handel this column name")
  }

}

/**
 * TODO: Implement the loadStudentsAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use the `fetch()` API to get data from 'students.json'.
 * 2. Check if the response is 'ok'. If not, log an error.
 * 3. Parse the JSON response (e.g., `await response.json()`).
 * 4. Assign the resulting array to the global 'students' variable.
 * 5. Call `renderTable(students)` to populate the table for the first time.
 * 6. After data is loaded, set up all the event listeners:
 * - "submit" on `changePasswordForm` -> `handleChangePassword`
 * - "submit" on `addStudentForm` -> `handleAddStudent`
 * - "click" on `studentTableBody` -> `handleTableClick`
 * - "input" on `searchInput` -> `handleSearch`
 * - "click" on each header in `tableHeaders` -> `handleSort`
 */
async function loadStudentsAndInitialize() {
  let stuData = await (await fetch('api/index.php')).json();

  students = Array.isArray(stuData.data) ? stuData.data : [];

  renderTable(students);

  passwordForm.addEventListener('submit', handleChangePassword);
  addStudentForm.addEventListener('submit', handleAddStudent);
  tbody.addEventListener('click', handleTableClick);
  searchInput.addEventListener('input', handleSearch);
  thAll.forEach(th => th.addEventListener('click', handleSort));
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadStudentsAndInitialize();
