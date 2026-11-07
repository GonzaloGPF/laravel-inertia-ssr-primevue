<?php

/**
 * What the page of an error says, by the status of the response. The title of each is in http-statuses.
 */
return [
    '403' => 'You do not have permission to see this page.',
    '404' => 'The page you are looking for does not exist, or it has been moved.',
    '429' => 'There have been too many requests. Wait a moment and try again.',
    '500' => 'Something went wrong on our side. Try again in a while.',
    '503' => 'The application is not available right now. Try again in a few minutes.',
    'default' => 'Something went wrong.',
];
