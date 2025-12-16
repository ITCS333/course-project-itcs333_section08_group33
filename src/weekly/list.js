/*
  Requirement: Populate the "Weekly Course Breakdown" list page.

  Instructions:
  1. Link this file to `list.html` using:
     <script src="list.js" defer></script>

  2. In `list.html`, add an `id="week-list-section"` to the
     <section> element that will contain the weekly articles.

  3. Implement the TODOs below.
*/

// --- Element Selections ---
// TODO: Select the section for the week list ('#week-list-section').
let listSection = document.getElementById('week-list-section');
// --- Functions ---

/**
 * TODO: Implement the createWeekArticle function.
 * It takes one week object {id, title, startDate, description}.
 * It should return an <article> element matching the structure in `list.html`.
 * - The "View Details & Discussion" link's `href` MUST be set to `details.html?id=${id}`.
 * (This is how the detail page will know which week to load).
 */
function createWeekArticle(week) {
  // ... your implementation here ...
  let article = document.createElement('article');

  let h2 = document.createElement('h2');
  h2.textContent = week.title;
  article.appendChild(h2);

  let pDate = document.createElement('p');
  pDate.textContent = `Start Date: ${week.startDate}`;
  article.appendChild(pDate);

  let pDesc = document.createElement('p');
  pDesc.textContent = week.description;
  article.appendChild(pDesc);

  let link = document.createElement('a');
  link.href = `details.html?id=${week.id}`;
  link.textContent = 'View Details & Discussion';
  article.appendChild(link);

  return article;
}

/**
 * TODO: Implement the loadWeeks function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'weeks.json'.
 * 2. Parse the JSON response into an array.
 * 3. Clear any existing content from `listSection`.
 * 4. Loop through the weeks array. For each week:
 * - Call `createWeekArticle()`.
 * - Append the returned <article> element to `listSection`.
 */
async function loadWeeks() {
  // ... your implementation here ...
  let response = await fetch('/api/index.php?resource=weeks');
  let weeks = await response.json();

  listSection.innerHTML = '';

  weeks.forEach(week => {
    let article = createWeekArticle(week);
    listSection.appendChild(article);
  });
}

// --- Initial Page Load ---
// Call the function to populate the page.
loadWeeks();
