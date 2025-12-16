/*
  Requirement: Make the "Manage Resources" page interactive.

  Instructions:
  1. Link this file to `admin.html` using:
     <script src="admin.js" defer></script>
  
  2. In `admin.html`, add an `id="resources-tbody"` to the <tbody> element
     inside your `resources-table`.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the resources loaded from the JSON file.
let resources = [];

// --- Element Selections ---
// TODO: Select the resource form ('#resource-form').
const resourceForm = document.getElementById('resource-form');

// TODO: Select the resources table body ('#resources-tbody').
const resourcesTableBody = document.getElementById('resources-tbody');

// --- Functions ---

/**
 * TODO: Implement the createResourceRow function.
 * It takes one resource object {id, title, description}.
 * It should return a <tr> element with the following <td>s:
 * 1. A <td> for the `title`.
 * 2. A <td> for the `description`.
 * 3. A <td> containing two buttons:
 * - An "Edit" button with class "edit-btn" and `data-id="${id}"`.
 * - A "Delete" button with class "delete-btn" and `data-id="${id}"`.
 */
function createResourceRow(resource) {
  // ... your implementation here ...
  const tr = document.createElement('tr');

  const titleTd = document.createElement('td');
  titleTd.textContent = resource.title || '';

  const descTd = document.createElement('td');
  descTd.textContent = resource.description || '';

  const actionsTd = document.createElement('td');

  const editBtn = document.createElement('button');
  editBtn.type = 'button';
  editBtn.className = 'edit-btn';
  editBtn.dataset.id = resource.id;
  editBtn.textContent = 'Edit';

  const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.className = 'delete-btn';
  deleteBtn.dataset.id = resource.id;
  deleteBtn.textContent = 'Delete';

  actionsTd.appendChild(editBtn);
  actionsTd.appendChild(deleteBtn);

  tr.appendChild(titleTd);
  tr.appendChild(descTd);
  tr.appendChild(actionsTd);

  return tr;
}

/**
 * TODO: Implement the renderTable function.
 * It should:
 * 1. Clear the `resourcesTableBody`.
 * 2. Loop through the global `resources` array.
 * 3. For each resource, call `createResourceRow()`, and
 * append the resulting <tr> to `resourcesTableBody`.
 */
function renderTable() {
  // ... your implementation here ...

  if (!resourcesTableBody) return;
  // Clear existing rows
  resourcesTableBody.innerHTML = '';

  // Append rows for each resource
  resources.forEach(resource => {
    const row = createResourceRow(resource);
    resourcesTableBody.appendChild(row);
  });
}

/**
 * TODO: Implement the handleAddResource function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the title, description, and link inputs.
 * 3. Create a new resource object with a unique ID (e.g., `id: \`res_${Date.now()}\``).
 * 4. Add this new resource object to the global `resources` array (in-memory only).
 * 5. Call `renderTable()` to refresh the list.
 * 6. Reset the form.
 */
function handleAddResource(event) {
  // ... your implementation here ...

  event.preventDefault();
  if (!resourceForm) return;

  const titleEl = document.getElementById('resource-title');
  const descEl = document.getElementById('resource-description');
  const linkEl = document.getElementById('resource-link');

  const title = titleEl?.value?.trim() || '';
  const description = descEl?.value?.trim() || '';
  const link = linkEl?.value?.trim() || '';

  if (!title || !link) {
    alert('Please provide a title and a link for the resource.');
    return;
  }

  const newResource = {
    id: `res_${Date.now()}`,
    title,
    description,
    link
  };

  resources.push(newResource);
  renderTable();
  resourceForm.reset();
}

/**
 * TODO: Implement the handleTableClick function.
 * This is an event listener on the `resourcesTableBody` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `resources` array by filtering out the resource
 * with the matching ID (in-memory only).
 * 4. Call `renderTable()` to refresh the list.
 */
function handleTableClick(event) {
  // ... your implementation here ...

  const target = event.target;
  if (!target) return;

  // Delete flow
  if (target.classList && target.classList.contains('delete-btn')) {
    const id = target.dataset.id;
    if (!id) return;
    resources = resources.filter(r => String(r.id) !== String(id));
    renderTable();
    return;
  }

  // Edit flow (basic inline prompt)
  if (target.classList && target.classList.contains('edit-btn')) {
    const id = target.dataset.id;
    if (!id) return;
    const res = resources.find(r => String(r.id) === String(id));
    if (!res) return;

    const newTitle = prompt('Edit resource title:', res.title);
    if (newTitle === null) return; // cancelled
    const newDesc = prompt('Edit resource description:', res.description || '');
    if (newDesc === null) return;
    const newLink = prompt('Edit resource link (full URL):', res.link || '');
    if (newLink === null) return;

    res.title = String(newTitle).trim() || res.title;
    res.description = String(newDesc).trim();
    res.link = String(newLink).trim() || res.link;
    renderTable();
    return;
  }
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'resources.json'.
 * 2. Parse the JSON response and store the result in the global `resources` array.
 * 3. Call `renderTable()` to populate the table for the first time.
 * 4. Add the 'submit' event listener to `resourceForm` (calls `handleAddResource`).
 * 5. Add the 'click' event listener to `resourcesTableBody` (calls `handleTableClick`).
 */
async function loadAndInitialize() {
  try {
    let resp = await fetch('api/index.php');
    if (resp.ok) {
      const data = await resp.json();
      if (data && Array.isArray(data.data)) {
        resources = data.data;
      } else {
        resources = [];
      }
    }
  } catch (err) {
    console.error('Failed to load resources:', err);
    resources = [];
  }

  renderTable();

  if (resourceForm) resourceForm.addEventListener('submit', handleAddResource);
  if (resourcesTableBody) resourcesTableBody.addEventListener('click', handleTableClick);
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
