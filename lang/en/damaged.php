<?php

return [
    'title'    => 'Damaged Products',
    'subtitle' => 'Record damaged stock, the reason, and the loss it caused.',
    'form_sub' => 'Fill in the damage details below.',
    'add'      => 'Report Damage',
    'edit'     => 'Edit Damage Record',
    'saved'    => 'Damage record saved successfully.',
    'updated'  => 'Damage record updated successfully.',
    'deleted'  => 'Damage record deleted.',

    'stat_records'       => 'DAMAGE RECORDS',
    'stat_records_sub'   => 'all time',
    'stat_units'         => 'DAMAGED UNITS',
    'stat_units_sub'     => 'items damaged',
    'stat_loss'          => 'TOTAL LOSS',
    'stat_loss_sub'      => 'value of damaged stock',
    'stat_unclaimed'     => 'NOT YET CLAIMED',
    'stat_unclaimed_sub' => 'need a supplier claim',

    'th_product' => 'PRODUCT',
    'th_qty'     => 'QTY',
    'th_stage'   => 'WHERE',
    'th_reason'  => 'REASON',
    'th_loss'    => 'LOSS',
    'th_date'    => 'DATE',
    'th_claim'   => 'CLAIM',

    'all_stages' => 'All stages',
    'all_status' => 'All status',
    'select'     => '— Select product —',

    'stage' => [
        'on_delivery'     => 'On delivery',
        'in_store'        => 'In store',
        'customer_return' => 'Customer return',
    ],
    'status' => [
        'reported'     => 'Reported',
        'claim_raised' => 'Claim raised',
        'recovered'    => 'Recovered',
        'written_off'  => 'Written off',
    ],

    'f_product_id'   => 'Product',
    'f_qty'          => 'Damaged quantity',
    'f_damage_stage' => 'Where was it damaged?',
    'f_reason'       => 'Reason',
    'f_loss_value'   => 'Loss value (Rs)',
    'f_photo'        => 'Photo',
    'f_status'       => 'Status',
    'f_reported_at'  => 'Date reported',
    'f_notes'        => 'Notes',
    'f_deduct_stock' => 'Deduct this quantity from product stock',

    'hint_loss'   => 'Leave empty to calculate automatically (quantity × cost price).',
    'hint_photo'  => 'Optional. JPG, PNG or WEBP, up to 2 MB. Useful as proof for the supplier claim.',
    'hint_deduct' => 'Applies only when saving a new record.',
];