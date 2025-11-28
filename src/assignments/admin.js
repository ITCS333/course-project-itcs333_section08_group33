/*
  Requirement: Make the "Manage Assignments" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="assignments-tbody"` to the <tbody> element
     so you can select it.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the assignments loaded from the JSON file.
let assignments = [];

// --- Element Selections ---
// TODO: Select the assignment form ('#assignment-form').
// Implementation (preserve TODO comment):
const assignmentForm = document.getElementById('assignment-form');

// TODO: Select the assignments table body ('#assignments-tbody').
// Implementation (preserve TODO comment):
// Prefer explicit tbody id if present, otherwise fall back to the table's tbody.
const assignmentsTableBody = document.getElementById('assignments-tbody') || document.querySelector('#assignments-table tbody');

// --- Functions ---

/**
 * TODO: Implement the createAssignmentRow function.
 * It takes one assignment object {id, title, dueDate}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `dueDate`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createAssignmentRow(assignment) {
  // ... your implementation here ...
  const tr = document.createElement('tr');

  const titleTd = document.createElement('td');
  titleTd.textContent = assignment.title || '';

  const dueTd = document.createElement('td');
  dueTd.textContent = assignment.dueDate || '';

  const actionsTd = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.type = 'button';
  editBtn.className = 'edit-btn';
  editBtn.dataset.id = assignment.id;
  editBtn.textContent = 'Edit';

  const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.className = 'delete-btn';
  deleteBtn.dataset.id = assignment.id;
  deleteBtn.textContent = 'Delete';

  actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);

  tr.appendChild(titleTd);
  tr.appendChild(dueTd);
  tr.appendChild(actionsTd);

  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `assignmentsTableBody`.
 * 2. Loop through the global `assignments` array.
 * 3. For each assignment, call `createAssignmentRow()`, and
 * append the resulting <tr> to `assignmentsTableBody`.
 */
function renderTable() {
   // ... your implementation here ...
  if (!assignmentsTableBody) return;
  assignmentsTableBody.innerHTML = '';
  assignments.forEach(assignment => {
    const row = createAssignmentRow(assignment);
    assignmentsTableBody.appendChild(row);
  });
}

/**
 * TODO: Implement the handleAddAssignment function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, description, due date, and files inputs.
 * 3. Create a new assignment object with a unique ID (e.g., `id: \`asg_${Date.now()}\``).
 * 4. Add this new assignment object to the global `assignments` array (in-memory only).
 * 5. Call `renderTable()` to refresh the list.
 * 6. Reset the form.
 */
function handleAddAssignment(event) {
  // ... your implementation here ...
  event.preventDefault();
  if (!assignmentForm) return;

  const titleEl = document.getElementById('assignment-title');
  const descEl = document.getElementById('assignment-description');
  const dueEl = document.getElementById('assignment-due-date');
  const filesEl = document.getElementById('assignment-files');

  const title = titleEl?.value?.trim() || '';
  const description = descEl?.value?.trim() || '';
  const dueDate = dueEl?.value || '';
  const files = filesEl?.value?.trim() || '';

  if (!title || !dueDate) {
    alert('Please provide at least a title and due date.');
    return;
  }

  const newAssignment = {
    id: `asg_${Date.now()}`,
    title,
    description,
    dueDate,
    files
  };

  assignments.push(newAssignment);
  renderTable();
  assignmentForm.reset();
}

/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `assignmentsTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `assignments` array by filtering out the assignment
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // ... your implementation here ...
  const target = event.target;
  if (!target) return;

  if (target.classList.contains('delete-btn')) {
    const id = target.dataset.id;
    if (!id) return;
    assignments = assignments.filter(a => String(a.id) !== String(id));
    renderTable();
    return;
  }

  // (Optional) handle edit click - simple alert for now (comment preserved)
  if (target.classList.contains('edit-btn')) {
    const id = target.dataset.id;
    if (!id) return;
    const asg = assignments.find(a => String(a.id) === String(id));
    if (!asg) return;
    // Basic inline edit could be implemented; for now show simple prompt flow
    const newTitle = prompt('Edit assignment title:', asg.title);
    if (newTitle === null) return; // cancelled
    asg.title = String(newTitle).trim() || asg.title;
    renderTable();
    return;
  }
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response and store the result in the global `assignments` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `assignmentForm` (calls `handleAddAssignment`).
 * 5. Add the 'click' event listener to `assignmentsTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  // ... your implementation here ...
    try {
    let resp = await fetch('api/assignments.json');
    if (!resp.ok) {
      // fallback to assignments.json at same folder
      resp = await fetch('assignments.json');
    }
    if (resp.ok) {
      const data = await resp.json();
      if (Array.isArray(data)) assignments = data;
    }
  } catch (err) {
    console.error('Failed to load assignments:', err);
  }

  renderTable();

  if (assignmentForm) assignmentForm.addEventListener('submit', handleAddAssignment);
  if (assignmentsTableBody) assignmentsTableBody.addEventListener('click', handleTableClick);
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
