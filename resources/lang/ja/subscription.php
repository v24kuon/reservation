<?php

return [
    'status' => [
        'active' => '有効',
        'canceled' => 'キャンセル済み',
        'past_due' => '支払い遅延',
        'trialing' => 'トライアル中',
    ],
    'errors' => [
        'count_limit_reached' => 'ご利用可能な回数が上限に達しました。次回の期間開始後にお試しください。',
        'reservation_blocked' => '現在のサブスクリプション状態では予約できません。',
        'payment_failed_grace' => 'お支払いに失敗しました。3日間の猶予期間中にお手続きをお願いします。',
        'plan_switch_invalid' => 'プラン切り替えに失敗しました。条件を確認して再度お試しください。',
        'checkout_failed' => '決済セッションの作成に失敗しました。時間をおいて再度お試しください。',
    ],
    'success' => [
        'reservation_canceled_refund' => '期限前キャンセルのため回数を戻しました。',
        'checkout_canceled' => 'サブスクリプション手続きがキャンセルされました。',
    ],
];
