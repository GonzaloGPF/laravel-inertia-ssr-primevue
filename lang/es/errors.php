<?php

/**
 * What the page of an error says, by the status of the response. The title of each is in http-statuses.
 */
return [
    '403' => 'No tienes permiso para ver esta página.',
    '404' => 'La página que buscas no existe, o se ha movido.',
    '429' => 'Se han hecho demasiadas solicitudes. Espera un momento e inténtalo de nuevo.',
    '500' => 'Algo ha ido mal por nuestra parte. Inténtalo de nuevo en un rato.',
    '503' => 'La aplicación no está disponible ahora mismo. Inténtalo de nuevo en unos minutos.',
    'default' => 'Algo ha ido mal.',
];
