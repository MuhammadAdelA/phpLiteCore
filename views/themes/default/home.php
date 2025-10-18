<?php
/**
 * The default home page view for phpLiteCore.
 * Displays a welcome message and links for next steps.
 * (This view only displays data passed from the controller)
 */
?>
<div class="container px-4 py-5" id="custom-cards">

    <div class="pb-2 border-bottom">
        <h1 class="display-4 fw-bold text-body-emphasis">
            🚀 <?= htmlspecialchars($heroTitle) ?>
        </h1>
    </div>
    <div class="p-5 mb-4 bg-body-tertiary rounded-3">
        <div class="container-fluid py-5">
            <h2 class="display-5 fw-bold"><?= htmlspecialchars($heroSubtitle) ?></h2>
            <p class="col-md-8 fs-4"><?= htmlspecialchars($heroDescription) ?></p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">

        <div class="col">
            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg">
                <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                    <div class="text-center mb-4">
                        <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.823c-.908-.349-2.103-.69-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                        </svg>
                    </div>
                    <h3 class="mt-3 mb-4 display-6 lh-1 fw-bold text-center"><?= htmlspecialchars($cardDocsTitle) ?></h3>
                    <ul class="d-flex list-unstyled mt-auto">
                        <li class="me-auto">
                            <small><?= htmlspecialchars($cardDocsText) ?></small>
                        </li>
                        <li class="d-flex align-items-center ms-3">
                            <a href="#" class="btn btn-outline-light btn-sm"><?= htmlspecialchars($cardDocsButton) ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card card-cover h-100 overflow-hidden text-bg-secondary rounded-4 shadow-lg">
                <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                    <div class="text-center mb-4">
                        <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>
                        </svg>
                    </div>
                    <h3 class="mt-3 mb-4 display-6 lh-1 fw-bold text-center"><?= htmlspecialchars($cardCodeTitle) ?></h3>
                    <ul class="d-flex list-unstyled mt-auto">
                        <li class="me-auto">
                            <small><?= htmlspecialchars($cardCodeText) ?></small>
                        </li>
                        <li class="d-flex align-items-center ms-3">
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card card-cover h-100 overflow-hidden text-bg-primary rounded-4 shadow-lg">
                <div class="d-flex flex-column h-100 p-5 pb-3 text-shadow-1">
                    <div class="text-center mb-4">
                        <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                        </svg>
                    </div>
                    <h3 class="mt-3 mb-4 display-6 lh-1 fw-bold text-center"><?= htmlspecialchars($cardCommunityTitle) ?></h3>
                    <ul class="d-flex list-unstyled mt-auto">
                        <li class="me-auto">
                            <small><?= htmlspecialchars($cardCommunityText) ?></small>
                        </li>
                        <li class="d-flex align-items-center ms-3">
                            <a href="https://github.com/MuhammadAdelA/phpLiteCore" class="btn btn-outline-light btn-sm" target="_blank"><?= htmlspecialchars($cardCommunityButton) ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <p class="text-muted text-center mt-5"><small><?= htmlspecialchars($versionLabel) ?> 1.0.0 (Example)</small></p>

</div>