# ヨガ・ピラティス教室予約システム - プロジェクト概要

## プロジェクト概要

### アプリケーションの目的
ヨガやピラティス等の教室の予約を自動化するシステムです。

### 主要機能
- **月謝制予約システム**: Stripeによるサブスクリプション決済
- **複数店舗対応**: 店舗ごとのレッスン管理
- **レッスン種類**: パーソナル・グループレッスン対応
- **予約管理**: 月謝プランに応じた予約回数制限

## 技術スタック

### バックエンド
- **PHP**: ^8.2 (実際の環境: 8.4.12)
- **Laravel Framework**: ^12.0 (実際のバージョン: 12.26.3)
  - ※ 基準日: 2025-09-22
- **データベース**:
  - 開発環境: SQLite
  - 本番環境: MySQL

### フロントエンド
- **基本**: Laravel Blade（サーバーサイドレンダリング）
- **動的機能**: Livewire（必要な部分のみ）
- **クライアントサイド**: Alpine.js（Livewireに含まれる）

### 決済システム
- **Stripe**: サブスクリプション決済
- **Laravel Cashier**: Stripe統合ライブラリ
- **Stripe Checkout**: 決済処理
- **Webhook**: 自動同期

#### Stripe連携とプラン紐付け（指針）
- プラン定義はアプリ（`subscription_plans`）に保持し、請求は常に`stripe_price_id`を用いる（アプリ側の`price`は表示用）
- Stripeで作成したProduct/PriceのID（`stripe_product_id`/`stripe_price_id`）を管理画面からプランに登録し、一意制約を付与する
- **管理画面での自動入力機能**: `stripe_price_id`入力時にStripe APIから価格を自動取得し、`price`フィールドに自動入力する（サーバーサイドAPI経由・認可必須・レート制限適用: Gate `manage-subscription-plans`、throttle設定）
- 決済はStripe Checkoutを優先（保存済みPM向けの`->create()`ではなく`->checkout([...])`を使用）
- 契約作成後はWebhookで同期：`checkout.session.completed`/`customer.subscription.created`/`invoice.paid`/`invoice.payment_failed`などを処理
- 利用回数リセットは`invoice.paid`で当期開始時に`current_month_used_count=0`へ更新
- 監査性確保のため、`user_subscriptions`に当期の`stripe_price_id`（任意で`monthly_quota`）も保存
- 管理画面入力バリデーション：`stripe_product_id`は`/^prod_/`、`stripe_price_id`は`/^price_/`にマッチ必須

### 開発ツール
- **Laravel Herd**: 開発環境として使用
- **Laravel Boost**: ^1.0
- **Laravel Pint**: ^1.24
- **Laravel Sail**: ^1.41
- **Laravel Pail**: ^1.2.2
- **Pest**: ^4.0
- **Livewire**: ^3.6
- **Tailwind CSS**: ^3.1.0

## システム設計

### データベース設計（実装完了）

#### 実装済みテーブル構成
```
users (ユーザー) ✅
├── id, name, email, password, role (user/instructor/admin), email_verified_at, created_at, updated_at
├── リレーション: taughtLessons, subscriptions, reservations, favorites

stores (店舗) ✅
├── id, name, address, phone, access_info, google_map_url, parking_info, notes, is_active, created_at, updated_at
├── リレーション: lessons

lesson_categories (レッスンカテゴリ) ✅
├── id, parent_id, name, description, is_active, sort_order, created_at, updated_at
├── リレーション: parent, children, lessons
├── 階層構造: 親→子（2階層対応）
├── 親カテゴリ制約: 「グループレッスン」「パーソナルレッスン」のみ（削除不可）
├── 子カテゴリ例: ヨガ、ピラティス、ストレッチ、筋トレなど

subscription_plans (月謝プラン) ✅
├── id, name, price, lesson_count, allowed_category_ids (JSON), stripe_product_id, stripe_price_id, description, is_active, created_at, updated_at
├── リレーション: userSubscriptions
├── 制約: stripe_product_id, stripe_price_id に一意制約

user_subscriptions (ユーザーの月謝契約) ✅
├── id, user_id, plan_id, stripe_subscription_id, status, payment_status, failure_reason, current_period_start, current_period_end, current_month_used_count, remaining_lessons, created_at, updated_at
├── リレーション: user, plan, reservations
├── 機能: 回数制限管理、プラン切り替え時の残数移行、Webhook同期
├── 回数管理仕様: remaining_lessons が真実源（表示・制限判定に使用）
├── 整合性規約:
│   ├── remaining_lessons が非NULLのときのみ残数判定に使用し、used_countは派生値
│   ├── 残数更新はトランザクション内で行う（予約作成/キャンセル）
│   └── Webhookでのリセット時の順序（reset → reconcile）を規定
├── リセットタイミング: invoice.paid イベントで current_month_used_count=0 にリセット
├── プラン切替: 残数は新プランの lesson_count に再計算（繰越なし）

lessons (レッスン) ✅
├── id, store_id, name, category_id, instructor_user_id, duration, capacity, booking_deadline_hours, cancel_deadline_hours, is_active, created_at, updated_at
├── リレーション: store, category, instructor, schedules

lesson_schedules (レッスンスケジュール) ✅
├── id, lesson_id, start_datetime, end_datetime, current_bookings, is_active, created_at, updated_at
├── リレーション: lesson, reservations

reservations (予約) ✅
├── id, user_id, lesson_schedule_id, user_subscription_id, status, reserved_at, created_at, updated_at
├── リレーション: user, lessonSchedule, userSubscription
├── 制約: 二重予約防止（user_id + lesson_schedule_id + status のユニーク制約）
├── 再予約対応: キャンセル/完了後の再予約を許容（ステータス込み制約）
├── 機能: 予約制約バリデーション、キャンセル期限チェック、ビジネスロジック

user_favorites (ユーザーお気に入り) ✅
├── id, user_id, favoritable_type, favoritable_id, created_at, updated_at
├── リレーション: user, favoritable (多態的関連)
├── 制約: 同一ユーザーの重複お気に入り防止

notification_templates (通知テンプレート) ✅
├── id, name, type, subject, body_text, variables (JSON), is_active, created_at, updated_at
├── リレーション: notifications
├── 制約: type は固定集合
    （reservation_confirmation, reminder, cancellation, subscription_update,
      subscription.created, subscription.updated, subscription.deleted,
      payment.succeeded, payment.failed,
      count_limit_warning, count_limit_reached）かつ一意制約
├── DB制約: MySQL 8.0系なら CHECK 制約または別テーブルによる参照整合を推奨
├── 本文: テキストのみ（HTML なし）
├── 変数置換: {{users_name}}, {{lessons_name}}, {{stores_name}}, {{lesson_schedules_start_datetime}} など
├── 変数管理: システム設定でテーブル別に許可リスト管理、テンプレート作成時は自動適用（手動入力不可）
├── 利用可能変数: データベーススキーマから動的取得、機密カラム（password等）は除外

notifications (通知履歴) ✅
├── id, user_id, template_id, type, subject, body, sent_at, read_at, created_at, updated_at
├── リレーション: user, template
├── 制約: 通知の送信履歴・既読管理

system_settings (システム設定) ✅
├── id, key (unique), value (text), type (text), description (nullable), created_at, updated_at
├── 用途: アプリケーション全体の設定値管理
├── 主要設定: email_variables_whitelist（メール通知で利用可能な変数リスト）
├── データ型: JSON、テキスト、数値など（typeフィールドで管理）

plan_switch_logs (プラン切り替えログ) ✅
├── id, user_id, from_plan_id, to_plan_id, remaining_lessons_hint, stripe_checkout_session_id, remaining_calculated_at, meta (JSON), created_at, updated_at
├── 用途: プラン切り替え時の残数移行記録と監査
├── 機能: 残数計算の透明性確保、Stripe Checkout Session追跡
├── 推奨インデックス: (user_id, created_at DESC)
├── 一意制約: stripe_checkout_session_id の一意制約（冪等性）
├── イベント相関: webhook_events(event_id UNIQUE) で重複処理抑止を推奨
```

### 機能設計

#### ユーザー側機能
- アカウント登録・ログイン
- 月謝プラン選択・契約（Stripe決済）
- レッスン予約・キャンセル
- 予約履歴・利用状況確認
- 店舗・レッスン検索・表示
- お気に入り機能（店舗・インストラクター）

#### インストラクター側機能
- 自分のレッスン予約枠の作成・編集・削除
- 自分のレッスンへの予約一覧確認

#### 管理者側機能
- 店舗管理（複数店舗対応）
- レッスンカテゴリ管理（親カテゴリ・子カテゴリ階層）
- レッスン管理（パーソナル・グループ）
- 月謝プラン管理
- 予約状況確認・管理
- ユーザー・インストラクター管理
- インストラクタープロフィール管理（専用テーブル）

## 詳細要件

### 月謝プラン詳細

#### グループレッスン（ピラティス・ヨガ）
- **月1回券サブスク**: 3,300円（allowed_category_ids: [1, 2]）
- **月2回券サブスク**: 6,000円（allowed_category_ids: [1, 2]）
- **月4回券サブスク**: 11,000円（allowed_category_ids: [1, 2]）

#### パーソナルレッスン（パーソナルレッスン）
- **月1回券サブスク**: 4,000円（allowed_category_ids: [4]）
- **月2回券サブスク**: 8,000円（allowed_category_ids: [4]）
- **月4回券サブスク**: 12,000円（allowed_category_ids: [4]）

#### プラン仕様
- **支払い方法**: 毎月払い（契約日から1ヶ月ごと）
- **未使用分**: 繰り越しなし（月次リセット）
- **複数契約**: 1つのアカウントで複数プラン契約可能
- **予約代理**: 家族・友人への代理予約不可
- **レッスン制限**: サブスクリプションのカテゴリに応じたレッスンのみ予約可能

#### プラン制約
- **lesson_count**: 1以上（空や0は不可）
- **price**: 1円以上

### レッスン仕様

#### 基本仕様
- **基本時間**: 60分（設定可能）
- **レッスンカテゴリ**: 階層構造で管理
  - **親カテゴリ**: 「グループレッスン」「パーソナルレッスン」（固定・削除不可）
  - **子カテゴリ**: ヨガ、ピラティス、ストレッチ、筋トレなど（自由に追加・編集・削除可能）
- **定員**: レッスンごとに個別設定
- **インストラクター**: レッスン内容により変更

#### 予約ルール
- **予約可能期間**: 各レッスン毎に設定可能、デフォルト24時間前
  - 例：9月15日 10:00開始のレッスン → 9月14日 10:00まで予約可能
  - 予約作成時点から予約可能、期限を過ぎると予約不可
- **キャンセル期限**: 各レッスン毎に設定可能、デフォルト24時間前
  - 期限を過ぎた場合：管理者・インストラクターのみキャンセル可能
  - ユーザー側：キャンセルボタンを無効化（押せない状態）
- **予約制限**: 同じ時間帯の重複予約防止
- **複数プラン契約時**: プラン間で予約可能内容が重複しないよう設計
  - 万が一重複した場合：ユーザーが選択（UIは実装時に決定）

### 権限管理

#### ユーザー権限
- ロール定義は「認証・権限・UI要件 > ロール定義」を参照

#### セキュリティ対策
- **ロール変更制限**: roleフィールドはfillableに含めず、専用メソッドでのみ変更可能
- **認可制御**: Gates/Policiesによる多層防御
  - Gate定義（`App\Providers\AppServiceProvider`）
    - `access-dashboard`: 管理者/インストラクターのみ
    - `access-admin`: 管理者のみ
    - `access-instructor`: インストラクター/管理者
    - `manage-subscription-plans`: 管理者のみ
  - Policy実装（自動ディスカバリ）
    - `App\Policies\SubscriptionPlanPolicy`
      - `before`: 管理者は全許可
      - `viewAny`/`view`: 全ユーザー許可
      - `create`/`update`/`delete`/`restore`/`forceDelete`: 非管理者は不可
- **ルート保護**: ミドルウェアによる権限チェック
- **監査ログ**: ロール変更時の履歴記録
- **CSRF保護**: セッション設定によるCSRF攻撃防止
- **XSS防止**: Bladeテンプレートの自動エスケープ
- **SQLインジェクション防止**: Eloquent ORMのパラメータバインディング
- **セッションセキュリティ**: HTTPS専用・HTTPOnly・SameSite設定
- **レート制限**: API・フォーム送信の頻度制限
- **入力検証**: Form Requestによる厳密なバリデーション

### インストラクタープロフィール（専用テーブル方式）

- 目的: インストラクターのみ「画像・自己紹介・資格・備考」を保持・編集（管理者は全員分作成・編集・削除）
- DB: `instructor_profiles`（`users` と1対1）
  - カラム:
    - `id`
    - `user_id`（unique, FK -> users.id, on delete cascade）
    - `image_path`（string, nullable）保存先は `public/instructors/`
    - `bio`（text, nullable）自己紹介
    - `qualifications`（text, nullable）資格（複数は改行区切り）
    - `notes`（text, nullable）備考
    - `created_at`, `updated_at`
- モデル/リレーション:
  - `User::instructorProfile()`（HasOne）
  - `InstructorProfile::user()`（BelongsTo）
- 画像バリデーション:
  - 拡張子: jpg, jpeg, png, webp
  - 最大サイズ: 10MB（例: `max:10240`）
- テキストバリデーション（例）:
  - `bio`, `qualifications`, `notes`: `nullable|string|max:2000`
- ストレージ:
  - `Storage::disk('public')` を使用し `instructors/` へ保存（`php artisan storage:link` 必須）
- 画面/ルート:
  - 講師本人: `GET /instructor/profile`, `PUT /instructor/profile`
  - 管理側: `GET /admin/instructors/{instructor}/edit`, `PUT /admin/instructors/{instructor}`
- 権限:
  - 作成/削除: 管理者のみ
  - 編集: インストラクター本人（自身のみ）または管理者

## 認証・権限・UI要件

- **認証状態**: 全ページログイン必須
- **権限別表示**: ユーザー向けページは内容共通（ロールによる表示内容変更なし）
- **UI設計方針**:
  - 管理画面：PC優先・レスポンシブ対応
  - ユーザー画面：モバイルファースト・PCでも違和感の少ないデザイン

### ロール定義
- **一般ユーザー** (role: user): 予約・キャンセル・履歴確認
- **インストラクター** (role: instructor): 自分のレッスン管理・予約一覧確認
- **管理者** (role: admin): 全機能管理

#### ログイン後の遷移ルール（実装）
- 一般ユーザー（role: user）: `home` ルート（`/` トップページ）に固定遷移（`intended` は無視）
- 管理者 / インストラクター（role: admin|instructor）: `dashboard`

## 実装計画

### Phase 1: 基盤構築
- [x] Livewireインストール・設定
- [x] ユーザー認証システム（Laravel Breeze/Fortify）
- [x] 認証ミドルウェア設定（全ページログイン必須）
- [x] 権限管理システム（Gates/Policies）
- [x] データベース設計・マイグレーション
- [x] 基本的なCRUD機能
  - [x] stores（店舗）CRUD
  - [x] lesson_categories（レッスンカテゴリ）CRUD
  - [x] lessons（レッスン）CRUD
  - [x] lesson_schedules（レッスンスケジュール）CRUD
  - [x] notification_templates（通知テンプレート）CRUD
  - [x] system_settings（システム設定）CRUD
  - [x] instructors（インストラクター）CRUD
  - [x] instructor_profiles（インストラクタープロフィール）CRUD（専用テーブル）
- [x] レッスンスケジュール機能の改善
  - [x] 一括作成機能（同じレッスンで複数スケジュールを一度に作成）
  - [x] 時間重複チェック機能（同じレッスンで時間が重複するスケジュールの防止）
  - [x] 検索・フィルタリング機能（日付やレッスンでの絞り込み）
- [x] 管理画面UI（PC優先・レスポンシブ対応）
  - [x] より使いやすい管理画面の実装
  - [x] ダッシュボードでの予約状況可視化（定員に対する予約数の表示改善）

### Phase 2: 月謝システム
- [x] Laravel Cashierインストール・設定
  - [x] Cashierマイグレーション公開: `php artisan vendor:publish --tag="cashier-migrations"`
  - [x] マイグレーション実行: `php artisan migrate`
  - [x] Stripeキー設定: `.env(.example)` に `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` を追加
- [x] Stripe Products & Prices設定
  - [x] 管理画面でのStripe Price ID自動取得機能
  - [x] 価格バリデーション（JPY、recurring、active状態チェック）
- [x] Stripe Checkout統合
  - [x] サブスクリプション作成・更新・キャンセル機能
  - [x] プラン切り替え時の残数移行機能
- [x] Webhook設定・自動同期
  - [x] StripeWebhookListener実装
  - [x] 専用キュー（stripe-webhooks）での処理
  - [x] 冪等性を保つWebhook処理
  - [x] 回数制限管理とWebhook同期
  - [x] 時刻境界の注意（Stripe period_start 基準、JST表示とのズレ回避）
  - [x] 運用要件:
    - [x] Stripe-Signature 検証必須（署名の時刻猶予も指定）
    - [x] event.id の一意記録で再送対策
    - [x] stripe-webhooks キューの専用ワーカー/再試行ポリシー（最大試行、バックオフ）
- [x] サブスクリプション管理
  - [x] 回数制限管理機能（remaining_lessons、current_month_used_count）
  - [x] 月次リセット（invoice.paid で current_month_used_count=0 にリセット）
  - [x] プラン切替時の残数再計算（新プランの lesson_count に設定）
  - [x] プラン切り替え時の残数移行ログ
- [x] subscription_plans（月謝プラン）CRUD実装
  - [x] 管理画面でのプラン管理
  - [x] Stripe連携バリデーション
- [x] 決済失敗時のエラーハンドリング
  - [x] Webhook経由での決済失敗処理
  - [x] エラーログとユーザー通知

### Phase 3: 予約システム
- [x] 予約データモデル構築
  - [x] Reservationモデルとマイグレーション作成
  - [x] 二重予約防止制約（user_id + lesson_schedule_id + status のユニーク制約）
  - [x] 予約制約バリデーション機能
  - [x] キャンセル期限チェック機能
  - [x] サーバー側バリデーション強制（UI無効化に加えて）
- [ ] レッスン予約機能（Livewire）
- [ ] 予約制限・重複チェック
- [ ] 時間帯重複防止チェック
- [ ] 定員制限チェック
- [ ] 同一ユーザー重複防止
- [ ] 月謝制限チェック
- [ ] エラーハンドリング（ユーザーフレンドリーなメッセージ）
- [ ] キャンセル機能
- [ ] 通知機能（メール送信）
- [ ] リマインダー機能（24時間前）
- [ ] お気に入り機能（店舗・インストラクター）

### Phase 3.5: セキュリティ強化
- [ ] 包括的な入力バリデーション実装
- [ ] 重要なエンドポイントへのレート制限追加
- [ ] CSRF保護検証実装（Webhook用CSRF除外設定は /stripe/webhook のみ）

### Phase 4: ユーザー向けUI基盤構築
- [ ] ユーザー画面UI（モバイルファースト）
  - [ ] レスポンシブデザイン（モバイル・タブレット・デスクトップ対応）
  - [ ] モバイルファーストUIコンポーネント
  - [ ] タッチ操作最適化
  - [ ] パフォーマンス最適化（画像遅延読み込み、キャッシュ）

### ユーザー向けページ実装計画

#### 基本ページ構成
1. **トップページ** (`/`)
2. **認証ページ** (`/login`, `/register`, `/forgot-password`, `/verify-email`)
3. **予約ページ** (`/reservations/{category}`)
   - 基本URL: `/reservations/{category}`（personal|group）
   - **グループレッスン** (`/reservations/group`):
     - 絞り込み機能（LAVAアプリ風）:
       - 店舗選択タブ（お気に入り店舗・全店舗・組み合わせ条件）
       - 組み合わせ条件: 店舗とレッスン内容の組み合わせ
       - 店舗ボタン（横スクロール可能）:
         - デフォルト: お気に入り店舗を全て表示
         - お気に入りなし: 店舗IDが低い順で表示
       - 日付選択（カレンダー表示・週単位ナビゲーション）
       - 時間帯表示（6時〜12時などの縦軸）
     - レッスン一覧表示:
       - デフォルト表示:
         - お気に入り店舗あり: お気に入り店舗全ての1週間分（店舗ID昇順）
         - お気に入りなし: 店舗ID最低の店舗の1週間分
       - 最大表示数: 15個
       - 時間帯別グループ化（17時、19時など）
       - レッスンカード（詳細情報 + アクションボタン）
       - 空き状況表示（オレンジ：空きあり、赤：満員/キャンセル待ち）
       - 予約状況（予約・キャンセル待ち予約）

   - **パーソナルレッスン** (`/reservations/personal`):
     - 絞り込み機能（LAVAアプリ風）:
       - インストラクター選択タブ（お気に入りインストラクター・全インストラクター・組み合わせ条件）
       - 組み合わせ条件: インストラクターと店舗の組み合わせ
       - インストラクターボタン（横スクロール可能）:
         - デフォルト: お気に入りインストラクターを全て表示
         - お気に入りなし: インストラクターIDが低い順で表示
       - 日付選択（カレンダー表示・週単位ナビゲーション）
       - 時間帯表示（6時〜12時などの縦軸）
     - レッスン一覧表示:
       - デフォルト表示:
         - お気に入りインストラクターあり: お気に入りインストラクター全ての予約枠（インストラクターID昇順）
         - お気に入りなし: 今日から日付の近い順で表示
       - 最大表示数: 15個
       - 時間帯別グループ化（17時、19時など）
       - レッスンカード（詳細情報 + アクションボタン）
       - 空き状況表示（オレンジ：空きあり、赤：満員/キャンセル待ち）
       - 予約状況（予約・キャンセル待ち予約）
4. **店舗一覧・詳細** (`/stores`, `/stores/{store}`)
5. **インストラクター一覧・詳細** (`/instructors`, `/instructors/{instructor}`)
6. **マイページ** (`/profile`)
7. **予約履歴詳細** (`/reservations/history`)
8. **サブスクリプション管理** (`/subscriptions`)
9. **お気に入り管理** (`/favorites`)

#### 実装上の共通方針
- 「Phase 3.5: ユーザー向けUI基盤構築」を参照

### Phase 4: 管理者・インストラクター向けページ実装計画

#### 管理者専用ページ
1. **管理者ダッシュボード** (`/dashboard`)
2. **店舗管理** (`/admin/stores`)
3. **レッスンカテゴリ管理** (`/admin/lesson-categories`)
   - 親カテゴリ：「グループレッスン」「パーソナルレッスン」（固定・削除不可）
   - 子カテゴリ：ヨガ、ピラティス、ストレッチ、筋トレなどの作成・編集・削除
   - 階層表示・並び順変更（ドラッグ&ドロップ）
4. **レッスン管理** (`/admin/lessons`)
5. **レッスンスケジュール管理** (`/admin/lesson-schedules`)
6. **月謝プラン管理** (`/admin/subscription-plans`) ✅
7. **通知テンプレート管理** (`/admin/notification-templates`) ✅
8. **ユーザー管理** (`/admin/users`)
9. **グループ予約管理** (`/admin/group-reservations`)
   - グループレッスン予約のCRUD操作（店舗運営向け）
   - 店舗・インストラクター・日時・ステータスでのフィルタリング
   - 予約状況の監視・管理
10. **パーソナル予約管理** (`/admin/personal-reservations`)
    - パーソナルレッスン予約のCRUD操作（個人指導向け）
    - インストラクター・日時・ステータスでのフィルタリング
    - 予約状況の監視・管理
11. **サブスクリプション管理** (`/admin/subscriptions`)
12. **システム設定** (`/admin/settings`) ✅

#### インストラクター専用ページ
1. **インストラクターダッシュボード** (`/instructor/dashboard`)
2. **自分のレッスン管理** (`/instructor/lessons`)
3. **予約確認** (`/instructor/reservations`)

#### 技術実装ポイント
- **権限管理**: ロール別アクセス制御（Gates/Policies）
- **データテーブル**: 大量データの効率的表示・検索
- **リアルタイム更新**: Livewireによる予約状況の即座反映
- **バッチ処理**: 一括操作・スケジュール作成
- **レポート機能**: 統計情報・CSVエクスポート
- **監査ログ**: 管理者操作の履歴記録

## 重要な考慮事項

### ビジネスロジック
- 予約に関する詳細ルールは「レッスン仕様 > 予約ルール」を参照
- サブスクのカテゴリ制限は「月謝プラン詳細 > プラン仕様」を参照
- 店舗間の予約: 複数店舗での予約管理
- 月次リセット: 契約日から1ヶ月ごとの請求サイクルで未使用回数をリセット

### 技術的考慮事項
- **Stripe統合（Checkout / Webhook）**: [決済システム](#決済システム)を参照
- **予約の同時性**: 同時予約時の競合処理（定員・重複・月謝制限チェック）
- **エラーハンドリング**: ユーザーフレンドリーなエラーメッセージ
- **決済失敗処理**: 段階的リトライ・手動再試行・画面通知
- **通知機能**: 予約確認・リマインダー
- **レポート機能**: 利用状況・売上レポート
- **セキュリティ**: 多層防御による権限制御
- **バージョン管理**: GitHubによるコード管理・共同開発
- **データ管理**: 削除されたレッスンは物理削除、キャンセルされた予約データは保持

## 技術選択の理由

### Livewire選択の理由
1. **PHPのみで開発**: JavaScriptの知識が最小限で済む
2. **リアルタイム更新**: 予約状況の即座反映
3. **シンプルな学習曲線**: Laravel開発者にとって親しみやすい
4. **Alpine.js統合**: クライアントサイドの細かい制御も可能

### Laravel Cashier + Stripe Checkout選択の理由
1. **標準的なアプローチ**: Laravel Cashierの推奨方法
2. **自動同期**: Webhookでデータベースが自動更新
3. **セキュリティ**: 決済情報をアプリケーションで保持しない
4. **開発効率**: 実装時間が大幅に短縮（4-6時間 vs 26-38時間）
5. **保守性**: 長期的な運用が容易

## 通知・コミュニケーション（確定）

### 通知の種類
- **予約確認メール**: 予約完了時に送信
- **予約リマインダー**: 24時間前に送信
- **キャンセル通知**: キャンセル時に送信
- **サブスク更新通知**: Stripeからの通知 + アプリからの通知

### 通知テンプレート管理
- **テンプレート種別**: 固定集合（ドット区切り推奨）
  - reservation_confirmation, reminder, cancellation, subscription_update,
    subscription.created, subscription.updated, subscription.deleted,
    payment.succeeded, payment.failed,
    count_limit_warning, count_limit_reached
- **命名ポリシー**: subscription_update は非推奨（subscription.* に統一）
- **変数管理**: システム設定でテーブル別に許可リスト管理
- **動的変数取得**: データベーススキーマから自動取得、機密カラムは除外
- **テンプレート作成**: 変数は自動適用、手動入力不可
- **UI/UX**: コピー用チップ表示、テーブル別グループ化

### 送信設定
- **送信先**: 登録時のメールアドレス（プロフィール更新時は更新後のアドレス）
- **送信タイミング**: 各イベント発生時 + リマインダーは24時間前
- **言語**: 日本語のみ（多言語対応なし）
- **設定**: ユーザー別通知ON/OFF設定なし、配信停止機能なし

## セキュリティ設定

### 環境変数設定
```env
# セキュリティ設定
APP_DEBUG=false
APP_ENV=production
APP_KEY=base64:your-32-character-key

# 注意: 上記は本番推奨設定です。開発環境では異なる値を使用してください。
# 開発環境例: APP_DEBUG=true, APP_ENV=local

# セッションセキュリティ
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# データベースセキュリティ
DB_STRICT=true
```

### セキュリティ実装例
```php
// レート制限ミドルウェア
Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Form Request バリデーション
class ReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lesson_schedule_id' => 'required|exists:lesson_schedules,id',
            'user_subscription_id' => 'required|exists:user_subscriptions,id',
        ];
    }
}
```

## 開発環境

### 開発環境
- **Laravel Herd**: 開発環境として使用
- **GitHub**: バージョン管理・コード共有
- **PHP**: ^8.2 (実際の環境: 8.4.12)
- **Laravel**: ^12.0 (実際のバージョン: 12.26.3)
- **データベース**: SQLite（開発）

### 必要なパッケージ
```bash
# Livewire
composer require livewire/livewire

# Laravel Cashier (Stripe統合)
composer require laravel/cashier

# 認証（選択）
composer require laravel/breeze --dev
# または
composer require laravel/fortify
```

### 開発コマンド
```bash
# 開発サーバー起動
php artisan serve

# フロントエンド開発サーバー
npm run dev

# データベース操作
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# テスト実行
php artisan test
php artisan test --coverage

# コードフォーマット
vendor/bin/pint

# セキュリティチェック
composer audit

# キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Git運用

### ブランチ戦略
- **main**: 本番環境用（安定版）
- **develop**: 開発統合用
- **feature/**: 新機能開発用
- **hotfix/**: 緊急修正用

### コミットメッセージ規約
```
feat: 新機能追加
fix: バグ修正
docs: ドキュメント更新
style: コードフォーマット
refactor: リファクタリング
test: テスト追加・修正
chore: その他の変更
```

### 開発フロー
1. **feature/ブランチ作成** → 機能開発 → **developにマージ** → **mainにリリース**
2. **各機能は独立したブランチ**で開発
3. **PR（Pull Request）**でコードレビュー
4. **コミットは小さく、意味のある単位**で分割

### Git操作コマンド
```bash
# 基本操作
git add .
git commit -m "feat: 機能追加"
git push origin main

# ブランチ操作
git checkout -b feature/新機能名
git checkout develop
git merge feature/新機能名

# 開発フロー例
git checkout -b feature/user-authentication
# 実装作業...
git add .
git commit -m "feat: ユーザー認証システム実装"
git push origin feature/user-authentication
# PR作成 → developマージ
```

---

**作成日**: 2025年9月
**バージョン**: 1.6
**ステータス**: Phase 2完了（Stripe統合・Webhook処理・回数制限管理）、Phase 3開始（予約データモデル構築完了）
