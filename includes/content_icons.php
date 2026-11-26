<?php
declare(strict_types=1);

/** Trusted inline SVGs (key => markup). Keys are chosen by editors; markup never comes from user input. */
function content_icons(): array
{
    $open = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';

    return [
        'consultation' => $open . '<path d="M6 2v6a6 6 0 0 0 12 0V2"></path><path d="M8 15v1a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-4"></path><circle cx="20" cy="10" r="2"></circle></svg>',
        'diagnostics' => $open . '<path d="M14.5 2v6l4.5 8a2 2 0 0 1-1.7 3H6.7A2 2 0 0 1 5 16l4.5-8V2"></path><path d="M8.5 2h7"></path><path d="M7 16h10"></path></svg>',
        'laboratory' => $open . '<path d="M9 2v6.5L4.6 17A2 2 0 0 0 6.4 20h11.2a2 2 0 0 0 1.8-3L15 8.5V2"></path><line x1="8" y1="2" x2="16" y2="2"></line><circle cx="12" cy="15" r="1.3"></circle></svg>',
        'physiotherapy' => $open . '<circle cx="12" cy="5" r="2"></circle><path d="M12 7v6"></path><path d="m9 21 3-4 3 4"></path><path d="M7 11h10"></path></svg>',
        'women' => $open . '<path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg>',
        'kidney' => $open . '<path d="M12 3C8 3 5 6 5 10c0 3.5 2.5 6 4.5 7.5C11 18.7 11 21 12 21s1-2.3 2.5-3.5C16.5 16 19 13.5 19 10c0-4-3-7-7-7z"></path><path d="M12 9v4"></path></svg>',
    ];
}
