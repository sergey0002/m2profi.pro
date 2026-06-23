<?php
$GLOBALS['compred_obj_types'] = [
    'apartment' => ['label' => function_exists('unit_badge') ? unit_badge() : '', 'badge_class' => 'cp-badge', 'enabled' => true],
    'parking'   => ['label' => 'ПАРКОВКА', 'enabled' => false],
    'rent'      => ['label' => 'АРЕНДА',   'enabled' => false],
];
