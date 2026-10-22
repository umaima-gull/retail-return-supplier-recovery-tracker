<?php

return [
    'title'    => 'Expiry Management',
    'subtitle' => 'Track products that are expired or about to expire, and what was done about them.',
    'form_sub' => 'Fill in the expiry details below.',
    'add'      => 'Add Expiry Record',
    'edit'     => 'Edit Expiry Record',
    'saved'    => 'Expiry record saved successfully.',
    'updated'  => 'Expiry record updated successfully.',
    'deleted'  => 'Expiry record deleted.',

    'units' => ':count units',

    'stat_expired'     => 'EXPIRED STOCK',
    'stat_expired_sub' => 'waiting for action',
    'stat_soon'        => 'EXPIRING SOON',
    'stat_soon_sub'    => 'within :days days',
    'stat_pending'     => 'PENDING ACTION',
    'stat_pending_sub' => 'records to handle',
    'stat_risk'        => 'VALUE AT RISK',
    'stat_risk_sub'    => 'not yet recovered',

    'th_product' => 'PRODUCT',
    'th_batch'   => 'BATCH',
    'th_qty'     => 'QTY',
    'th_date'    => 'EXPIRY DATE',
    'th_left'    => 'EXPIRES',
    'th_loss'    => 'VALUE',
    'th_claim'   => 'CLAIM',
    'th_action'  => 'ACTION TAKEN',

    'all_dates'   => 'All dates',
    'all_actions' => 'All actions',
    'select'      => '— Select product —',

    'state_expired' => 'Expired',
    'state_urgent'  => 'Urgent (3 days)',
    'state_soon'    => 'Expiring soon',
    'state_ok'      => 'Not due yet',

    'today'       => 'Expires today',
    'expired_ago' => '{1} Expired 1 day ago|[2,*] Expired :count days ago',
    'days_left'   => '{1} 1 day left|[2,*] :count days left',

    'action' => [
        'pending'      => 'Pending',
        'discounted'   => 'Sold at discount',
        'claim_raised' => 'Claim raised',
        'recovered'    => 'Recovered',
        'disposed'     => 'Disposed',
    ],

    'f_product_id'    => 'Product',
    'f_qty'           => 'Quantity',
    'f_expiry_date'   => 'Expiry date',
    'f_action_status' => 'Action taken',
    'f_loss_value'    => 'Value (Rs)',
    'f_notes'         => 'Notes',
    'f_deduct_stock'  => 'Deduct this quantity from product stock',

    'hint_loss'   => 'Leave empty to calculate automatically (quantity × cost price).',
    'hint_deduct' => 'Applies only when saving a new record.',
];