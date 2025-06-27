<?php


// --- Scenario 1: Web Page with Bootstrap 5 ---

use PhpLiteCore\Pagination\Paginator;
use PhpLiteCore\Pagination\Renderers\Bootstrap5Renderer;
use PhpLiteCore\Pagination\Renderers\JsonRenderer;

echo "<h2>Scenario 1: Bootstrap 5 HTML Output</h2>";

try {
    // 2. Get data from user request
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $totalItemsFromDb = 157; // Example: result of "SELECT COUNT(*) FROM articles"

    // 3. Create the logic Paginator
    $paginator = new Paginator(
        totalItems: $totalItemsFromDb,
        perPage: 15,
        currentPage: $currentPage
    );

    // 4. Create the desired renderer
    $renderer = new Bootstrap5Renderer();

    // 5. Render the output
    $paginationHtml = $renderer->render($paginator);

    // 6. Use the paginator data in your application
    echo "<p>Showing items from a database query like: <br><code>SELECT * FROM articles LIMIT {$paginator->getPerPage()} OFFSET {$paginator->getOffset()}</code></p>";

    // 7. Display the pagination controls
    echo $paginationHtml;

} catch (InvalidArgumentException $e) {
    // Handle error, e.g., show a 404 page or an error message
    http_response_code(400);
    echo "Error: " . $e->getMessage();
}


// --- Scenario 2: JSON API Endpoint ---

echo "<hr><h2>Scenario 2: JSON API Output</h2>";
echo "<pre>";

// Use the same Paginator object from before or create a new one
$apiPaginator = new Paginator(157, 15, 3);

// Just switch the renderer!
$jsonRenderer = new JsonRenderer();

// Render the JSON output
$jsonOutput = $jsonRenderer->render($apiPaginator);

echo htmlspecialchars($jsonOutput);

echo "</pre>";