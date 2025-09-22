<!-- markdownlint-disable MD024 -->
# Tasks Document for Yoga & Pilates Reservation System
# ヨガ・ピラティス予約システム - タスクドキュメント

## Phase 1: Complete Foundation Setup
## フェーズ1: 基盤完成

### Instructor Management Tasks
### インストラクター管理タスク

- [x] 1. Add role field to users table / ユーザーテーブルにロールフィールドを追加
  - File: database/migrations/ (check existing migration) / ファイル: database/migrations/ (既存マイグレーション確認)
  - Purpose: Ensure role field exists for user types (admin, user, instructor) / 目的: ユーザータイプ用のロールフィールド（admin, user, instructor）が存在することを確認
  - Requirements: 1.1 (User Authentication and Authorization) / 要件: 1.1 (ユーザー認証・認可)
  - Dependencies: None / 依存関係: なし
  - Estimated time: 5 minutes / 推定時間: 5分

- [x] 2. Create admin interface for instructor account creation / インストラクターアカウント作成用の管理者インターフェースを作成
  - File: resources/views/admin/instructors/ (new directory) / ファイル: resources/views/admin/instructors/ (新規ディレクトリ)
  - Purpose: Allow admin to create new instructor accounts with instructor role / 目的: 管理者がインストラクターロールで新しいインストラクターアカウントを作成可能にする
  - Requirements: 1.1 / 要件: 1.1
  - Dependencies: Task 1 / 依存関係: タスク1
  - Estimated time: 20 minutes / 推定時間: 20分

- [x] 3. Create instructor account management controller / インストラクターアカウント管理コントローラーを作成
  - File: app/Http/Controllers/Admin/InstructorController.php (new) / ファイル: app/Http/Controllers/Admin/InstructorController.php (新規)
  - Purpose: Handle instructor account creation, editing, and deletion / 目的: インストラクターアカウントの作成、編集、削除を処理
  - Requirements: 1.1 / 要件: 1.1
  - Dependencies: Task 2 / 依存関係: タスク2
  - Estimated time: 25 minutes / 推定時間: 25分

- [x] 4. Create instructor_profiles table migration / instructor_profilesテーブルマイグレーションを作成
  - File: database/migrations/create_instructor_profiles_table.php (new) / ファイル: database/migrations/create_instructor_profiles_table.php (新規)
  - Fields: user_id (unique FK), image_path, bio, qualifications, notes / フィールド: user_id (unique FK), image_path, bio, qualifications, notes
  - Purpose: Create dedicated table for instructor profile data / 目的: インストラクタープロフィールデータ用の専用テーブルを作成
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: None / 依存関係: なし
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 5. Create InstructorProfile model / InstructorProfileモデルを作成
  - File: app/Models/InstructorProfile.php (new) / ファイル: app/Models/InstructorProfile.php (新規)
  - Add relationships, accessors, and validation / リレーション、アクセサー、バリデーションを追加
  - Purpose: Model for instructor profile data management / 目的: インストラクタープロフィールデータ管理用のモデル
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 4 / 依存関係: タスク4
  - Estimated time: 20 minutes / 推定時間: 20分

- [x] 6. Update User model for instructor profile relationship / インストラクタープロフィールリレーションシップ用にUserモデルを更新
  - File: app/Models/User.php (modify) / ファイル: app/Models/User.php (修正)
  - Add instructorProfile() relationship and helper methods / instructorProfile()リレーションとヘルパーメソッドを追加
  - Purpose: Enable user-instructor profile relationship / 目的: ユーザー-インストラクタープロフィールリレーションを有効化
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 5 / 依存関係: タスク5
  - Estimated time: 10 minutes / 推定時間: 10分

- [x] 7. Create instructor profile Form Request validation / インストラクタープロフィールForm Requestバリデーションを作成
  - File: app/Http/Requests/UpdateInstructorProfileRequest.php (new) / ファイル: app/Http/Requests/UpdateInstructorProfileRequest.php (新規)
  - Validate image (jpg/png/webp, max 10MB), text fields (max 2000 chars) / 画像（jpg/png/webp、最大10MB）、テキストフィールド（最大2000文字）をバリデーション
  - Purpose: Validate instructor profile form submissions / 目的: インストラクタープロフィールフォーム送信をバリデーション
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 5 / 依存関係: タスク5
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 8. Create instructor profile controller / インストラクタープロフィールコントローラーを作成
  - File: app/Http/Controllers/InstructorProfileController.php (new) / ファイル: app/Http/Controllers/InstructorProfileController.php (新規)
  - Implement CRUD operations with proper authorization / 適切な認可でCRUD操作を実装
  - Purpose: Handle instructor profile management requests / 目的: インストラクタープロフィール管理リクエストを処理
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 7 / 依存関係: タスク7
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 9. Add instructor profile routes / インストラクタープロフィールルートを追加
  - File: routes/web.php (modify) / ファイル: routes/web.php (修正)
  - Add routes for /instructor/profile and /admin/instructors/{user}/edit / /instructor/profile と /admin/instructors/{user}/edit のルートを追加
  - Purpose: Define URL routing for instructor profile features / 目的: インストラクタープロフィール機能のURLルーティングを定義
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 8 / 依存関係: タスク8
  - Estimated time: 10 minutes / 推定時間: 10分

- [x] 10. Create instructor profile views / インストラクタープロフィールビューを作成
  - File: resources/views/instructor/profile/edit.blade.php (new) / ファイル: resources/views/instructor/profile/edit.blade.php (新規)
  - File: resources/views/admin/instructors/edit.blade.php (new/modify) / ファイル: resources/views/admin/instructors/edit.blade.php (新規/修正)
  - Purpose: User interface for instructor profile editing / 目的: インストラクタープロフィール編集用のユーザーインターフェース
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 9 / 依存関係: タスク9
  - Estimated time: 25 minutes / 推定時間: 25分

- [x] 11. Implement image upload and storage / 画像アップロードとストレージを実装
  - File: app/Http/Controllers/InstructorProfileController.php (modify) / ファイル: app/Http/Controllers/InstructorProfileController.php (修正)
  - Handle image upload to storage/app/public/instructors/ / storage/app/public/instructors/ への画像アップロードを処理
  - Run `php artisan storage:link` to expose public disk / 公開ディスク用のシンボリックリンクを作成
  - Consider S3 (s3 driver) for production and set visibility / 本番はS3利用と可視性設定を検討
  - Purpose: Manage instructor profile image uploads / 目的: インストラクタープロフィール画像アップロードを管理
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 8 / 依存関係: タスク8
  - Estimated time: 20 minutes / 推定時間: 20分

### Lesson Schedule Enhancement Tasks
### レッスンスケジュール機能強化タスク

- [x] 12. Add bulk schedule creation feature / 一括スケジュール作成機能を追加
  - File: app/Http/Controllers/Admin/LessonScheduleController.php (modify) / ファイル: app/Http/Controllers/Admin/LessonScheduleController.php (修正)
  - Add method for creating multiple schedules at once / 複数のスケジュールを一度に作成するメソッドを追加
  - Purpose: Allow creating recurring lesson schedules efficiently / 目的: 繰り返しレッスンスケジュールを効率的に作成できるようにする
  - Requirements: 5.1 (Lesson Schedule Management) / 要件: 5.1 (レッスンスケジュール管理)
  - Dependencies: None / 依存関係: なし
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 13. Implement time overlap validation / 時間重複バリデーションを実装
  - File: app/Models/LessonSchedule.php (modify) / ファイル: app/Models/LessonSchedule.php (修正)
  - Prevent overlaps within payload and against DB; interval = [start, end) / 入力内およびDBに対して重複を防止（半開区間 [start, end)）
  - Store datetimes in UTC; convert at view-layer / 日時はUTC保存・表示時に変換
  - DST boundaries covered by tests / DST境界のテストを含める
  - Overlap scope: same lesson_id AND same instructor_id / 重複判定の対象: 同一lesson_id かつ同一instructor_id（要件に合わせて確定）
  - Edge cases: end == next.start はOK、start == existing.end はOK / 端点一致は非重複
  - Purpose: Prevent scheduling conflicts / 目的: スケジュールの競合を防ぐ
  - Requirements: 5.2 / 要件: 5.2
  - Dependencies: Task 12 / 依存関係: タスク12
  - Estimated time: 20 minutes / 推定時間: 20分

- [x] 14. Add schedule search and filtering / スケジュール検索・フィルタリング機能を追加
  - File: app/Http/Controllers/Admin/LessonScheduleController.php (modify) / ファイル: app/Http/Controllers/Admin/LessonScheduleController.php (修正)
  - Add search by date, lesson, instructor / 日付、レッスン、インストラクターによる検索を追加
  - DB Indexes: (lesson_id, start_datetime), (instructor_id, start_datetime), start_datetime単独
  - Query pattern決定後に複合インデックスを見直し / 実クエリに合わせて見直し
  - Purpose: Improve admin usability for schedule management / 目的: スケジュール管理の管理者ユーザビリティを向上
  - Requirements: 5.3 / 要件: 5.3
  - Dependencies: Task 12 / 依存関係: タスク12
  - Estimated time: 25 minutes / 推定時間: 25分

### Admin UI Enhancement Tasks
### 管理者UI強化タスク

- [x] 15. Improve admin dashboard layout / 管理者ダッシュボードレイアウトを改善
  - File: resources/views/components/admin-layout.blade.php (modify/improve) / ファイル: resources/views/components/admin-layout.blade.php (修正/改善)
  - Purpose: Create consistent admin interface design / 目的: 一貫した管理者インターフェースデザインを作成
  - Requirements: UI improvements / 要件: UI改善
  - Dependencies: None / 依存関係: なし
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 16. Add reservation status visualization / 予約状況可視化を追加
  - File: resources/views/admin/dashboard.blade.php (modify) / ファイル: resources/views/admin/dashboard.blade.php (修正)
  - Display current reservation counts vs capacity / 現在の予約数とキャパシティを表示
  - Purpose: Provide admin with booking status overview / 目的: 管理者に予約状況の概要を提供
  - Requirements: Dashboard improvements / 要件: ダッシュボード改善
  - Dependencies: Task 15 / 依存関係: タスク15
  - Estimated time: 25 minutes / 推定時間: 25分

- [x] 17. Implement responsive admin design / レスポンシブ管理者デザインを実装
  - File: resources/css/app.css (modify), admin views (modify) / ファイル: resources/css/app.css (修正), admin views (修正)
  - Purpose: Ensure admin interface works on different screen sizes / 目的: 管理者インターフェースが異なる画面サイズで動作することを保証
  - Requirements: Responsive design / 要件: レスポンシブデザイン
  - Dependencies: Task 15 / 依存関係: タスク15
  - Estimated time: 20 minutes / 推定時間: 20分


## Phase 2: Subscription System Implementation
## フェーズ2: サブスクリプションシステム実装

### Laravel Cashier Setup Tasks
### Laravel Cashierセットアップタスク

- [x] 18. Install Laravel Cashier and configure Stripe / Laravel CashierをインストールしStripeを設定
  - File: composer.json (modify) / ファイル: composer.json (修正)
  - Command: `composer require laravel/cashier` / コマンド: `composer require laravel/cashier`
  - Purpose: Install Stripe payment processing library / 目的: Stripe決済処理ライブラリをインストール
  - Requirements: 6.1 (Subscription Plan Management) / 要件: 6.1 (サブスクリプションプラン管理)
  - Dependencies: None / 依存関係: なし
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 19. Configure Stripe API keys and webhooks / Stripe APIキーとWebhookを設定
  - File: .env (modify), config/services.php (modify) / ファイル: .env (修正), config/services.php (修正)
  - Add STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET / STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRETを追加
  - Configure CSRF exclusion for only '/stripe/webhook' in bootstrap/app.php / bootstrap/app.phpで'/stripe/webhook'のみCSRF除外を設定
  - Use `php artisan cashier:webhook` for API version consistency with Cashier / CashierとのAPIバージョン整合性のため `php artisan cashier:webhook` を使用
  - Purpose: Set up Stripe credentials and webhook endpoints / 目的: Stripe認証情報とWebhookエンドポイントを設定
  - Requirements: 6.1, 7.1 / 要件: 6.1, 7.1
  - Dependencies: Task 18 / 依存関係: タスク18
  - Estimated time: 20 minutes / 推定時間: 20分

- [x] 20. Create Cashier migrations for subscription tables / サブスクリプションテーブル用のCashierマイグレーションを作成
  - File: database/migrations/ (new files via artisan) / ファイル: database/migrations/ (artisan経由で新規ファイル)
  - Command: `php artisan vendor:publish --tag=cashier-migrations` / コマンド: `php artisan vendor:publish --tag=cashier-migrations`
  - Purpose: Create necessary database tables for subscriptions / 目的: サブスクリプション用の必要なデータベーステーブルを作成
  - Requirements: 7.1 / 要件: 7.1
  - Dependencies: Task 18 / 依存関係: タスク18
  - Estimated time: 5 minutes / 推定時間: 5分

- [x] 21. Run Cashier migrations / Cashierマイグレーションを実行
  - Command: `php artisan migrate` / コマンド: `php artisan migrate`
  - Purpose: Create subscription-related database tables / 目的: サブスクリプション関連のデータベーステーブルを作成
  - Requirements: 7.1 / 要件: 7.1
  - Dependencies: Task 20 / 依存関係: タスク20
  - Estimated time: 2 minutes / 推定時間: 2分

### Stripe Product and Price Management Tasks
### Stripeプロダクト・価格管理タスク

- [x] 22. Create Stripe products and prices via Stripe Dashboard / Stripeダッシュボード経由でStripeプロダクトと価格を作成
  - External: Stripe Dashboard (stripe.com) / 外部: Stripe Dashboard (stripe.com)
  - Create products for Group Lessons and Personal Lessons / グループレッスンと個人レッスン用のプロダクトを作成
  - Create prices for each subscription tier / 各サブスクリプションティア用の価格を作成
  - Purpose: Set up Stripe products matching our subscription plans / 目的: サブスクリプションプランに一致するStripeプロダクトを設定
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Tasks 19-20 / 依存関係: タスク19-20
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 23. Update SubscriptionPlan model for Stripe integration / Stripe統合用にSubscriptionPlanモデルを更新
  - File: app/Models/SubscriptionPlan.php (modify) / ファイル: app/Models/SubscriptionPlan.php (修正)
  - UserモデルにBillableトレイトを付与 / User::class に Billable を追加
  - SubscriptionPlan は Stripe の product/price のメタ情報保持のみ / プラン定義は product_id, price_id 等を保持
  - Purpose: 課金主体（ユーザー）でCashierを動かし、プランは参照に専念 / 目的: ユーザーを課金主体としてCashierを利用
  - Requirements: 6.1, 7.1 / 要件: 6.1, 7.1
  - Dependencies: Task 22 / 依存関係: タスク22
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 24. Implement SubscriptionPlan admin interface for Stripe data management / Stripeデータ管理用のSubscriptionPlan管理画面を実装
  - Files: app/Http/Controllers/Admin/SubscriptionPlanController.php (implement), routes/web.php (add routes), resources/views/admin/subscription_plans/ (complete forms) / ファイル: app/Http/Controllers/Admin/SubscriptionPlanController.php (実装), routes/web.php (ルート追加), resources/views/admin/subscription_plans/ (フォーム完成)
  - Add admin CRUD interface for subscription plans with Stripe product/price ID input / Stripeプロダクト/価格ID入力付きサブスクリプションプラン管理画面を追加
  - サーバー側バリデーション:
    - product_id: 正規表現 ^prod_[A-Za-z0-9]+$
    - price_id:   正規表現 ^price_[A-Za-z0-9]+$
  - 保存前チェック（Stripe API照合）:
    - price が active かつ recurring であること、currency/interval が想定内であることを確認
    - product と price の関連が一致していることを確認
  - 運用ガード:
    - 既に購読中ユーザーがいるプランの product/price 変更や削除は禁止（別レコード追加 → 段階的移行）
      - 判定基準: subscriptions.status in (trialing, active, past_due)
      - 判定クエリ例: `$plan->userSubscriptions()->whereIn('status', ['trialing', 'active', 'past_due'])->exists()`
      - 段階的移行フロー:
        1. 新規プランレコード作成（既存プランは無効化）
        2. 既存ユーザーに新プランへの切替誘導
        3. 全ユーザー移行完了後に旧プラン削除
    - Live/Test のID混入を防ぐため、環境毎の接頭辞・キーで検証し保存を拒否
  - Purpose: Allow admin to manage subscription plans via web interface / 目的: 管理者がWebインターフェース経由でサブスクリプションプランを管理可能にする
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Tasks 22, 23 / 依存関係: タスク22, 23
  - Estimated time: 60 minutes / 推定時間: 60分

### Stripe Checkout Integration Tasks
### Stripe Checkout統合タスク

- [x] 25. Create checkout session controller / チェックアウトセッションコントローラーを作成
  - File: app/Http/Controllers/SubscriptionController.php (new) / ファイル: app/Http/Controllers/SubscriptionController.php (新規)
  - Method: createCheckoutSession($planId) / メソッド: createCheckoutSession($planId)
  - Purpose: Handle Stripe checkout session creation / 目的: Stripeチェックアウトセッション作成を処理
  - Requirements: 7.1 / 要件: 7.1
  - Dependencies: Tasks 22, 23 / 依存関係: タスク22, 23
  - Estimated time: 25 minutes / 推定時間: 25分

- [x] 26. Add subscription routes / サブスクリプションルートを追加
  - File: routes/web.php (modify) / ファイル: routes/web.php (修正)
  - Add routes for subscription checkout and success / サブスクリプションチェックアウトと成功用のルートを追加
  - Purpose: Define URL routing for subscription features / 目的: サブスクリプション機能のURLルーティングを定義
  - Requirements: 7.1 / 要件: 7.1
  - Dependencies: Task 25 / 依存関係: タスク25
  - Estimated time: 10 minutes / 推定時間: 10分

- [x] 27. Create checkout success page / チェックアウト成功ページを作成
  - File: resources/views/subscription/success.blade.php (new) / ファイル: resources/views/subscription/success.blade.php (新規)
  - Display subscription confirmation and next steps / サブスクリプション確認と次のステップを表示
  - Purpose: Provide user feedback after successful subscription / 目的: サブスクリプション成功後のユーザーフィードバックを提供
  - Requirements: 7.1 / 要件: 7.1
  - Dependencies: Task 26 / 依存関係: タスク26
  - Estimated time: 15 minutes / 推定時間: 15分

### Advanced Subscription Management Tasks
### 高度なサブスクリプション管理タスク

- [x] 28. Implement category-based subscription management / カテゴリー別サブスクリプション管理を実装
  - File: app/Models/User.php (modify), app/Models/UserSubscription.php (modify) / ファイル: app/Models/User.php (修正), app/Models/UserSubscription.php (修正)
  - **Architecture Design / アーキテクチャ設計**:
    - Two-layer subscription management: Stripe Layer (subscriptions table) vs Application Layer (user_subscriptions table) / 二層サブスクリプション管理: Stripe Layer (subscriptions table) vs Application Layer (user_subscriptions table)
    - 1:1 relationship: user_subscriptions.stripe_subscription_id = subscriptions.stripe_id / 1:1対応関係: user_subscriptions.stripe_subscription_id = subscriptions.stripe_id
    - Separation of concerns: Stripe handles billing, application handles business logic / 関心の分離: Stripeは請求処理、アプリケーションはビジネスロジック
  - Add User::getActiveSubscriptionForCategory(int $categoryId): ?UserSubscription method / User::getActiveSubscriptionForCategory(int $categoryId): ?UserSubscriptionメソッドを追加
  - Add UserSubscription::scopeForCategory(int $categoryId) scope / UserSubscription::scopeForCategory(int $categoryId)スコープを追加
  - Add UserSubscription::hasCategory(int $categoryId): bool method / UserSubscription::hasCategory(int $categoryId): boolメソッドを追加
  - Add UserSubscription::canBookLesson(Lesson $lesson): bool method with lesson count validation / 回数制限付きUserSubscription::canBookLesson(Lesson $lesson): boolメソッドを追加
  - Priority order: current_period_start DESC, id DESC / 優先順位: current_period_start DESC, id DESC
  - Enforce lesson count limits: check remaining lessons before allowing reservation / 回数制限を強制: 予約許可前に残り回数をチェック
  - Purpose: Enable independent management of group and personal lesson subscriptions with count limits / 目的: 回数制限付きでグループレッスンとパーソナルレッスンのサブスクリプションを独立管理
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Task 27 / 依存関係: タスク27
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 29. Implement plan switching functionality / プラン切り替え機能を実装
  - File: app/Services/SubscriptionService.php (new) / ファイル: app/Services/SubscriptionService.php (新規)
  - Add SubscriptionService::switchPlan(User $user, SubscriptionPlan $from, SubscriptionPlan $to): StripeCheckoutSession method / SubscriptionService::switchPlan(User $user, SubscriptionPlan $from, SubscriptionPlan $to): StripeCheckoutSessionメソッドを追加
  - Implement same-category-only switching rule with validation / 同じカテゴリー内のみ切り替え可能ルールとバリデーションを実装
  - Calculate remaining lessons at webhook time to avoid race: new_remaining = old_plan.lesson_count - current_month_used_count (at completion) / レース回避のため Webhook 時点で再計算
  - Create Checkout Session with hint metadata (remaining_calculated_at, ids) / 参考用メタデータ（remaining_calculated_at 等）を付与
  - Use idempotency_key for session creation (format example: switch:{user_id}:{from_id}:{to_id}:{minute_ts}) / セッション作成に idempotency_key を付与
  - Handle Stripe subscription cancellation and creation via webhooks / Webhook経由でStripeサブスクリプションのキャンセルと作成を処理
  - New subscription: new_plan.lesson_count + remaining_lessons / 新サブスクリプション: new_plan.lesson_count + remaining_lessons
  - Purpose: Allow users to switch between plans within the same category with remaining lessons transfer / 目的: ユーザーが同じカテゴリー内でプランを切り替え可能にし、残り回数を引継ぎ
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Task 28 / 依存関係: タスク28
  - Estimated time: 45 minutes / 推定時間: 45分

- [x] 30. Implement lesson count management / 回数管理機能を実装
  - File: app/Models/UserSubscription.php (modify) / ファイル: app/Models/UserSubscription.php (修正)
  - Add UserSubscription::getTotalAvailableLessons(): int method / UserSubscription::getTotalAvailableLessons(): intメソッドを追加
  - Add UserSubscription::getRemainingLessons(): int method / UserSubscription::getRemainingLessons(): intメソッドを追加
  - Add UserSubscription::hasRemainingLessons(): bool method / UserSubscription::hasRemainingLessons(): boolメソッドを追加
  - Add UserSubscription::canBookLesson(): bool method for reservation control / 予約制御用UserSubscription::canBookLesson(): boolメソッドを追加
  - Handle period reset with remaining lessons via webhooks / Webhook経由で残り回数付き期間リセットを処理
  - Count management: +1 on confirmed reservation, -1 on deadline-before cancellation / 回数管理: 予約確定時+1、期限前キャンセル時-1
  - Enforce lesson count limits: block new reservations when remaining lessons = 0 / 回数制限を強制: 残り回数=0の時は新規予約をブロック
  - Purpose: Manage lesson count transitions during plan switches and enforce reservation limits / 目的: プラン切り替え時の回数移行を管理し、予約制限を強制
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Task 29 / 依存関係: タスク29
  - Estimated time: 25 minutes / 推定時間: 25分

- [x] 30.1. Add database schema enhancements for subscription management / サブスクリプション管理用データベーススキーマ強化を追加
  - File: database/migrations/ (new migration files) / ファイル: database/migrations/ (新規マイグレーションファイル)
  - Add remaining_lessons column to user_subscriptions table / user_subscriptionsテーブルにremaining_lessonsカラムを追加
  - Create plan_switch_logs table for statistics / 統計用plan_switch_logsテーブルを作成
  - Add performance indexes: (user_id, status, payment_status), (user_id, current_period_start DESC), (user_id, lesson_schedule_id), (user_id, status, payment_status, current_period_start DESC, id DESC) / パフォーマンスインデックス追加: (user_id, status, payment_status), (user_id, current_period_start DESC), (user_id, lesson_schedule_id), (user_id, status, payment_status, current_period_start DESC, id DESC)
  - Optional: Add stripe_price_id column for audit trail / 任意: 監査用stripe_price_idカラムを追加
  - Purpose: Support advanced subscription management features / 目的: 高度なサブスクリプション管理機能をサポート
  - Requirements: 6.1 / 要件: 6.1
  - Dependencies: Task 30 / 依存関係: タスク30
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 30.2. Implement comprehensive error handling and user messaging / 包括的なエラーハンドリングとユーザーメッセージングを実装
  - File: app/Http/Controllers/SubscriptionController.php (modify), resources/lang/ (new files) / ファイル: app/Http/Controllers/SubscriptionController.php (修正), resources/lang/ (新規ファイル)
  - Define specific error messages for lesson count limit scenarios / 回数制限シナリオ用の具体的なエラーメッセージを定義
  - Define specific error messages for reservation blocking scenarios / 予約ブロックシナリオ用の具体的なエラーメッセージを定義
  - Implement payment failure handling with 3-day grace period / 3日間猶予期間付き支払い失敗処理を実装
  - Add plan switching error messages and validation feedback / プラン切り替えエラーメッセージとバリデーションフィードバックを追加
  - Handle data inconsistency detection and repair procedures / データ不整合検出と修復手順を処理
  - Purpose: Provide clear user feedback and robust error handling for count limits / 目的: 回数制限に対する明確なユーザーフィードバックと堅牢なエラーハンドリングを提供
  - Requirements: 6.1, 7.2 / 要件: 6.1, 7.2
  - Dependencies: Task 30 / 依存関係: タスク30
  - Estimated time: 20 minutes / 推定時間: 20分

### Webhook Processing Tasks
### Webhook処理タスク

- [x] 31. Create webhook event listeners / Webhookイベントリスナーを作成
  - File: app/Listeners/ (new listener classes) / ファイル: app/Listeners/ (新規リスナークラス)
  - Handle checkout.session.completed: user_subscriptions creation with period sync and remaining lessons transfer / checkout.session.completed: user_subscriptions作成、period同期、残り回数引継ぎ
  - Handle customer.subscription.created/updated/deleted: status/payment_status/period sync / customer.subscription.created/updated/deleted: status/payment_status/period同期
  - Handle invoice.paid: current_period_* sync + current_month_used_count=0 (statistics reset) / invoice.paid: current_period_*同期+current_month_used_count=0（統計リセット）
  - Handle invoice.payment_failed: payment_status=failed (new reservation blocking due to count limits) / invoice.payment_failed: payment_status=failed（回数制限による新規予約ブロック）
  - Use Cashier's default /stripe/webhook route (no custom controller to avoid conflicts) / Cashierの既定ルート /stripe/webhook を使用（衝突回避のためカスタムコントローラーは作成しない）
  - Enforce idempotency by recording processed event.id / event.id を保存して多重処理を防止
  - Allowlist only expected event types / 想定イベントのみ許可（アロウリスト）
  - Purpose: Process Stripe webhook notifications via event listeners with count limit enforcement / 目的: 回数制限付きでイベントリスナー経由のStripe Webhook通知を処理
  - Requirements: 7.2 / 要件: 7.2
  - Dependencies: Task 19 / 依存関係: タスク19
  - Estimated time: 30 minutes / 推定時間: 30分

- [x] 32. Configure webhook CSRF exclusion / Webhook CSRF除外設定
  - File: bootstrap/app.php (modify) / ファイル: bootstrap/app.php (修正)
  - Exclude CSRF only for '/stripe/webhook' (use default Cashier route) / '/stripe/webhook' のみCSRF除外設定（既定のCashierルートを利用）
  - Webhookルートは POST のみ許可し、Cashierの署名検証を必須化（STRIPE_WEBHOOK_SECRET）
  - JSONのみ受理（Content-Type: application/json）
  - リスナーはキュー経由で非同期処理（長時間処理の同期応答を避ける）
  - 受信時の冪等性（event.id 記録）と再試行時の安全性を明記
    - 整合時/重複時: 200 または 204 を返却（常に2xxでリトライ抑止）
  - 署名検証失敗時: 400/401/403レスポンス
  - Webhook専用ログ/メトリクス（受信数、検証失敗、重複破棄件数）を追加
  - Do not apply global rate limiting to webhook route / Webhookにはグローバルなレート制限を適用しない
  - Purpose: Accept Stripe webhook notifications via default Cashier route / 目的: 既定のCashierルート経由でStripe Webhook通知を受け入れる
  - Requirements: 7.2 / 要件: 7.2
  - Dependencies: Task 31 / 依存関係: タスク31
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 33. Implement webhook event handlers / Webhookイベントハンドラーを実装
  - File: app/Listeners/ (implement listener logic) / ファイル: app/Listeners/ (リスナーロジック実装)
  - Handle subscription status changes via event listeners / イベントリスナー経由でサブスクリプションステータス変更を処理
  - Update user subscription records with lesson count enforcement / 回数制限付きでユーザーサブスクリプションレコードを更新
  - Purpose: Process subscription lifecycle events via listeners with count limit management / 目的: 回数制限管理付きでリスナー経由のサブスクリプションライフサイクルイベントを処理
  - Requirements: 7.2 / 要件: 7.2
  - Dependencies: Task 31 / 依存関係: タスク31
  - Estimated time: 40 minutes / 推定時間: 40分

### Reservation System Implementation Tasks
### 予約システム実装タスク

- [x] 34. Create Reservation model and migration / Reservationモデルとマイグレーションを作成
  - File: app/Models/Reservation.php (new) / ファイル: app/Models/Reservation.php (新規)
  - File: database/migrations/create_reservations_table.php (new) / ファイル: database/migrations/create_reservations_table.php (新規)
  - Define relationships and business logic methods / リレーションとビジネスロジックメソッドを定義
  - DB constraints: FK(user_id), FK(lesson_schedule_id), FK(user_subscription_id) with ON DELETE CASCADE / DB制約: FK(user_id), FK(lesson_schedule_id), FK(user_subscription_id) with ON DELETE CASCADE
  - Indexes:
    - (lesson_schedule_id, status, start_datetime)
    - (user_id, status, created_at)
    - start_datetime 単独（期間検索最適化）
  - Purpose: Create reservation data model / 目的: 予約データモデルを作成
  - Requirements: 8.1, 8.2 / 要件: 8.1, 8.2
  - Dependencies: None / 依存関係: なし
  - Estimated time: 20 minutes / 推定時間: 20分

- [x] 35. Extend LessonSchedule model / LessonScheduleモデルを拡張
  - File: app/Models/LessonSchedule.php (modify) / ファイル: app/Models/LessonSchedule.php (修正)
  - Add reservations relationship / 予約リレーションを追加
  - Add availability checking methods / 空き状況チェックメソッドを追加
  - Enforce subscription validity and lesson count limits (per month/period) / サブスク有効性と回数制限を強制
  - Check capacity and waitlist policy / 定員とウェイトリスト方針を評価
  - Provide atomic check-and-book API for concurrency / 競合対策の原子的APIを提供
  - Validate lesson count limits before allowing reservation / 予約許可前に回数制限をバリデーション
  - Purpose: Enable reservation functionality on lesson schedules with count enforcement / 目的: 回数制限付きでレッスンスケジュールの予約機能を有効化
  - Requirements: 8.3, 8.4 / 要件: 8.3, 8.4
  - Dependencies: Task 34 / 依存関係: タスク34
  - Estimated time: 15 minutes / 推定時間: 15分

- [x] 36. Create reservation controller / 予約コントローラーを作成
  - File: app/Http/Controllers/Admin/ReservationController.php (new) / ファイル: app/Http/Controllers/Admin/ReservationController.php (新規)
  - Implement CRUD operations for reservation management / 予約管理用のCRUD操作を実装
  - Add filtering and search functionality / フィルタリングと検索機能を追加
  - Handle reservation status updates with lesson count validation / 回数制限バリデーション付きで予約ステータス更新を処理
  - Enforce lesson count limits before allowing new reservations / 新規予約許可前に回数制限を強制
  - Authorization / 認可: 管理者専用（Policy or Gate: manage-reservations、ルートは ['auth','verified','can:admin']）
  - Eager Load / N+1回避: with(['user','lessonSchedule.lesson','userSubscription'])
  - Filters / フィルタ: user_id, lesson_schedule_id, status, date_from/date_to（基準: schedule.start_datetime）
  - Sorting / 並び順: start_datetime DESC, id DESC（安定ソート）
  - Pagination / ページネーション: 50/ページ（設定可能）
  - Status transitions / 状態遷移:
    - allowed: pending→confirmed, confirmed→{canceled, completed, no_show}
    - delete: canceled のみ削除可（翻訳キー: reservation.delete_only_canceled を使用）
  - Concurrency / 競合対策:
    - DB::transaction + 対象 LessonSchedule 行に FOR UPDATE
    - 収容数/締切/回数の再検証（TOCTOU回避）
    - ステータス更新は冪等（同一入力の多重実行防止用キー検討）
  - i18n / 国際化: created/updated/canceled/completed/no_show/ status_update_not_supported を定義して使用
  - Purpose: Admin interface for reservation management with count enforcement / 目的: 回数制限付きで予約管理用の管理者インターフェース
  - Requirements: 8.5 / 要件: 8.5
  - Dependencies: Task 34 / 依存関係: タスク34
  - Estimated time: 30 minutes / 推定時間: 30分

- [ ] 37. Add reservation routes / 予約ルートを追加
  - File: routes/web.php (modify) / ファイル: routes/web.php (修正)
  - Add admin routes for reservation management / 予約管理用の管理者ルートを追加
  - Add user routes for lesson booking with count limit validation / 回数制限バリデーション付きでレッスン予約用のユーザールートを追加
  - Purpose: Define URL routing for reservation features with count enforcement / 目的: 回数制限付きで予約機能のURLルーティングを定義
  - Requirements: 8.5 / 要件: 8.5
  - Dependencies: Task 36 / 依存関係: タスク36
  - Estimated time: 10 minutes / 推定時間: 10分

### Livewire Component Development Tasks
### Livewireコンポーネント開発タスク

- [ ] 38. Create reservation booking Livewire component / 予約Livewireコンポーネントを作成
  - File: app/Livewire/ReservationBooking.php (new) / ファイル: app/Livewire/ReservationBooking.php (新規)
  - Display available lessons and handle booking / 利用可能なレッスンを表示し予約を処理
  - Add subscription validation with lesson count limits / 回数制限付きサブスクリプション検証を追加
  - Display remaining lesson count and limit warnings / 残り回数と制限警告を表示
  - Enforce lesson count limits before allowing new reservations / 新規予約許可前に回数制限を強制
  - Purpose: User interface for lesson reservation with count limit management / 目的: 回数制限管理付きでレッスン予約用のユーザーインターフェース
  - Requirements: 8.1, 8.2 / 要件: 8.1, 8.2
  - Dependencies: Tasks 34, 35 / 依存関係: タスク34, 35
  - Estimated time: 45 minutes / 推定時間: 45分

- [ ] 39. Create reservation cancellation component / 予約キャンセルコンポーネントを作成
  - File: app/Livewire/ReservationCancellation.php (new) / ファイル: app/Livewire/ReservationCancellation.php (新規)
  - Handle reservation cancellation logic / 予約キャンセルロジックを処理
  - Check cancellation deadlines and permissions / キャンセル期限と権限をチェック
  - Update lesson count when cancellation is within deadline / 期限前キャンセル時に回数を更新
  - Purpose: User interface for reservation cancellation with count management / 目的: 回数管理付きで予約キャンセル用のユーザーインターフェース
  - Requirements: 8.4 / 要件: 8.4
  - Dependencies: Task 38 / 依存関係: タスク38
  - Estimated time: 25 minutes / 推定時間: 25分

- [ ] 40. Create reservation history component / 予約履歴コンポーネントを作成
  - File: app/Livewire/ReservationHistory.php (new) / ファイル: app/Livewire/ReservationHistory.php (新規)
  - Display user's reservation history / ユーザーの予約履歴を表示
  - Show upcoming and past reservations / 今後の予約と過去の予約を表示
  - Display lesson count usage and remaining lessons / 回数使用状況と残り回数を表示
  - Purpose: User interface for reservation history with count tracking / 目的: 回数追跡付きで予約履歴用のユーザーインターフェース
  - Requirements: 8.5 / 要件: 8.5
  - Dependencies: Task 38 / 依存関係: タスク38
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 40.1. Implement detailed error handling for reservation operations / 予約操作の詳細エラーハンドリングを実装
  - File: app/Http/Controllers/ReservationController.php (modify), app/Http/Controllers/Admin/ReservationController.php (modify) / ファイル: app/Http/Controllers/ReservationController.php (修正), app/Http/Controllers/Admin/ReservationController.php (修正)
  - Define specific error messages for reservation creation blocking scenarios / 予約作成ブロックシナリオ用の具体的なエラーメッセージを定義
  - Define specific error messages for reservation cancellation scenarios / 予約キャンセルシナリオ用の具体的なエラーメッセージを定義
  - Implement lesson count limit enforcement with user-friendly messages / ユーザーフレンドリーなメッセージで回数制限を強制
  - Handle subscription status validation with clear feedback / 明確なフィードバックでサブスクリプション状態バリデーションを処理
  - Add reservation deadline validation and messaging / 予約期限バリデーションとメッセージングを追加
  - Purpose: Provide clear user feedback for reservation operations with count limits / 目的: 回数制限付きで予約操作に対する明確なユーザーフィードバックを提供
  - Requirements: 8.3, 8.4, 8.5 / 要件: 8.3, 8.4, 8.5
  - Dependencies: Task 40 / 依存関係: タスク40
  - Estimated time: 25 minutes / 推定時間: 25分

### Notification System Implementation Tasks
### 通知システム実装タスク

- [ ] 41. Extend NotificationTemplate model / NotificationTemplateモデルを拡張
  - File: app/Models/NotificationTemplate.php (modify) / ファイル: app/Models/NotificationTemplate.php (修正)
  - Add template type constants and validation / テンプレートタイプ定数とバリデーションを追加
  - Add subscription event notification types (subscription.created, subscription.updated, subscription.deleted, payment.succeeded, payment.failed) / サブスクリプションイベント通知タイプを追加（subscription.created, subscription.updated, subscription.deleted, payment.succeeded, payment.failed）
  - Add lesson count limit warning notification types / 回数制限警告通知タイプを追加
  - Purpose: Support different notification types including subscription events and count limit warnings / 目的: サブスクリプションイベントと回数制限警告を含む異なる通知タイプをサポート
  - Requirements: 10.1, 10.2 / 要件: 10.1, 10.2
  - Dependencies: None / 依存関係: なし
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 42. Create notification service / 通知サービスを作成
  - File: app/Services/NotificationService.php (new) / ファイル: app/Services/NotificationService.php (新規)
  - Implement email sending with template substitution / テンプレート置換でメール送信を実装
  - Add methods for subscription event notifications (sendSubscriptionCreated, sendSubscriptionUpdated, sendSubscriptionDeleted, sendPaymentSucceeded, sendPaymentFailed) / サブスクリプションイベント通知メソッドを追加（sendSubscriptionCreated, sendSubscriptionUpdated, sendSubscriptionDeleted, sendPaymentSucceeded, sendPaymentFailed）
  - Add methods for lesson count limit warnings (sendCountLimitWarning, sendCountLimitReached) / 回数制限警告メソッドを追加（sendCountLimitWarning, sendCountLimitReached）
  - Implement idempotency: check (user_id, event_id, type) in notifications table to prevent duplicate sends / 冪等性実装: notificationsテーブルで(user_id, event_id, type)をチェックして重複送信を防止
  - Add rate limiting: throttle notification frequency per user/type to prevent spam / レート制限追加: ユーザー/タイプ別の通知頻度を制限してスパムを防止
  - Purpose: Centralized notification handling including subscription events and count limit warnings / 目的: サブスクリプションイベントと回数制限警告を含む集中化された通知処理
  - Requirements: 10.3, 10.4 / 要件: 10.3, 10.4
  - Dependencies: Task 41 / 依存関係: タスク41
  - Estimated time: 45 minutes / 推定時間: 45分

- [ ] 43. Implement notification templates / 通知テンプレートを実装
  - File: database/seeders/NotificationTemplateSeeder.php (new) / ファイル: database/seeders/NotificationTemplateSeeder.php (新規)
  - Create templates for reservation confirmation, reminders, cancellations / 予約確認、リマインダー、キャンセル用のテンプレートを作成
  - Create templates for subscription events (subscription.created, subscription.updated, subscription.deleted, payment.succeeded, payment.failed) / サブスクリプションイベント用テンプレートを作成（subscription.created, subscription.updated, subscription.deleted, payment.succeeded, payment.failed）
  - Create templates for lesson count limit warnings (count_limit_warning, count_limit_reached) / 回数制限警告用テンプレートを作成（count_limit_warning, count_limit_reached）
  - Seed system_settings with email_variables_whitelist for template variable validation / テンプレート変数バリデーション用のemail_variables_whitelistをsystem_settingsに投入
  - Ensure template variables match allowed whitelist from database schema / テンプレート変数がデータベーススキーマの許可ホワイトリストと一致することを確認
  - Purpose: Populate notification templates including subscription events and count limit warnings / 目的: サブスクリプションイベントと回数制限警告を含む通知テンプレートを投入
  - Requirements: 10.1 / 要件: 10.1
  - Dependencies: Task 41 / 依存関係: タスク41
  - Estimated time: 35 minutes / 推定時間: 35分

### Security Enhancement Tasks (Phase 2 Final)
### セキュリティ強化タスク (フェーズ2最終)

- [ ] 44. Implement comprehensive input validation / 包括的な入力バリデーションを実装
  - File: app/Http/Requests/ (review and enhance all) / ファイル: app/Http/Requests/ (すべてをレビュー・強化)
  - Purpose: Strengthen data validation across all forms / 目的: すべてのフォームでデータバリデーションを強化
  - Requirements: Security requirements / 要件: セキュリティ要件
  - Dependencies: None / 依存関係: なし
  - Estimated time: 30 minutes / 推定時間: 30分

- [ ] 45. Add rate limiting for critical endpoints / 重要なエンドポイントにレート制限を追加
  - File: app/Http/Kernel.php (modify) / ファイル: app/Http/Kernel.php (修正)
  - Apply throttle:login to auth, custom throttle to reservation create/cancel / 認証にloginスロットル、予約作成/取消に専用スロットル
  - Exclude Stripe webhook route from throttling / Webhookはスロットル除外
  - Purpose: Prevent abuse of authentication and booking endpoints / 目的: 認証と予約エンドポイントの悪用を防ぐ
  - Requirements: Security requirements / 要件: セキュリティ要件
  - Dependencies: None / 依存関係: なし
  - Estimated time: 15 minutes / 推定時間: 15分

- [ ] 46. Implement CSRF protection verification / CSRF保護検証を実装
  - File: resources/views/ (review all forms) / ファイル: resources/views/ (すべてのフォームをレビュー)
  - Verify admin blade forms include @csrf and method spoofing as needed / 管理画面フォームで@csrfとHTTPメソッド疑似化を確認
  - Purpose: Ensure all forms have proper CSRF tokens / 目的: すべてのフォームに適切なCSRFトークンがあることを保証
  - Requirements: Security requirements / 要件: セキュリティ要件
  - Dependencies: None / 依存関係: なし
  - Estimated time: 10 minutes / 推定時間: 10分

### Testing Implementation Tasks
### テスト実装タスク

- [ ] 47. Create instructor profile model tests / インストラクタープロフィールモデルテストを作成
  - File: tests/Unit/Models/InstructorProfileTest.php (new) / ファイル: tests/Unit/Models/InstructorProfileTest.php (新規)
  - Test instructor profile validation, relationships, and accessors / インストラクタープロフィールのバリデーション、リレーション、アクセサーをテスト
  - Purpose: Ensure instructor profile model reliability / 目的: インストラクタープロフィールモデルの信頼性を保証
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 5 / 依存関係: タスク5
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 48. Create instructor profile feature tests / インストラクタープロフィール機能テストを作成
  - File: tests/Feature/InstructorProfileTest.php (new) / ファイル: tests/Feature/InstructorProfileTest.php (新規)
  - Test complete instructor profile workflow (create, edit, delete) / 完全なインストラクタープロフィールワークフロー（作成、編集、削除）をテスト
  - Tests: end == next.start 非重複, start == existing.end 非重複, 部分重なりは重複
  - Tests: DST前後の1時間（繰上げ/繰下げ）ケース
  - Tests: 同一リクエスト内での多重重複検知（N^2比較の最適化も検証）
  - Purpose: Ensure end-to-end instructor profile functionality / 目的: エンドツーエンドのインストラクタープロフィール機能を保証
  - Requirements: Instructor Profile Management / 要件: インストラクタープロフィール管理
  - Dependencies: Task 8 / 依存関係: タスク8
  - Estimated time: 25 minutes / 推定時間: 25分

- [ ] 49. Create subscription model tests / サブスクリプションモデルテストを作成
  - File: tests/Unit/Models/SubscriptionPlanTest.php (new) / ファイル: tests/Unit/Models/SubscriptionPlanTest.php (新規)
  - Test subscription plan validation and relationships / サブスクリプションプランのバリデーションとリレーションをテスト
  - Tests: end == next.start 非重複, start == existing.end 非重複, 部分重なりは重複。
  - Tests: DST前後の1時間（繰上げ/繰下げ）ケース
  - Tests: 同一リクエスト内での多重重複検知（N^2比較の最適化も検証）
  - Purpose: Ensure subscription model reliability / 目的: サブスクリプションモデルの信頼性を保証
  - Requirements: 6.1, 7.1 / 要件: 6.1, 7.1
  - Dependencies: Task 23 / 依存関係: タスク23
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 50. Create reservation model tests / 予約モデルテストを作成
  - File: tests/Unit/Models/ReservationTest.php (new) / ファイル: tests/Unit/Models/ReservationTest.php (新規)
  - Test reservation validation and relationships / 予約のバリデーションとリレーションをテスト
  - Tests: end == next.start 非重複, start == existing.end 非重複, 部分重なりは重複
  - Tests: DST前後の1時間（繰上げ/繰下げ）ケース
  - Tests: 同一リクエスト内での多重重複検知（N^2比較の最適化も検証）
  - Purpose: Ensure reservation model reliability / 目的: 予約モデルの信頼性を保証
  - Requirements: 8.1, 8.2 / 要件: 8.1, 8.2
  - Dependencies: Task 34 / 依存関係: タスク34
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 51. Create webhook controller tests / Webhookコントローラーテストを作成
  - File: tests/Feature/WebhookTest.php (new) / ファイル: tests/Feature/WebhookTest.php (新規)
  - Test Stripe webhook processing / Stripe Webhook処理をテスト
  - Purpose: Ensure webhook reliability / 目的: Webhookの信頼性を保証
  - Requirements: 7.2 / 要件: 7.2
  - Dependencies: Task 31 / 依存関係: タスク31
  - Estimated time: 25 minutes / 推定時間: 25分

- [ ] 52. Create reservation feature tests / 予約機能テストを作成
  - File: tests/Feature/ReservationTest.php (new) / ファイル: tests/Feature/ReservationTest.php (新規)
  - Test complete reservation workflow / 完全な予約ワークフローをテスト
  - Purpose: Ensure end-to-end reservation functionality / 目的: エンドツーエンドの予約機能を保証
  - Requirements: 8.1-8.5 / 要件: 8.1-8.5
  - Dependencies: Tasks 34, 38 / 依存関係: タスク34, 38
  - Estimated time: 30 minutes / 推定時間: 30分

### Final Integration and Cleanup Tasks
### 最終統合・クリーンアップタスク

- [ ] 53. Update project overview documentation / プロジェクト概要ドキュメントを更新
  - File: docs/project-overview.md (modify) / ファイル: docs/project-overview.md (修正)
  - Update Phase 2 and 3 completion status / フェーズ2と3の完了状況を更新
  - Add implemented features to documentation / 実装された機能をドキュメントに追加
  - Purpose: Keep documentation current / 目的: ドキュメントを最新に保つ
  - Requirements: All / 要件: すべて
  - Dependencies: All completed tasks / 依存関係: すべての完了したタスク
  - Estimated time: 20 minutes / 推定時間: 20分

- [ ] 54. Run code formatting and linting / コードフォーマットとリンティングを実行
  - Command: `vendor/bin/pint` / コマンド: `vendor/bin/pint`
  - Purpose: Ensure code quality and consistency / 目的: コード品質と一貫性を保証
  - Requirements: All / 要件: すべて
  - Dependencies: All tasks / 依存関係: すべてのタスク
  - Estimated time: 5 minutes / 推定時間: 5分

- [ ] 55. Final testing and validation / 最終テストと検証
  - Run all tests: `php artisan test` / すべてのテストを実行: `php artisan test`
  - Manual testing of key user flows / 主要ユーザーフローの手動テスト
  - Performance validation / パフォーマンス検証
  - Purpose: Ensure system readiness / 目的: システムの準備完了を保証
  - Requirements: All / 要件: すべて
  - Dependencies: All tasks / 依存関係: すべてのタスク
  - Estimated time: 45 minutes / 推定時間: 45分

## Task Dependencies and Execution Order
## タスク依存関係と実行順序

### Phase 1A: Complete Foundation Setup (Tasks 1-17)
### フェーズ1A: 基盤完成セットアップ (タスク1-17)
Execute in order: 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → 9 → 10 → 11 → 12 → 13 → 14 → 15 → 16 → 17
順次実行: 1 → 2 → 3 → 4 → 5 → 6 → 7 → 8 → 9 → 10 → 11 → 12 → 13 → 14 → 15 → 16 → 17

### Phase 1B: Stripe Setup (Tasks 18-21)
### フェーズ1B: Stripeセットアップ (タスク18-21)
Execute in order: 18 → 19 → 20 → 21
順次実行: 18 → 19 → 20 → 21

### Phase 1C: Stripe Product and Price Management (Tasks 22-24)
### フェーズ1C: Stripeプロダクト・価格管理 (タスク22-24)
Execute in order: 22 → 23 → 24
順次実行: 22 → 23 → 24

### Phase 1D: Checkout Integration (Tasks 25-27)
### フェーズ1D: Checkout統合 (タスク25-27)
Execute in order: 25 → 26 → 27
順次実行: 25 → 26 → 27

### Phase 1E: Advanced Subscription Management (Tasks 28-30.2)
### フェーズ1E: 高度なサブスクリプション管理 (タスク28-30.2)
Execute in order: 28 → 29 → 30 → 30.1 → 30.2
順次実行: 28 → 29 → 30 → 30.1 → 30.2

### Phase 1F: Webhook Processing (Tasks 31-33)
### フェーズ1F: Webhook処理 (タスク31-33)
Execute in order: 31 → 32 → 33
順次実行: 31 → 32 → 33

### Phase 2A: Reservation Core (Tasks 34-37)
### フェーズ2A: 予約コア (タスク34-37)
Execute in order: 34 → 35 → 36 → 37
順次実行: 34 → 35 → 36 → 37

### Phase 2B: Livewire Components (Tasks 38-40)
### フェーズ2B: Livewireコンポーネント (タスク38-40)
Execute in order: 38 → 39 → 40
順次実行: 38 → 39 → 40

### Phase 2C: Notifications (Tasks 41-43)
### フェーズ2C: 通知 (タスク41-43)
Execute in order: 41 → 42 → 43
順次実行: 41 → 42 → 43

### Phase 2D: Security Enhancement (Tasks 44-46)
### フェーズ2D: セキュリティ強化 (タスク44-46)
Execute in order: 44 → 45 → 46
順次実行: 44 → 45 → 46

### Phase 3: Testing (Tasks 47-52)
### フェーズ3: テスト (タスク47-52)
Can be executed in parallel after respective features are complete
対応する機能完了後に並列実行可能

### Phase 4: Finalization (Tasks 53-55)
### フェーズ4: 最終化 (タスク53-55)
Execute in order: 53 → 54 → 55
順次実行: 53 → 54 → 55

## Risk Mitigation
## リスク軽減

### Technical Risks
### 技術的リスク
- **Stripe API changes**: Monitor Stripe documentation for breaking changes
  - **Stripe API変更**: 破壊的変更についてStripeドキュメントを監視
- **Webhook security**: Implement signature validation and replay attack prevention
  - **Webhookセキュリティ**: 署名検証とリプレイ攻撃防止を実装
- **Concurrent booking conflicts**:
    - Use DB transactions with SELECT ... FOR UPDATE on the target schedule row(s) and affected reservation rows
    - Status updates/cancel 時も同様にロックし、回数カウンタ更新は同一Tx内で実施
    - Admin 操作の再送に備え、操作単位の idempotency key を記録
    - Add covering index on (lesson_id, start_datetime, end_datetime)
    - Consider unique constraint to prevent exact-duplicate schedules
    - Record idempotency keys for client retries
  - **同時予約競合**:
    - 対象スケジュール行にSELECT ... FOR UPDATEでDBトランザクション使用
    - (lesson_id, start_datetime, end_datetime)のカバリングインデックス追加
    - 完全重複スケジュール防止の一意性制約を検討
    - クライアントリトライ用の冪等性キーを記録

### Business Risks
### ビジネスリスク
- **Payment processing failures**: Implement retry mechanisms and user notifications
  - **決済処理失敗**: リトライメカニズムとユーザー通知を実装
- **Subscription billing issues**: Regular reconciliation between local and Stripe data
  - **サブスクリプション請求問題**: ローカルとStripeデータの定期的な照合
- **Data consistency**: Use database transactions for critical operations
  - **データ一貫性**: 重要な操作にデータベーストランザクションを使用

## Success Criteria
## 成功基準

### Functional Completion
### 機能完成
- [ ] Users can subscribe to plans via Stripe Checkout
  - [ ] ユーザーはStripe Checkout経由でプランにサブスクライブ可能
- [ ] Webhooks properly update subscription status
  - [ ] Webhookがサブスクリプションステータスを適切に更新
- [ ] Users can book lessons according to subscription limits
  - [ ] ユーザーはサブスクリプション制限に従ってレッスンを予約可能
- [ ] Users can switch between plans within the same category with remaining lessons transfer
  - [ ] ユーザーは同じカテゴリー内でプランを切り替え可能（残り回数引継ぎ）
- [ ] Category-based independent subscription management works correctly
  - [ ] カテゴリー別独立サブスクリプション管理が正常に動作
- [ ] Admin can manage reservations and subscriptions
  - [ ] 管理者は予約とサブスクリプションを管理可能
- [ ] Admin can filter/search reservations by user/schedule/status/date with p95 < 500ms
  - [ ] 管理者はユーザー/スケジュール/状態/日付で予約を検索でき、p95 < 500ms を満たす
- [ ] Routes are scoped and named as 'admin.reservations.*' under proper middleware
  - [ ] ルートは適切なミドルウェア下で 'admin.reservations.*' に統一
- [ ] Notifications are sent for all relevant events
  - [ ] 関連する全てのイベントで通知が送信される
- [ ] Instructor profiles can be created, edited, and managed
  - [ ] インストラクタープロフィールが作成、編集、管理可能
- [ ] Image uploads work correctly with proper validation
  - [ ] 画像アップロードが適切なバリデーションで正常に動作

### Quality Assurance
### 品質保証
- [ ] All tests pass (unit, feature, integration)
  - [ ] 全てのテストが合格 (単体、機能、統合)
- [ ] Code follows Laravel and project conventions
  - [ ] コードがLaravelとプロジェクト規約に従う
- [ ] Documentation is updated and accurate
  - [ ] ドキュメントが更新され正確
- [ ] Performance meets requirements (p95 < 500ms for booking/search endpoints under X users, Y schedules)
  - [ ] パフォーマンスが要件を満たす (予約/検索エンドポイントでp95 < 500ms、Xユーザー、Yスケジュール下)
- [ ] Security measures are implemented and tested
  - [ ] セキュリティ対策が実装されテスト済み

### User Experience
### ユーザーエクスペリエンス
- [ ] Intuitive reservation booking process
  - [ ] 直感的な予約プロセス
- [ ] Clear error messages and validation feedback
  - [ ] 明確なエラーメッセージとバリデーションフィードバック
- [ ] Responsive design works on mobile devices
  - [ ] レスポンシブデザインがモバイルデバイスで動作
- [ ] Email notifications are properly formatted and timely
  - [ ] メール通知が適切にフォーマットされタイムリー
