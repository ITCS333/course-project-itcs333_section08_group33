/*
  Requirement: Populate the "Course Assignments" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="assignment-list-section"` to the
     <section> element that will contain the assignment articles. *Done*

  3. Implement the TODOs below.
*/
<script src="list.js" defer></script>

// --- Element Selections ---
// TODO: Select the section for the assignment list ('#assignment-list-section').
let listSection = document.getElementById("assignment-list-section");
// --- Functions ---


/**
 * TODO: Implement the createAssignmentArticle function.
 * It takes one assignment object {id, title, dueDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * The "View Details" link's `href` MUST be set to `details.html?id=${id}`.
 * This is how the detail page will know which assignment to load.
 */
function createAssignmentArticle(assignment) {
  // ... your implementation here ...
  const {id, title, dueDate, description} = assignment;

  // Create article element
  let article = document.createElement("article");

  // Create and append h2 for title
  let h2 = document.createElement("h2");
  h2.textContent = title;
  article.appendChild(h2);

  // Create and append p for due date
  let dueDateP = document.createElement("p");
  dueDateP.textContent = `Due Date: ${dueDate}`;
  article.appendChild(dueDateP);

  // Create and append p for description
  let descP = document.createElement("p");
  descP.textContent = description;
  article.appendChild(descP);

  // Create and append a for view details link
  let link = document.createElement("a");
  link.href = `details.html?id=${id}`;
  link.textContent = "View Details";
  article.appendChild(link);

  return article;
  
}

/**
 * TODO: Implement the loadAssignments function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'assignments.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the assignments array. For each assignment:
 * - Call `createAssignmentArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadAssignments() {
  // ... your implementation here ...
  try {
    // 1. Fetch data from 'assignments.json'
    const response = await fetch('assignments.json');
    
    // 2. Parse the JSON response into an array
    const assignments = await response.json();
    
    // 3. Clear any existing content from listSection
    listSection.innerHTML = '';
    
    // 4. Loop through the assignments array
    assignments.forEach(assignment => {
      // Call createAssignmentArticle()
      const article = createAssignmentArticle(assignment);
      // Append the returned <article> element to listSection
      listSection.appendChild(article);
    });
  } catch (error) {
    console.error('Error loading assignments:', error);
  }
}

// --- Initial Page Load ---
// Call the function to populate the page.
loadAssignments();
