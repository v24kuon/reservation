<?php

return [
    'errors' => [
        'duplicate_active_reservation' => '同じレッスン枠に既に予約があります。',
        'subscription_invalid_or_no_remaining' => 'サブスクリプションが無効であるか、利用可能なレッスン回数がありません。',
        'booking_deadline_passed' => '予約受付期限を過ぎています。',
        'lesson_full' => 'このレッスンは満員です。',
        'target_not_found' => '予約対象のデータが見つかりません。',
        'subscription_owner_mismatch' => 'サブスクリプションの所有者が一致しません。',
        'no_remaining_lessons' => '利用可能なレッスン回数がありません。',
        'generic_failure' => '予約処理中にエラーが発生しました。時間をおいて再度お試しください。',
        'cancel_deadline_passed' => 'キャンセル期限を過ぎているため、キャンセルできません。',
        'cancel_generic_failure' => 'キャンセル処理中にエラーが発生しました。',
        'reservation_lesson_missing' => '予約対象のレッスンが見つかりません。',
        'subscription_missing' => '有効なサブスクリプションがありません。',
    ],
];
