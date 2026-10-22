<?php
declare(strict_types=1);

/**
 * Root entry point for shared hosting where the document root cannot be
 * configured to point directly at /public.
 *
 * Keep the application entry point inside /public. Query parameters are
 * preserved by redirecting to the relative public/ path.
 */
header('Location: public/', true, 302);
exit;
