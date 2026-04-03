<?php

declare(strict_types=1);

return [
    'fallback_actions' => [
        'priority' => [
            'critical' => 'Immediate attention required. Assign to senior team member.',
            'high' => 'Prioritize in current sprint. Review within 24 hours.',
            'medium' => 'Schedule for next available slot. Review within 3 days.',
            'low' => 'Add to backlog. Review when capacity allows.',
        ],

        'category_prefix' => [
            'bug' => 'Reproduce the issue and gather logs.',
        ],
    ],
];
