<?php

return [
    'status' => [
        'pending' => '保留',
        'confirmed' => '予約済み',
        'canceled' => 'キャンセル済み',
        'completed' => '完了',
        'no_show' => '欠席',
    ],
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
        'status_update_not_supported' => 'このステータス更新はサポートされていません。',
        'delete_only_canceled' => '削除はキャンセル済みの予約に対してのみ可能です。',
    ],
    'success' => [
        'created' => '予約を作成しました。',
        'updated' => '予約を更新しました。',
        'canceled' => '予約をキャンセルしました。',
        'deleted' => '予約を削除しました。',
    ],
];
